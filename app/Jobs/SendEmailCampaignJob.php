<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignLog;
use App\Services\BrevoService;
use App\Services\ContentRecommender;
use App\Services\EmailGroupResolver;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendEmailCampaignJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public int $tries = 2;

    public function __construct(public readonly int $campaignId) {}

    public function handle(BrevoService $brevo, EmailGroupResolver $resolver, ContentRecommender $recommender): void
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

        $templateHtml = $campaign->template->html_body;

        // Apply excluded emails
        if (! empty($campaign->excluded_emails)) {
            $excludedSet = array_flip($campaign->excluded_emails);
            $recipients = array_values(array_filter($recipients, fn ($r) => ! isset($excludedSet[$r['email']])));
        }

        // Add extra recipients
        if (! empty($campaign->extra_recipients)) {
            foreach ($campaign->extra_recipients as $extra) {
                $recipients[] = [
                    'email' => $extra['email'],
                    'name' => $extra['name'] ?? '',
                    'user_id' => null,
                    'params' => [
                        'name' => $extra['name'] ?? 'Pengguna',
                        'email' => $extra['email'],
                        'app_url' => config('brevo.novelya_url', config('app.url')),
                    ],
                ];
            }
        }

        $needsStoryData = (bool) preg_match('/\{\{story_/', $templateHtml);

        // Detect all params used in the template
        preg_match_all('/\{\{(\w+)\}\}/', $templateHtml, $tagMatches);
        $usedParams = array_unique($tagMatches[1] ?? []);

        $appUrl = config('brevo.novelya_url', config('app.url'));

        // Defaults matching what previewForUser() provides
        $defaults = [
            'name' => 'Pengguna',
            'email' => '',
            'app_url' => $appUrl,
            'expiry_date' => '',
            'plan_name' => '',
            'join_date' => '',
            'last_paid' => '',
            'invoice_url' => $appUrl.'/payment',
            'payment_status' => '',
            'trx_count' => '',
        ];

        // Batch-enrich recipients that have user_id with real data
        $userIds = array_filter(array_unique(array_column($recipients, 'user_id')));

        $userDataMap = [];
        if (! empty($userIds)) {
            $db = DB::connection('novel');

            $users = $db->table('users')
                ->whereIn('id', $userIds)
                ->get(['id', 'name', 'email', 'created_at'])
                ->keyBy('id');

            $transactions = $db->table('transactions as t')
                ->leftJoin('membership_plans as mp', 'mp.id', '=', 't.plan_id')
                ->whereIn('t.user_id', $userIds)
                ->where('t.status', 'paid')
                ->orderByDesc('t.expired_at')
                ->get(['t.user_id', 't.expired_at', 'mp.name as plan_name', 't.paid_at'])
                ->unique('user_id')
                ->keyBy('user_id');

            foreach ($userIds as $uid) {
                $u = $users->get($uid);
                $t = $transactions->get($uid);
                if (! $u) {
                    continue;
                }
                $userDataMap[$uid] = [
                    'name' => $u->name ?: 'Pengguna',
                    'email' => $u->email,
                    'join_date' => Carbon::parse($u->created_at)->format('d M Y'),
                    'expiry_date' => $t ? Carbon::parse($t->expired_at)->format('d M Y') : '',
                    'plan_name' => $t->plan_name ?? '',
                    'last_paid' => ($t && $t->paid_at) ? Carbon::parse($t->paid_at)->format('d M Y') : '',
                ];
            }
        }

        foreach ($recipients as &$r) {
            // Start with defaults, overlay user data, then resolver data (highest priority)
            $enriched = $defaults;

            if (! empty($r['user_id']) && isset($userDataMap[$r['user_id']])) {
                $enriched = array_merge($enriched, $userDataMap[$r['user_id']]);
            }

            $r['params'] = array_merge($enriched, $r['params'] ?? []);
            $r['params']['app_url'] = $appUrl;
            $r['params']['invoice_url'] = $appUrl.'/payment';

            if ($needsStoryData) {
                $story = $recommender->getTopForUser($r['user_id'] ?? null);
                if ($story) {
                    $r['params'] = array_merge($r['params'], $story);
                }
            }
        }
        unset($r);

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
                htmlContent: $templateHtml,
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
