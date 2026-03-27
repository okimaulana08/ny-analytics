<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignLog;
use App\Services\BrevoService;
use App\Services\EmailGroupResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendEmailCampaignJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public int $tries = 2;

    public function __construct(public readonly int $campaignId) {}

    public function handle(BrevoService $brevo, EmailGroupResolver $resolver): void
    {
        $campaign = EmailCampaign::with(['group', 'template'])->find($this->campaignId);

        if (! $campaign) {
            Log::error("SendEmailCampaignJob: Campaign #{$this->campaignId} not found");

            return;
        }

        if (! $campaign->template) {
            Log::error("SendEmailCampaignJob: Campaign #{$this->campaignId} has no template");
            $campaign->update(['status' => 'failed']);

            return;
        }

        $campaign->update(['status' => 'sending']);

        $recipients = $campaign->group
            ? $resolver->resolve($campaign->group)
            : [];

        if (empty($recipients)) {
            Log::warning("SendEmailCampaignJob: Campaign #{$this->campaignId} has no recipients");
            $campaign->update(['status' => 'failed']);

            return;
        }

        $campaign->update(['recipient_count' => count($recipients)]);

        $htmlBody = $brevo->renderTemplate($campaign->template->html_body, [
            'app_url' => config('brevo.novelya_url'),
        ]);

        $scheduledAt = $campaign->scheduled_at
            ? $campaign->scheduled_at->toIso8601String()
            : null;

        $chunks = array_chunk($recipients, 900);
        $sentCount = 0;
        $failedCount = 0;

        foreach ($chunks as $chunk) {
            $result = $brevo->sendBatch(
                recipients: $chunk,
                subject: $campaign->subject,
                htmlContent: $htmlBody,
                scheduledAt: $scheduledAt
            );

            $logStatus = $result['success'] ? 'sent' : 'failed';
            $errorMessage = $result['success'] ? null : $result['error'];
            $messageIds = $result['message_ids'];

            $now = now();
            $logs = [];
            foreach ($chunk as $i => $r) {
                $logs[] = [
                    'email_campaign_id' => $campaign->id,
                    'recipient_email' => $r['email'],
                    'recipient_name' => $r['name'] ?? null,
                    'status' => $logStatus,
                    'brevo_message_id' => $messageIds[$i] ?? null,
                    'error_message' => $errorMessage,
                    'sent_at' => $result['success'] ? $now : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            EmailCampaignLog::insert($logs);

            if ($result['success']) {
                $sentCount += count($chunk);
            } else {
                $failedCount += count($chunk);
            }
        }

        $campaign->update([
            'status' => $failedCount === count($recipients) ? 'failed' : 'sent',
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'sent_at' => now(),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendEmailCampaignJob: Campaign #{$this->campaignId} job failed", [
            'message' => $exception->getMessage(),
        ]);

        EmailCampaign::where('id', $this->campaignId)->update(['status' => 'failed']);
    }
}
