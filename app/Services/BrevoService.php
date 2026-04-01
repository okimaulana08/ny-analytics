<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrevoService
{
    private string $apiKey;

    private string $baseUrl = 'https://api.brevo.com/v3';

    private string $senderEmail;

    private string $senderName;

    public function __construct()
    {
        $this->apiKey = config('brevo.api_key') ?? '';
        $this->senderEmail = config('brevo.sender_email', 'no-reply@novelya.id');
        $this->senderName = config('brevo.sender_name', 'Novelya');
    }

    /**
     * Send to multiple recipients using Brevo messageVersions batch.
     * Max 900 recipients per call — caller must chunk.
     *
     * @param  array<int, array{email: string, name: string, params: array<string, string>}>  $recipients
     * @return array{success: bool, message_ids: array<string>, error: ?string}
     */
    public function sendBatch(
        array $recipients,
        string $subject,
        string $htmlContent,
        ?string $scheduledAt = null
    ): array {
        if (empty($this->apiKey)) {
            Log::error('BrevoService: BREVO_API_KEY is not configured');

            return ['success' => false, 'message_ids' => [], 'error' => 'API key not configured'];
        }

        // Render HTML and subject per-recipient using PHP substitution.
        // This mirrors renderTemplate() and avoids relying on Brevo's server-side engine.
        $messageVersions = array_map(function ($r) use ($htmlContent, $subject) {
            $params = $r['params'] ?? [];

            return [
                'to' => [['email' => $r['email'], 'name' => $r['name'] ?? '']],
                'htmlContent' => $this->renderTemplate($htmlContent, $params),
                'subject' => $this->renderTemplate($subject, $params),
            ];
        }, $recipients);

        // Global subject and htmlContent are required by Brevo even when overridden per-version.
        // Render global subject with defaults so Brevo's own engine doesn't get raw {{placeholders}}.
        $payload = [
            'sender' => ['email' => $this->senderEmail, 'name' => $this->senderName],
            'subject' => $this->renderTemplate($subject, []),
            'htmlContent' => $htmlContent,
            'messageVersions' => $messageVersions,
        ];

        if ($scheduledAt !== null) {
            $payload['scheduledAt'] = $scheduledAt;
        }

        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->timeout(30)
                ->post("{$this->baseUrl}/smtp/email", $payload);

            if ($response->successful()) {
                $data = $response->json();
                $messageIds = [];

                if (isset($data['messageId'])) {
                    $messageIds[] = $data['messageId'];
                }

                if (isset($data['messageIds']) && is_array($data['messageIds'])) {
                    $messageIds = array_merge($messageIds, $data['messageIds']);
                }

                return ['success' => true, 'message_ids' => $messageIds, 'error' => null];
            }

            Log::error('BrevoService: sendBatch failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message_ids' => [],
                'error' => $response->json('message') ?? $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('BrevoService: HTTP exception in sendBatch', [
                'message' => $e->getMessage(),
            ]);

            return ['success' => false, 'message_ids' => [], 'error' => $e->getMessage()];
        }
    }

    /**
     * Render template by replacing {{tag}} placeholders with actual values.
     * Used for preview only; Brevo handles per-recipient substitution via params in sendBatch.
     *
     * @param  array<string, string>  $params
     */
    public function renderTemplate(string $html, array $params): string
    {
        $defaults = [
            'name' => 'Pengguna',
            'email' => 'user@example.com',
            'expiry_date' => now()->addDays(7)->format('d M Y'),
            'plan_name' => 'Premium',
            'app_url' => config('app.url'),
        ];

        $merged = array_merge($defaults, $params);

        foreach ($merged as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $html = str_replace("{{{$key}}}", (string) $value, $html);
        }

        return $html;
    }

    /**
     * Verify a Brevo webhook signature.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = config('brevo.webhook_secret');

        if (empty($secret)) {
            return true;
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }
}
