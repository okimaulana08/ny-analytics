<?php

namespace App\Http\Controllers\Api;

use App\Console\Commands\DailyTransactionSummary;
use App\Console\Commands\NotifyPaidTransactions;
use App\Console\Commands\NotifyPendingTransactions;
use App\Http\Controllers\Controller;
use App\Models\WaSchedulerLog;
use App\Services\WahaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class NotificationController extends Controller
{
    public function __construct(private WahaService $waha)
    {
    }

    /**
     * POST /api/notify/pending
     * Manually trigger pending transaction check.
     */
    public function triggerPending(): JsonResponse
    {
        $exitCode = Artisan::call(NotifyPendingTransactions::class);
        $output   = Artisan::output();

        return response()->json([
            'triggered' => 'notify:pending',
            'exit_code' => $exitCode,
            'output'    => trim($output),
        ]);
    }

    /**
     * POST /api/notify/paid
     */
    public function triggerPaid(): JsonResponse
    {
        $exitCode = Artisan::call(NotifyPaidTransactions::class);
        $output   = Artisan::output();

        return response()->json([
            'triggered' => 'notify:paid',
            'exit_code' => $exitCode,
            'output'    => trim($output),
        ]);
    }

    /**
     * POST /api/notify/daily-summary
     */
    public function triggerDailySummary(): JsonResponse
    {
        $exitCode = Artisan::call(DailyTransactionSummary::class);
        $output   = Artisan::output();

        return response()->json([
            'triggered' => 'notify:daily-summary',
            'exit_code' => $exitCode,
            'output'    => trim($output),
        ]);
    }

    /**
     * GET /api/groups/find
     * Discover the WhatsApp group ID for "Novelya-Cuaks".
     */
    public function findGroup(): JsonResponse
    {
        $groups  = $this->waha->listGroups();
        $groupId = $this->waha->findGroupByName('Novelya-Cuaks');

        $matching = array_values(array_filter($groups, function ($g) {
            // WAHA GOWS uses uppercase field names
            $name = $g['Name'] ?? $g['name'] ?? $g['subject'] ?? '';
            return str_contains(strtolower($name), 'novelya');
        }));

        return response()->json([
            'found_group_id'       => $groupId,
            'instruction'          => $groupId
                ? "Set WAHA_GROUP_ID={$groupId} in your .env then run: php artisan config:clear"
                : 'Group not found. Check group name or verify WAHA session is active.',
            'matching_groups'      => $matching,
            'total_groups_scanned' => count($groups),
        ]);
    }

    /**
     * GET /api/scheduler/status
     * Show last execution status for each scheduler.
     */
    public function schedulerStatus(): JsonResponse
    {
        $schedulers = ['notify:pending', 'notify:paid', 'notify:daily-summary'];
        $status     = [];

        foreach ($schedulers as $name) {
            $last = WaSchedulerLog::where('scheduler_name', $name)
                ->orderByDesc('executed_at')
                ->first();

            $status[$name] = $last ? [
                'status'             => $last->status,
                'message'            => $last->message,
                'notifications_sent' => $last->notifications_sent,
                'executed_at'        => $last->executed_at?->toIso8601String(),
            ] : null;
        }

        return response()->json(['schedulers' => $status]);
    }
}
