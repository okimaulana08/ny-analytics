<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use Illuminate\Http\Request;

class ActivityLogger
{
    /**
     * Map of route name patterns → [feature label, action label].
     * More specific patterns must come first.
     *
     * @var array<string, array{string, string}>
     */
    private static array $routeMap = [
        // Auth
        'admin.login.post' => ['Auth', 'Login'],

        // Admin Users
        'admin.users.store' => ['Admin Users', 'Create'],
        'admin.users.update' => ['Admin Users', 'Update'],
        'admin.users.destroy' => ['Admin Users', 'Delete'],
        'admin.users.toggle' => ['Admin Users', 'Toggle'],

        // Broadcast Email
        'admin.crm.broadcast.store' => ['Broadcast Email', 'Send'],

        // Individual Email
        'admin.crm.individual.store' => ['Individual Email', 'Send'],

        // Email Groups
        'admin.crm.groups.store' => ['Email Groups', 'Create'],
        'admin.crm.groups.update' => ['Email Groups', 'Update'],
        'admin.crm.groups.destroy' => ['Email Groups', 'Delete'],

        // Email Templates
        'admin.crm.templates.store' => ['Email Templates', 'Create'],
        'admin.crm.templates.update' => ['Email Templates', 'Update'],
        'admin.crm.templates.destroy' => ['Email Templates', 'Delete'],
        'admin.crm.templates.ai-generate' => ['Email Templates', 'Generate'],

        // Campaigns
        'admin.crm.campaigns.resend' => ['Campaign History', 'Send'],
        'admin.crm.campaigns.destroy' => ['Campaign History', 'Delete'],

        // Email Triggers
        'admin.crm.triggers.store' => ['Email Triggers', 'Create'],
        'admin.crm.triggers.update' => ['Email Triggers', 'Update'],
        'admin.crm.triggers.destroy' => ['Email Triggers', 'Delete'],
        'admin.crm.triggers.toggle' => ['Email Triggers', 'Toggle'],

        // Scheduled Reports
        'admin.crm.scheduled-reports.store' => ['Scheduled Reports', 'Create'],
        'admin.crm.scheduled-reports.update' => ['Scheduled Reports', 'Update'],
        'admin.crm.scheduled-reports.destroy' => ['Scheduled Reports', 'Delete'],
        'admin.crm.scheduled-reports.send-now' => ['Scheduled Reports', 'Send'],

        // Revenue Forecast
        'admin.reports.revenue-forecast.ai' => ['Revenue Forecast', 'Generate'],

        // Revenue Daily
        'admin.reports.revenue-daily.cost' => ['Revenue Harian', 'Update'],

        // User Recommend
        'admin.reports.user-recommend.send-email' => ['User Recommend', 'Send'],

        // WA Triggers
        'admin.crm.wa-triggers.store' => ['WA Triggers', 'Create'],
        'admin.crm.wa-triggers.update' => ['WA Triggers', 'Update'],
        'admin.crm.wa-triggers.destroy' => ['WA Triggers', 'Delete'],
        'admin.crm.wa-triggers.toggle' => ['WA Triggers', 'Toggle'],

        // System Config
        'admin.system-config.update' => ['System Config', 'Update'],

        // Novel Generator
        'admin.novel.stories.store' => ['Novel Generator', 'Create Story'],
        'admin.novel.stories.destroy' => ['Novel Generator', 'Delete Story'],
        'admin.novel.stories.approve-overview' => ['Novel Generator', 'Approve Overview'],
        'admin.novel.stories.reject-overview' => ['Novel Generator', 'Reject Overview'],
        'admin.novel.stories.regenerate-overview' => ['Novel Generator', 'Regenerate Overview'],
        'admin.novel.stories.generate-outlines' => ['Novel Generator', 'Generate Outlines'],
        'admin.novel.stories.approve-outlines' => ['Novel Generator', 'Approve Outlines'],
        'admin.novel.chapters.approve-outline' => ['Novel Generator', 'Approve Chapter Outline'],
        'admin.novel.chapters.regenerate-outline' => ['Novel Generator', 'Regenerate Outline'],
        'admin.novel.chapters.generate-content' => ['Novel Generator', 'Generate Content'],
        'admin.novel.chapters.approve-content' => ['Novel Generator', 'Approve Content'],
        'admin.novel.chapters.request-revision' => ['Novel Generator', 'Request Revision'],
        'admin.novel.guidelines.store' => ['Novel Guideline', 'Create'],
        'admin.novel.guidelines.update' => ['Novel Guideline', 'Update'],
        'admin.novel.guidelines.destroy' => ['Novel Guideline', 'Delete'],
    ];

