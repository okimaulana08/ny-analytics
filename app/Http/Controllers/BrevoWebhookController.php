<?php

namespace App\Http\Controllers;

use App\Models\EmailCampaignLog;
use App\Services\BrevoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class BrevoWebhookController extends Controller
{
    public function handle(Request $request, BrevoService $brevo): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Brevo-Signature', '');

        if (! $brevo->verifyWebhookSignature($payload, $signature)) {
            Log::warning('BrevoWebhook: invalid signature');

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
        $messageId = $event['MessageId'] ?? $event['messageId'] ?? null;
        $eventType = $event['event'] ?? '';

        if (! $messageId) {
            return;
        }

        $log = EmailCampaignLog::where('brevo_message_id', $messageId)->first();

        if (! $log) {
            return;
        }

        $statusMap = [
            'delivered' => 'delivered',
            'opened' => 'opened',
            'click' => 'clicked',
            'hard_bounce' => 'bounced',
            'soft_bounce' => 'bounced',
            'unsubscribed' => 'unsubscribed',
        ];

        $newStatus = $statusMap[$eventType] ?? null;

        if (! $newStatus) {
            return;
        }

        $updates = ['status' => $newStatus];

        if ($eventType === 'opened' && ! $log->opened_at) {
            $updates['opened_at'] = now();
        }

        if ($eventType === 'click' && ! $log->clicked_at) {
            $updates['clicked_at'] = now();
        }

        if (in_array($eventType, ['hard_bounce', 'soft_bounce'])) {
            $reason = $event['reason'] ?? $event['error'] ?? $event['description'] ?? null;
            $bounceType = $eventType === 'hard_bounce' ? 'Hard bounce' : 'Soft bounce';
            $updates['error_message'] = $reason ? "{$bounceType}: {$reason}" : $bounceType;
        }

        $log->update($updates);
    }
}
