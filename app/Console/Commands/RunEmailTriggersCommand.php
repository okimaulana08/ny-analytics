<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailCampaignJob;
use App\Models\AdminUser;
use App\Models\EmailCampaign;
use App\Models\EmailGroup;
use App\Models\EmailTrigger;
use App\Models\EmailTriggerLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunEmailTriggersCommand extends Command
{
    protected $signature = 'triggers:run';

    protected $description = 'Jalankan automated email triggers yang aktif';

    public function handle(): int
    {
        $triggers = EmailTrigger::with('template')->where('is_active', true)->get();

        if ($triggers->isEmpty()) {
            $this->info('Tidak ada trigger aktif.');

            return self::SUCCESS;
        }

        $systemAdmin = AdminUser::first();

        foreach ($triggers as $trigger) {
            $this->processTrigger($trigger, $systemAdmin?->id);
        }

        return self::SUCCESS;
    }

    private function processTrigger(EmailTrigger $trigger, ?int $adminId): void
    {
        $recipients = $this->resolveRecipients($trigger);

        if (empty($recipients)) {
            $this->info("Trigger [{$trigger->name}]: tidak ada penerima yang ditemukan.");

            return;
        }

        // Filter out recipients who already received this trigger within cooldown period
        $cooldownDate = now()->subDays($trigger->cooldown_days);
        $recentlySent = EmailTriggerLog::where('email_trigger_id', $trigger->id)
            ->where('sent_at', '>=', $cooldownDate)
            ->where('status', 'sent')
            ->pluck('recipient_email')
            ->flip()
            ->all();

        $recipients = array_values(array_filter(
            $recipients,
            fn ($r) => ! isset($recentlySent[$r['email']])
        ));

        if (empty($recipients)) {
            $this->info("Trigger [{$trigger->name}]: semua penerima masih dalam cooldown.");

            return;
        }

        $this->info("Trigger [{$trigger->name}]: mengirim ke ".count($recipients).' penerima.');

        // Create a temporary static EmailGroup for the campaign
        $group = EmailGroup::create([
            'name' => 'Trigger: '.$trigger->name.' '.now()->format('Y-m-d H:i'),
            'type' => 'static',
            'is_active' => true,
        ]);

        foreach (array_chunk($recipients, 500) as $chunk) {
            $group->members()->createMany(
                array_map(fn ($r) => ['email' => $r['email'], 'name' => $r['name'] ?? ''], $chunk)
            );
        }

        $campaign = EmailCampaign::create([
            'name' => 'Trigger: '.$trigger->name.' '.now()->format('d/m/Y'),
            'email_group_id' => $group->id,
            'email_template_id' => $trigger->email_template_id,
            'subject' => $trigger->template?->subject ?? $trigger->subject ?? $trigger->name,
            'status' => 'queued',
            'recipient_count' => count($recipients),
            'created_by' => $adminId,
        ]);

        SendEmailCampaignJob::dispatch($campaign->id);

        // Log all recipients
        $now = now();
        $logs = array_map(fn ($r) => [
            'email_trigger_id' => $trigger->id,
            'recipient_email' => $r['email'],
            'recipient_name' => $r['name'] ?? null,
            'user_id' => $r['user_id'] ?? null,
            'email_campaign_id' => $campaign->id,
            'status' => 'sent',
            'sent_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ], $recipients);

        foreach (array_chunk($logs, 500) as $chunk) {
            EmailTriggerLog::insert($chunk);
        }

        $this->info("Trigger [{$trigger->name}]: campaign #{$campaign->id} dibuat dan dijadwalkan.");
    }

    /**
     * @return array<int, array{email: string, name: string, user_id: string|null}>
     */
    private function resolveRecipients(EmailTrigger $trigger): array
    {
        $conditions = $trigger->conditions ?? [];

        // Route by condition first, fall back to trigger_type for legacy rows
        $condition = $trigger->condition;

        if ($condition) {
            return match ($condition) {
                EmailTrigger::COND_INVOICE_ACTIVE => $this->resolvePendingPayment($conditions, withInvoiceLink: true),
                EmailTrigger::COND_INVOICE_EXPIRED => $this->resolvePendingPayment($conditions, withInvoiceLink: false),
                EmailTrigger::COND_BEFORE_EXPIRY => $this->resolveBeforeExpiry($conditions),
                EmailTrigger::COND_AFTER_EXPIRY => $this->resolveAfterExpiry($conditions),
                default => $this->resolveByType($trigger, $conditions),
            };
        }

        return $this->resolveByType($trigger, $conditions);
    }

    /** Legacy routing by trigger_type (for rows without condition set). */
    private function resolveByType(EmailTrigger $trigger, array $conditions): array
    {
        return match ($trigger->trigger_type) {
            EmailTrigger::TYPE_EXPIRY_REMINDER => $this->resolveBeforeExpiry($conditions),
            EmailTrigger::TYPE_RE_ENGAGEMENT => $this->resolveReEngagement($conditions),
            EmailTrigger::TYPE_WELCOME_PAYMENT => $this->resolveWelcomePayment(),
            default => [],
        };
    }

    /**
     * Transaksi masih pending lebih dari X menit/jam.
     * withInvoiceLink = true  → invoice_active (ada link bayar di email)
     * withInvoiceLink = false → invoice_expired (tidak ada invoice, kirim link langganan)
     *
     * @param  array<string, mixed>  $conditions
     * @return array<int, array{email: string, name: string, user_id: string}>
     */
    private function resolvePendingPayment(array $conditions, bool $withInvoiceLink = true): array
    {
        $delayMinutes = (int) ($conditions['delay_minutes'] ?? 30);
        $cutoff = now()->subMinutes($delayMinutes);
        $subscriptionUrl = config('app.url');

        $rows = DB::connection('novel')
            ->table('transactions as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->leftJoin('membership_plans as mp', 'mp.id', '=', 't.plan_id')
            ->where('t.status', 'pending')
            ->where('t.created_at', '<=', $cutoff)
            ->whereNotNull('u.email')
            ->where('u.email', '!=', '')
            ->orderBy('t.created_at')
            ->get([
                'u.id as user_id',
                'u.email',
                'u.name',
                'mp.name as plan_name',
                't.total_amount',
                't.created_at as transaction_created_at',
            ])
            ->unique('email');

        return $rows->map(function ($r) use ($withInvoiceLink, $subscriptionUrl) {
            $params = [
                'name' => $r->name ?? 'Pengguna',
                'email' => $r->email,
                'plan_name' => $r->plan_name ?? 'Novelya Premium',
                'amount' => 'Rp '.number_format($r->total_amount ?? 0, 0, ',', '.'),
            ];

            if ($withInvoiceLink) {
                $params['invoice_link'] = $subscriptionUrl;
            } else {
                $params['subscription_url'] = $subscriptionUrl;
            }

            return [
                'email' => $r->email,
                'name' => $r->name ?? '',
                'user_id' => (string) $r->user_id,
                'params' => $params,
            ];
        })->values()->toArray();
    }

    /**
     * Subscription yang akan expired dalam X hari ke depan.
     *
     * @param  array<string, mixed>  $conditions
     * @return array<int, array{email: string, name: string, user_id: string}>
     */
    private function resolveBeforeExpiry(array $conditions): array
    {
        $daysBefore = (int) ($conditions['days_before'] ?? 7);

        $rows = DB::connection('novel')
            ->table('transactions as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->leftJoin('membership_plans as mp', 'mp.id', '=', 't.plan_id')
            ->where('t.status', 'paid')
            ->whereBetween('t.expired_at', [now(), now()->addDays($daysBefore)])
            ->whereNotNull('u.email')
            ->where('u.email', '!=', '')
            ->orderBy('t.expired_at')
            ->get(['u.id', 'u.email', 'u.name', 't.expired_at', 'mp.name as plan_name'])
            ->unique('email');

        return $rows->map(fn ($r) => [
            'email' => $r->email,
            'name' => $r->name ?? '',
            'user_id' => (string) $r->id,
            'params' => [
                'name' => $r->name ?? 'Pengguna',
                'email' => $r->email,
                'expiry_date' => Carbon::parse($r->expired_at)->format('d M Y'),
                'expired_at' => Carbon::parse($r->expired_at)->format('d M Y'),
                'days_left' => (string) max(0, (int) now()->diffInDays(Carbon::parse($r->expired_at), false)),
                'plan_name' => $r->plan_name ?? '',
            ],
        ])->values()->toArray();
    }

    /**
     * Subscription sudah expired dalam X hari terakhir dan belum ada renewal aktif.
     *
     * @param  array<string, mixed>  $conditions
     * @return array<int, array{email: string, name: string, user_id: string}>
     */
    private function resolveAfterExpiry(array $conditions): array
    {
        $days = (int) ($conditions['days_after'] ?? 7);
        $windowStart = now()->subDays($days);

        $rows = DB::connection('novel')
            ->table('transactions as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->leftJoin('membership_plans as mp', 'mp.id', '=', 't.plan_id')
            ->where('t.status', 'paid')
            ->where('t.expired_at', '<', now())
            ->where('t.expired_at', '>=', $windowStart)
            ->whereNotNull('u.email')
            ->where('u.email', '!=', '')
            ->whereNotExists(function ($q) {
                $q->from('transactions as t2')
                    ->whereColumn('t2.user_id', 't.user_id')
                    ->where('t2.status', 'paid')
                    ->where('t2.expired_at', '>', now());
            })
            ->orderByDesc('t.expired_at')
            ->get(['u.id', 'u.email', 'u.name', 't.expired_at', 'mp.name as plan_name'])
            ->unique('email');

        return $rows->map(fn ($r) => [
            'email' => $r->email,
            'name' => $r->name ?? '',
            'user_id' => (string) $r->id,
            'params' => [
                'name' => $r->name ?? 'Pengguna',
                'email' => $r->email,
                'expired_at' => Carbon::parse($r->expired_at)->format('d M Y'),
                'plan_name' => $r->plan_name ?? '',
            ],
        ])->values()->toArray();
    }

    /**
     * Subscriber aktif yang tidak baca chapter apapun dalam X hari.
     *
     * @param  array<string, mixed>  $conditions
     * @return array<int, array{email: string, name: string, user_id: string}>
     */
    private function resolveReEngagement(array $conditions): array
    {
        $inactiveDays = (int) ($conditions['inactive_days'] ?? 7);
        $db = DB::connection('novel');

        $recentReaderIds = $db->table('user_read')
            ->where('created_at', '>=', now()->subDays($inactiveDays))
            ->whereNull('is_deleted')
            ->orWhere('is_deleted', 0)
            ->pluck('user_id')
            ->unique()
            ->all();

        $rows = $db->table('transactions as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->leftJoin('membership_plans as mp', 'mp.id', '=', 't.plan_id')
            ->where('t.status', 'paid')
            ->where('t.expired_at', '>', now())
            ->whereNotIn('u.id', $recentReaderIds)
            ->whereNotNull('u.email')
            ->where('u.email', '!=', '')
            ->orderByDesc('t.expired_at')
            ->get(['u.id', 'u.email', 'u.name', 't.expired_at', 'mp.name as plan_name'])
            ->unique('email');

        return $rows->map(fn ($r) => [
            'email' => $r->email,
            'name' => $r->name ?? '',
            'user_id' => (string) $r->id,
            'params' => [
                'name' => $r->name ?? 'Pengguna',
                'email' => $r->email,
                'expiry_date' => Carbon::parse($r->expired_at)->format('d M Y'),
                'plan_name' => $r->plan_name ?? '',
            ],
        ])->values()->toArray();
    }

    /**
     * User yang baru pertama kali bayar hari ini.
     *
     * @return array<int, array{email: string, name: string, user_id: string}>
     */
    private function resolveWelcomePayment(): array
    {
        $firstPaidToday = DB::connection('novel')
            ->table('transactions')
            ->where('status', 'paid')
            ->whereDate('paid_at', today())
            ->select('user_id', DB::raw('MIN(paid_at) as first_paid'), DB::raw('COUNT(*) as total_trx'))
            ->groupBy('user_id')
            ->having('total_trx', '=', 1);

        $rows = DB::connection('novel')
            ->table('users as u')
            ->joinSub($firstPaidToday, 'fp', 'fp.user_id', '=', 'u.id')
            ->whereNotNull('u.email')
            ->where('u.email', '!=', '')
            ->get(['u.id', 'u.email', 'u.name', 'fp.first_paid']);

        return $rows->map(fn ($r) => [
            'email' => $r->email,
            'name' => $r->name ?? '',
            'user_id' => (string) $r->id,
            'params' => [
                'name' => $r->name ?? 'Pengguna',
                'email' => $r->email,
                'paid_at' => Carbon::parse($r->first_paid)->format('d M Y H:i'),
            ],
        ])->toArray();
    }
}
