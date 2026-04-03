<?php

namespace App\Console\Commands;

use App\Models\ScheduledEmailReport;
use App\Services\BrevoService;
use App\Services\ScheduledReportBuilder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledReportsCommand extends Command
{
    protected $signature = 'reports:send-scheduled';

    protected $description = 'Send scheduled email reports that are due today';

    public function handle(BrevoService $brevo, ScheduledReportBuilder $builder): int
    {
        $reports = ScheduledEmailReport::where('is_active', true)->get();
        $sent = 0;

        foreach ($reports as $report) {
            if (! $report->isDueToday()) {
                continue;
            }

            $this->info("Sending: {$report->name}");

            try {
                $html = $builder->build($report);
                $subject = '[Novelya] '.$report->name;

                $recipients = collect($report->recipients)->map(fn ($r) => [
                    'email' => $r['email'],
                    'name' => $r['name'] ?? $r['email'],
                    'params' => [],
                ])->all();

                $brevo->sendBatch($recipients, $subject, $html);

                $report->update([
                    'last_sent_at' => Carbon::now(),
                    'next_run_at' => $report->computeNextRun(),
                ]);

                $sent++;
            } catch (\Throwable $e) {
                Log::error("ScheduledReport #{$report->id} failed: ".$e->getMessage());
                $this->error("Failed: {$report->name} — ".$e->getMessage());
            }
        }

        $this->info("Done. {$sent} report(s) sent.");

        return self::SUCCESS;
    }
}
