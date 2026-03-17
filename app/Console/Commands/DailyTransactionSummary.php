<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\WaSchedulerLog;
use App\Services\WahaService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DailyTransactionSummary extends Command
{
    protected $signature   = 'notify:daily-summary';
    protected $description = 'Send daily paid transaction summary to WhatsApp group';

    public function handle(WahaService $waha): void
    {
        $startedAt = Carbon::now();
        $today     = $startedAt->toDateString();

        try {
            $transactions = Transaction::where('status', 'paid')
                ->whereDate('created_at', $today)
                ->get();

            $totalCount  = $transactions->count();
            $totalAmount = $transactions->sum('total_amount');

            $byGateway = $transactions
                ->groupBy('payment_gateway')
                ->map(fn($group) => [
                    'count' => $group->count(),
                    'total' => $group->sum('total_amount'),
                ]);

            $totalFormatted = number_format((float) $totalAmount, 0, ',', '.');
            $dateFormatted  = $startedAt->format('d/m/Y');

            if ($totalCount === 0) {
                $message = "📊 Rekap Harian {$dateFormatted}\n"
                         . "Tidak ada transaksi paid hari ini.";
            } else {
                $message = "📊 Rekap Harian {$dateFormatted}\n"
                         . "💳 Total Transaksi Paid: {$totalCount}\n"
                         . "💰 Total Nominal: Rp {$totalFormatted}\n"
                         . "\nRincian per Payment Gateway:";

                foreach ($byGateway as $gateway => $data) {
                    $gatewayTotal = number_format((float) $data['total'], 0, ',', '.');
                    $message .= "\n- " . strtoupper($gateway) . ": {$data['count']} transaksi, Rp {$gatewayTotal}";
                }
            }

            $success = $waha->sendToGroup($message);

            WaSchedulerLog::create([
                'scheduler_name'     => 'notify:daily-summary',
                'status'             => $success ? 'success' : 'failed',
                'message'            => $success
                    ? "Summary sent for {$today}: {$totalCount} transactions, Rp {$totalFormatted}"
                    : 'Failed to send WhatsApp message',
                'notifications_sent' => $success ? 1 : 0,
                'executed_at'        => Carbon::now(),
            ]);

            $this->info($success ? "Daily summary sent for {$today}." : "Failed to send daily summary.");

        } catch (\Exception $e) {
            WaSchedulerLog::create([
                'scheduler_name'     => 'notify:daily-summary',
                'status'             => 'failed',
                'message'            => $e->getMessage(),
                'notifications_sent' => 0,
                'executed_at'        => Carbon::now(),
            ]);

            $this->error("Error: {$e->getMessage()}");
        }
    }
}
