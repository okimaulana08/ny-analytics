<?php

namespace App\Console\Commands;

use App\Models\WaTrigger;
use App\Models\WaTriggerLog;
use App\Models\WaTriggerTemplate;
use App\Services\WahaService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunWaTriggersCommand extends Command
{
    protected $signature = 'wa:run-triggers';

    protected $description = 'Jalankan WhatsApp trigger otomatis yang aktif';

    public function __construct(private readonly WahaService $waha)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $triggers = WaTrigger::with('templates')->where('is_active', true)->get();

        if ($triggers->isEmpty()) {
            $this->info('Tidak ada WA trigger aktif.');

            return self::SUCCESS;
        }

        foreach ($triggers as $trigger) {
            $this->processTrigger($trigger);
        }

        return self::SUCCESS;
    }

    private function processTrigger(WaTrigger $trigger): void
    {
        $activeTemplates = $trigger->templates->where('is_active', true);

        if ($activeTemplates->isEmpty()) {
            $this->warn("Trigger [{$trigger->name}]: tidak ada template aktif, dilewati.");

            return;
        }

        // Route by condition first, fall back to type for legacy rows (condition = null)
        $condition = $trigger->condition ?? ($trigger->type === WaTrigger::TYPE_PENDING_PAYMENT
            ? WaTrigger::COND_INVOICE_ACTIVE
            : WaTrigger::COND_BEFORE_EXPIRY);

        $recipients = match ($condition) {
            WaTrigger::COND_INVOICE_ACTIVE => $this->resolvePendingPayment($trigger, withInvoiceLink: true),
            WaTrigger::COND_INVOICE_EXPIRED => $this->resolvePendingPayment($trigger, withInvoiceLink: false),
            WaTrigger::COND_BEFORE_EXPIRY => $this->resolveBeforeExpiry($trigger),
            WaTrigger::COND_AFTER_EXPIRY => $this->resolveAfterExpiry($trigger),
            default => [],
        };

        if (empty($recipients)) {
            $this->info("Trigger [{$trigger->name}]: tidak ada penerima.");

            return;
        }

        // Filter cooldown: skip if already sent within cooldown_hours
        $cooldownCutoff = now()->subHours($trigger->cooldown_hours);
        $recentlySent = WaTriggerLog::where('wa_trigger_id', $trigger->id)
            ->where('sent_at', '>=', $cooldownCutoff)
            ->pluck('user_id')
            ->flip()
            ->all();

        $recipients = array_values(array_filter(
            $recipients,
            fn ($r) => ! isset($recentlySent[(string) $r['user_id']])
        ));

        if (empty($recipients)) {
            $this->info("Trigger [{$trigger->name}]: semua penerima masih dalam cooldown.");

            return;
        }

        $this->info("Trigger [{$trigger->name}]: mengirim ke ".count($recipients).' penerima.');

        $templatePool = $activeTemplates->values();
        $sent = 0;

        foreach ($recipients as $recipient) {
            /** @var WaTriggerTemplate $template */
            $template = $templatePool->random();
            $message = $template->render($recipient['params']);

            $success = $this->waha->sendToUser($recipient['phone'], $message);

            if ($success) {
                WaTriggerLog::create([
                    'wa_trigger_id' => $trigger->id,
                    'user_id' => (string) $recipient['user_id'],
                    'phone' => $recipient['phone'],
                    'sent_at' => now(),
                ]);
                $sent++;
            } else {
                $this->warn("Gagal kirim WA ke {$recipient['phone']}");
            }
        }

        $this->info("Trigger [{$trigger->name}]: berhasil kirim ke {$sent} penerima.");
    }

    /**
     * Transaksi masih pending lebih dari X menit/jam.
     * withInvoiceLink = true  → kondisi invoice_active (invoice belum expired, ada link bayar)
     * withInvoiceLink = false → kondisi invoice_expired (invoice sudah expired, kirim link langganan)
     *
     * @return array<int, array{user_id: string, phone: string, params: array<string, string>}>
     */
    private function resolvePendingPayment(WaTrigger $trigger, bool $withInvoiceLink = true): array
    {
        $delayMinutes = $trigger->delayInMinutes();
        $cutoff = now()->subMinutes($delayMinutes);

        $rows = DB::connection('novel')
            ->table('transactions as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->join('profile as p', 'p.user_id', '=', 't.user_id')
            ->leftJoin('membership_plans as mp', 'mp.id', '=', 't.plan_id')
            ->where('t.status', 'pending')
            ->where('t.created_at', '<=', $cutoff)
            ->whereNotNull('p.phone_number')
            ->where('p.phone_number', '!=', '')
            ->orderBy('t.created_at')
            ->get([
                't.id as transaction_id',
                'u.id as user_id',
                'u.name',
                'mp.name as plan_name',
                'mp.price',
                't.total_amount',
                't.created_at as transaction_created_at',
                'p.phone_number',
            ])
            ->unique('user_id');

        $subscriptionUrl = config('app.url');

        return $rows->map(function ($r) use ($withInvoiceLink, $subscriptionUrl) {
            $minutesAgo = (int) Carbon::parse($r->transaction_created_at)->diffInMinutes(now());

            $params = [
                'name' => $r->name ?? 'Kak',
                'plan_name' => $r->plan_name ?? 'Novelya Premium',
                'amount' => 'Rp '.number_format($r->total_amount ?? $r->price ?? 0, 0, ',', '.'),
                'minutes_ago' => (string) $minutesAgo,
            ];

            if ($withInvoiceLink) {
                $params['invoice_link'] = $subscriptionUrl;
            } else {
                $params['subscription_url'] = $subscriptionUrl;
            }

            return [
                'user_id' => (string) $r->user_id,
                'phone' => $r->phone_number,
                'params' => $params,
            ];
        })->values()->toArray();
    }

    /**
     * Subscription yang akan expired dalam X hari.
     *
     * @return array<int, array{user_id: string, phone: string, params: array<string, string>}>
     */
    private function resolveBeforeExpiry(WaTrigger $trigger): array
    {
        $days = $trigger->delay_value;

        $rows = DB::connection('novel')
            ->table('transactions as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->join('profile as p', 'p.user_id', '=', 't.user_id')
            ->leftJoin('membership_plans as mp', 'mp.id', '=', 't.plan_id')
            ->where('t.status', 'paid')
            ->whereBetween('t.expired_at', [now(), now()->addDays($days)])
            ->whereNotNull('p.phone_number')
            ->where('p.phone_number', '!=', '')
            ->orderBy('t.expired_at')
            ->get([
                'u.id as user_id',
                'u.name',
                'mp.name as plan_name',
                't.expired_at',
                'p.phone_number',
            ])
            ->unique('user_id');

        return $rows->map(function ($r) {
            $expiredAt = Carbon::parse($r->expired_at);
            $daysLeft = (int) now()->diffInDays($expiredAt, false);

            return [
                'user_id' => (string) $r->user_id,
                'phone' => $r->phone_number,
                'params' => [
                    'name' => $r->name ?? 'Kak',
                    'plan_name' => $r->plan_name ?? 'Novelya Premium',
                    'expired_at' => $expiredAt->format('d M Y'),
                    'days_left' => (string) max(0, $daysLeft),
                ],
            ];
        })->values()->toArray();
    }

    /**
     * Subscription sudah expired dalam X hari terakhir dan belum ada renewal aktif.
     *
     * @return array<int, array{user_id: string, phone: string, params: array<string, string>}>
     */
    private function resolveAfterExpiry(WaTrigger $trigger): array
    {
        $days = $trigger->delay_value;
        $windowStart = now()->subDays($days);

        $rows = DB::connection('novel')
            ->table('transactions as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->join('profile as p', 'p.user_id', '=', 't.user_id')
            ->leftJoin('membership_plans as mp', 'mp.id', '=', 't.plan_id')
            ->where('t.status', 'paid')
            ->where('t.expired_at', '<', now())
            ->where('t.expired_at', '>=', $windowStart)
            ->whereNotNull('p.phone_number')
            ->where('p.phone_number', '!=', '')
            ->whereNotExists(function ($q) {
                $q->from('transactions as t2')
                    ->whereColumn('t2.user_id', 't.user_id')
                    ->where('t2.status', 'paid')
                    ->where('t2.expired_at', '>', now());
            })
            ->orderByDesc('t.expired_at')
            ->get([
                'u.id as user_id',
                'u.name',
                'mp.name as plan_name',
                't.expired_at',
                'p.phone_number',
            ])
            ->unique('user_id');

        return $rows->map(function ($r) {
            $expiredAt = Carbon::parse($r->expired_at);

            return [
                'user_id' => (string) $r->user_id,
                'phone' => $r->phone_number,
                'params' => [
                    'name' => $r->name ?? 'Kak',
                    'plan_name' => $r->plan_name ?? 'Novelya Premium',
                    'expired_at' => $expiredAt->format('d M Y'),
                ],
            ];
        })->values()->toArray();
    }
}
