<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WahaService
{
    private string $baseUrl;
    private string $apiKey;
    private string $session;
    private ?string $groupId;

    public function __construct()
    {
        $this->baseUrl  = rtrim(config('waha.base_url'), '/');
        $this->apiKey   = config('waha.api_key') ?? '';
        $this->session  = config('waha.session', 'default');
        $this->groupId  = config('waha.group_id');
    }

    /**
     * Send a text message to the configured WhatsApp group.
     */
    public function sendToGroup(string $text): bool
    {
        if (empty($this->groupId)) {
            Log::error('WahaService: WAHA_GROUP_ID is not configured in .env');
            return false;
        }

        try {
            $response = Http::withHeaders(['X-Api-Key' => $this->apiKey])
                ->timeout(15)
                ->post("{$this->baseUrl}/api/sendText", [
                    'chatId'  => $this->groupId,
                    'text'    => $text,
                    'session' => $this->session,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('WahaService: sendText failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('WahaService: HTTP exception in sendToGroup', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Fetch all groups/chats from WAHA session.
     */
    public function listGroups(): array
    {
        try {
            // Try the groups endpoint first (WAHA v2)
            $response = Http::withHeaders(['X-Api-Key' => $this->apiKey])
                ->timeout(30)
                ->get("{$this->baseUrl}/api/{$this->session}/groups");

            if ($response->successful()) {
                $data = $response->json();
                return is_array($data) ? $data : [];
            }

            // Fallback: try chats endpoint
            $response = Http::withHeaders(['X-Api-Key' => $this->apiKey])
                ->timeout(30)
                ->get("{$this->baseUrl}/api/{$this->session}/chats");

            if ($response->successful()) {
                $data = $response->json();
                $chats = is_array($data) ? $data : [];
                // Filter only group chats (id ends with @g.us)
                return array_filter($chats, fn($c) => str_ends_with($c['id'] ?? '', '@g.us'));
            }

            Log::error('WahaService: listGroups failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return [];

        } catch (\Exception $e) {
            Log::error('WahaService: HTTP exception in listGroups', [
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Find a group by name substring, return its JID (chatId).
     * WAHA GOWS engine uses uppercase field names: JID, Name
     */
    public function findGroupByName(string $name): ?string
    {
        $groups = $this->listGroups();

        foreach ($groups as $group) {
            // WAHA GOWS: uppercase field names
            $subject = $group['Name'] ?? $group['name'] ?? $group['subject'] ?? '';
            if (str_contains(strtolower($subject), strtolower($name))) {
                return $group['JID'] ?? $group['id'] ?? null;
            }
        }

        return null;
    }

    public function getSession(): string
    {
        return $this->session;
    }
}
