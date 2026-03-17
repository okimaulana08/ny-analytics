<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\WaNotification;
use App\Models\WaSchedulerLog;
use App\Services\WahaService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifyPaidTransactions extends Command
{
    protected $signature   = 'notify:paid';
    protected $description = 'Send WhatsApp notifications for paid transactions in the last 5 minutes';

    public function handle(WahaService $waha): void
    {
        $startedAt = Carbon::now();
        $sent      = 0;

        try {
            $cutoff = Carbon::now()->subMinutes(5);

            $transactions = Transaction::with('user')
                ->where('status', 'paid')
                ->where('paid_at', '>=', $cutoff)
                ->get();

            foreach ($transactions as $tx) {
                $alreadySent = WaNotification::where('transaction_id', $tx->id)
                    ->where('type', 'paid')
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $amount   = number_format((float) $tx->total_amount, 0, ',', '.');
                $paidDate = Carbon::parse($tx->paid_at)->format('d/m/Y H:i');
                $userName  = $tx->user?->name ?? 'Unknown';
                $userEmail = $tx->user?->email ?? '-';

                $message = "✅ Transaksi Sudah Dibayar\n"
                         . "📦 ID: {$tx->external_id}\n"
                         . "👤 {$userName}\n"
                         . "📧 {$userEmail}\n"
                         . "💳 " . strtoupper($tx->payment_gateway) . "\n"
                         . "💰 Rp {$amount}\n"
                         . "📅 {$paidDate} WIB";

                if ($waha->sendToGroup($message)) {
                    WaNotification::create([
                        'transaction_id' => $tx->id,
                        'type'           => 'paid',
                        'sent_at'        => Carbon::now(),
                    ]);
                    $sent++;
                    $this->info("Sent paid notification for {$tx->external_id}");
                } else {
                    $this->error("Failed to send for {$tx->external_id}");
                }
            }

            WaSchedulerLog::create([
                'scheduler_name'     => 'notify:paid',
                'status'             => 'success',
                'message'            => "Processed {$transactions->count()} transactions, sent {$sent} notifications",
                'notifications_sent' => $sent,
                'executed_at'        => $startedAt,
            ]);

            $this->info("Done. Sent {$sent} notifications.");

        } catch (\Exception $e) {
            WaSchedulerLog::create([
                'scheduler_name'     => 'notify:paid',
                'status'             => 'failed',
                'message'            => $e->getMessage(),
                'notifications_sent' => $sent,
                'executed_at'        => $startedAt,
            ]);

            $this->error("Error: {$e->getMessage()}");
        }
    }
}
