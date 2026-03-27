<?php

namespace App\Http\Controllers;

use App\Models\EmailCampaignLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class BrevoWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        // Brevo does NOT send a signature header — auth via query string token
        $secret = config('brevo.webhook_secret');
        if (! empty($secret) && $request->query('token') !== $secret) {
            Log::warning('BrevoWebhook: invalid token');

            return response('Unauthorized', 401);
        }

        $data = $request->json()->all();

        if (empty($data)) {
            return response('OK', 200);
        }

        $events = isset($data[0]) ? $data : [$data];

        foreach ($events as $event) {
            $this->processEvent($event);
        }

        return response('OK', 200);
    }

    private function processEvent(array $event): void
    {
        // Brevo sends 'message-id' with hyphen per their docs
        $messageId = $event['message-id'] ?? $event['MessageId'] ?? $event['messageId'] ?? null;
        $eventType = $event['event'] ?? '';

        if (! $messageId) {
            return;
        }

        $log = EmailCampaignLog::where('brevo_message_id', $messageId)->first();

        if (! $log) {
            return;
        }

        // Full event map per Brevo transactional webhook docs
        $statusMap = [
            'request' => 'sent',
            'delivered' => 'delivered',
            'opened' => 'opened',
            'first_open' => 'opened',
            'proxy_open' => 'opened',
            'unique_proxy_open' => 'opened',
            'click' => 'clicked',
            'hard_bounce' => 'bounced',
            'soft_bounce' => 'bounced',
            'deferred' => 'sent',
            'spam' => 'failed',
            'invalid_email' => 'failed',
            'blocked' => 'failed',
            'error' => 'failed',
            'unsubscribed' => 'unsubscribed',
        ];

        $newStatus = $statusMap[$eventType] ?? null;

        if (! $newStatus) {
            return;
        }

        $updates = ['status' => $newStatus];

        if ($eventType === 'request' && ! $log->sent_at) {
            $updates['sent_at'] = now();
        }

        if (in_array($eventType, ['opened', 'first_open', 'proxy_open', 'unique_proxy_open']) && ! $log->opened_at) {
            $updates['opened_at'] = now();
        }

        if ($eventType === 'click' && ! $log->clicked_at) {
            $updates['clicked_at'] = now();
        }

        if (in_array($eventType, ['hard_bounce', 'soft_bounce'])) {
            $reason = $event['reason'] ?? null;
            $bounceType = $eventType === 'hard_bounce' ? 'Hard bounce' : 'Soft bounce';
            $updates['error_message'] = $reason ? "{$bounceType}: {$reason}" : $bounceType;
        }

        if (in_array($eventType, ['spam', 'invalid_email', 'blocked', 'error'])) {
            $reason = $event['reason'] ?? $event['description'] ?? null;
            $label = match ($eventType) {
                'spam' => 'Spam complaint',
                'invalid_email' => 'Invalid email',
                'blocked' => 'Blocked',
                'error' => 'Error',
            };
            $updates['error_message'] = $reason ? "{$label}: {$reason}" : $label;
        }

        $log->update($updates);
    }
}