    /** Fields to strip from payload for security/privacy. */
    private static array $sensitiveFields = [
        '_token', '_method', 'password', 'password_confirmation', 'current_password',
    ];

    /** Maximum payload size stored (characters). Prevents huge email HTML from bloating DB. */
    private const MAX_PAYLOAD_LENGTH = 4000;

    public static function fromRequest(Request $request): void
    {
        $routeName = $request->route()?->getName() ?? '';
        $mapping = self::$routeMap[$routeName] ?? null;

        if ($mapping === null) {
            return; // Not a tracked route
        }

        [$feature, $action] = $mapping;

        $adminSession = session('admin_user');
        $adminId = $adminSession['id'] ?? null;
        $adminName = $adminSession['name'] ?? 'System';
        $adminEmail = $adminSession['email'] ?? '';

        $payload = self::buildPayload($request);

        AdminActivityLog::create([
            'admin_user_id' => $adminId,
            'admin_name' => $adminName,
            'admin_email' => $adminEmail,
            'action' => $action,
            'feature' => $feature,
            'url' => '/'.ltrim($request->path(), '/'),
            'http_method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }

    public static function login(Request $request, int $adminUserId, string $adminName, string $adminEmail): void
    {
        AdminActivityLog::create([
            'admin_user_id' => $adminUserId,
            'admin_name' => $adminName,
            'admin_email' => $adminEmail,
            'action' => 'Login',
            'feature' => 'Auth',
            'url' => '/admin/login',
            'http_method' => 'POST',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => [
                'ip' => $request->ip(),
                'device' => self::parseDevice($request->userAgent() ?? ''),
                'user_agent' => $request->userAgent(),
            ],
            'created_at' => now(),
        ]);
    }

    /** @return array<string, mixed>|null */
    private static function buildPayload(Request $request): ?array
    {
        $data = $request->except(self::$sensitiveFields);

        if (empty($data)) {
            return null;
        }

        // Truncate very long string values (e.g. HTML content)
        array_walk_recursive($data, function (&$value) {
            if (is_string($value) && mb_strlen($value) > 500) {
                $value = mb_substr($value, 0, 500).'…[truncated]';
            }
        });

        // If total JSON is still huge, drop large keys
        $json = json_encode($data);
        if ($json && strlen($json) > self::MAX_PAYLOAD_LENGTH) {
            $data = ['note' => 'Payload terlalu besar untuk disimpan lengkap.'];
        }

        return $data;
    }

    private static function parseDevice(string $ua): string
    {
        if (str_contains($ua, 'Mobile') || str_contains($ua, 'Android')) {
            $type = 'Mobile';
        } elseif (str_contains($ua, 'Tablet') || str_contains($ua, 'iPad')) {
            $type = 'Tablet';
        } else {
            $type = 'Desktop';
        }

        $browser = 'Unknown Browser';
        if (str_contains($ua, 'Edg/')) {
            $browser = 'Edge';
        } elseif (str_contains($ua, 'Chrome/') && ! str_contains($ua, 'Chromium')) {
            $browser = 'Chrome';
        } elseif (str_contains($ua, 'Firefox/')) {
            $browser = 'Firefox';
        } elseif (str_contains($ua, 'Safari/') && ! str_contains($ua, 'Chrome')) {
            $browser = 'Safari';
        } elseif (str_contains($ua, 'OPR/') || str_contains($ua, 'Opera/')) {
            $browser = 'Opera';
        }

        $os = 'Unknown OS';
        if (str_contains($ua, 'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($ua, 'Mac OS')) {
            $os = 'macOS';
        } elseif (str_contains($ua, 'Linux')) {
            $os = 'Linux';
        } elseif (str_contains($ua, 'Android')) {
            $os = 'Android';
        } elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) {
            $os = 'iOS';
        }

        return "{$type} · {$browser} · {$os}";
    }
}
