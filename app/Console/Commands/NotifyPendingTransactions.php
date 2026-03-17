<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\WaNotification;
use App\Models\WaSchedulerLog;
use App\Services\WahaService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifyPendingTransactions extends Command
{
    protected $signature   = 'notify:pending';
    protected $description = 'Send WhatsApp notifications for pending transactions in the last 5 minutes';

    public function handle(WahaService $waha): void
    {
        $startedAt = Carbon::now();
        $sent      = 0;

        try {
            $cutoff = Carbon::now()->subMinutes(5);

            $transactions = Transaction::with('user')
                ->where('status', 'pending')
                ->where('created_at', '>=', $cutoff)
                ->get();

            foreach ($transactions as $tx) {
                $alreadySent = WaNotification::where('transaction_id', $tx->id)
                    ->where('type', 'pending')
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $amount   = number_format((float) $tx->total_amount, 0, ',', '.');
                $date     = Carbon::parse($tx->created_at)->format('d/m/Y H:i');
                $userName  = $tx->user?->name ?? 'Unknown';
                $userEmail = $tx->user?->email ?? '-';

                $message = "🔔 Transaksi Pending\n"
                         . "📦 ID: {$tx->external_id}\n"
                         . "👤 {$userName}\n"
                         . "📧 {$userEmail}\n"
                         . "💳 " . strtoupper($tx->payment_gateway) . "\n"
                         . "💰 Rp {$amount}\n"
                         . "📅 {$date} WIB";

                if ($waha->sendToGroup($message)) {
                    WaNotification::create([
                        'transaction_id' => $tx->id,
                        'type'           => 'pending',
                        'sent_at'        => Carbon::now(),
                    ]);
                    $sent++;
                    $this->info("Sent pending notification for {$tx->external_id}");
                } else {
                    $this->error("Failed to send for {$tx->external_id}");
                }
            }

            WaSchedulerLog::create([
                'scheduler_name'     => 'notify:pending',
                'status'             => 'success',
                'message'            => "Processed {$transactions->count()} transactions, sent {$sent} notifications",
                'notifications_sent' => $sent,
                'executed_at'        => $startedAt,
            ]);

            $this->info("Done. Sent {$sent} notifications.");

        } catch (\Exception $e) {
            WaSchedulerLog::create([
                'scheduler_name'     => 'notify:pending',
                'status'             => 'failed',
                'message'            => $e->getMessage(),
                'notifications_sent' => $sent,
                'executed_at'        => $startedAt,
            ]);

            $this->error("Error: {$e->getMessage()}");
        }
    }
}
