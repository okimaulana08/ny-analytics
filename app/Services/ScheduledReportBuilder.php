<?php

namespace App\Services;

use App\Models\ScheduledEmailReport;
use Carbon\Carbon;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class ScheduledReportBuilder
{
    public function build(ScheduledEmailReport $report): string
    {
        $html = match ($report->report_type) {
            ScheduledEmailReport::TYPE_REVENUE_SUMMARY => $this->buildRevenueSummary(),
            ScheduledEmailReport::TYPE_TOP_CONTENT => $this->buildTopContent(),
            ScheduledEmailReport::TYPE_CHURN_ALERT => $this->buildChurnAlert(),
            ScheduledEmailReport::TYPE_ENGAGEMENT_SUMMARY => $this->buildEngagementSummary(),
            default => '<p>Unknown report type.</p>',
        };

        return $this->wrap($report->name, $html);
    }

    private function db(): ConnectionInterface
    {
        return DB::connection('novel');
    }

    private function buildRevenueSummary(): string
    {
        $now = Carbon::now();

        $rev7d = (float) $this->db()->table('transactions')
            ->where('status', 'paid')
            ->where('created_at', '>=', $now->copy()->subDays(7))
            ->sum('total_amount');

        $rev30d = (float) $this->db()->table('transactions')
            ->where('status', 'paid')
            ->where('created_at', '>=', $now->copy()->subDays(30))
            ->sum('total_amount');

        $paidUsers = (int) $this->db()->table('transactions')
            ->where('status', 'paid')
            ->where('created_at', '>=', $now->copy()->subDays(30))
            ->distinct('user_id')
            ->count('user_id');

        $arpu = $paidUsers > 0 ? round($rev30d / $paidUsers) : 0;

        $newMembers = (int) $this->db()->table('transactions')
            ->where('status', 'paid')
            ->where('created_at', '>=', $now->copy()->subDays(30))
            ->whereNotExists(function ($q) use ($now) {
                $q->from('transactions as t2')
                    ->whereColumn('t2.user_id', 'transactions.user_id')
                    ->where('t2.status', 'paid')
                    ->where('t2.created_at', '<', $now->copy()->subDays(30));
            })
            ->distinct('user_id')
            ->count('user_id');

        $topPlan = $this->db()->table('transactions')
            ->join('membership_plans', 'membership_plans.id', '=', 'transactions.plan_id')
            ->where('transactions.status', 'paid')
            ->where('transactions.created_at', '>=', $now->copy()->subDays(30))
            ->selectRaw('membership_plans.name as plan_name, COUNT(*) as cnt')
            ->groupBy('membership_plans.name')
            ->orderByDesc('cnt')
            ->first();

        $rows = "
            <tr><td style='padding:8px 12px;color:#64748b;'>Revenue 7 hari</td><td style='padding:8px 12px;text-align:right;font-weight:600;'>Rp ".number_format($rev7d, 0, ',', '.')."</td></tr>
            <tr style='background:#f8fafc;'><td style='padding:8px 12px;color:#64748b;'>Revenue 30 hari</td><td style='padding:8px 12px;text-align:right;font-weight:600;'>Rp ".number_format($rev30d, 0, ',', '.')."</td></tr>
            <tr><td style='padding:8px 12px;color:#64748b;'>Member baru (30h)</td><td style='padding:8px 12px;text-align:right;font-weight:600;'>".number_format($newMembers)."</td></tr>
            <tr style='background:#f8fafc;'><td style='padding:8px 12px;color:#64748b;'>ARPU (30 hari)</td><td style='padding:8px 12px;text-align:right;font-weight:600;'>Rp ".number_format($arpu, 0, ',', '.')."</td></tr>
            <tr><td style='padding:8px 12px;color:#64748b;'>Plan Terlaris</td><td style='padding:8px 12px;text-align:right;font-weight:600;'>".($topPlan?->plan_name ?? '—').'</td></tr>
        ';

        return $this->table('Revenue Summary', $rows);
    }

    private function buildTopContent(): string
    {
        $top = $this->db()->table('content')
            ->where('is_published', 1)
            ->where('is_deleted', 0)
            ->orderByDesc('read_count')
            ->limit(10)
            ->select('title', 'read_count', 'view_count', 'subscribe_count', 'rating')
            ->get();

        $rows = '';
        foreach ($top as $i => $c) {
            $bg = $i % 2 === 1 ? 'background:#f8fafc;' : '';
            $rows .= "<tr style='{$bg}'>
                <td style='padding:8px 12px;color:#64748b;'>".($i + 1)."</td>
                <td style='padding:8px 12px;font-weight:500;'>".htmlspecialchars($c->title)."</td>
                <td style='padding:8px 12px;text-align:right;'>".number_format($c->read_count)."</td>
                <td style='padding:8px 12px;text-align:right;'>".number_format($c->view_count)."</td>
                <td style='padding:8px 12px;text-align:right;'>".($c->rating ? number_format($c->rating, 1) : '—').'</td>
            </tr>';
        }

        $header = "<tr style='background:#1e293b;color:#fff;'>
            <th style='padding:10px 12px;text-align:left;font-weight:600;font-size:12px;'>#</th>
            <th style='padding:10px 12px;text-align:left;font-weight:600;font-size:12px;'>Judul</th>
            <th style='padding:10px 12px;text-align:right;font-weight:600;font-size:12px;'>Baca</th>
            <th style='padding:10px 12px;text-align:right;font-weight:600;font-size:12px;'>View</th>
            <th style='padding:10px 12px;text-align:right;font-weight:600;font-size:12px;'>Rating</th>
        </tr>";

        return "<h3 style='font-size:15px;font-weight:600;color:#1e293b;margin:0 0 12px;'>Top 10 Konten</h3>
            <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;font-size:13px;'>
                {$header}{$rows}
            </table>";
    }

    private function buildChurnAlert(): string
    {
        $now = Carbon::now();

        $expiring7d = (int) $this->db()->table('transactions')
            ->where('status', 'paid')
            ->whereBetween('expired_at', [$now, $now->copy()->addDays(7)])
            ->distinct('user_id')
            ->count('user_id');

        $churned = (int) $this->db()->table('transactions')
            ->where('status', 'paid')
            ->where('expired_at', '>=', $now->copy()->subDays(7))
            ->where('expired_at', '<', $now)
            ->whereNotExists(function ($q) use ($now) {
                $q->from('transactions as t2')
                    ->whereColumn('t2.user_id', 'transactions.user_id')
                    ->where('t2.status', 'paid')
                    ->where('t2.created_at', '>=', $now->copy()->subDays(7));
            })
            ->distinct('user_id')
            ->count('user_id');

        $dormant = (int) $this->db()->table('transactions')
            ->where('status', 'paid')
            ->where('expired_at', '>', $now)
            ->whereNotExists(function ($q) use ($now) {
                $q->from('user_read')
                    ->whereColumn('user_read.user_id', 'transactions.user_id')
                    ->where('user_read.created_at', '>=', $now->copy()->subDays(14));
            })
            ->distinct('user_id')
            ->count('user_id');

        $rows = "
            <tr><td style='padding:8px 12px;color:#64748b;'>Akan expire 7 hari</td><td style='padding:8px 12px;text-align:right;font-weight:600;color:#f59e0b;'>{$expiring7d} user</td></tr>
            <tr style='background:#f8fafc;'><td style='padding:8px 12px;color:#64748b;'>Churn 7 hari terakhir</td><td style='padding:8px 12px;text-align:right;font-weight:600;color:#ef4444;'>{$churned} user</td></tr>
            <tr><td style='padding:8px 12px;color:#64748b;'>Dormant (aktif, tapi 14h tak baca)</td><td style='padding:8px 12px;text-align:right;font-weight:600;color:#8b5cf6;'>{$dormant} user</td></tr>
        ";

        return $this->table('Churn Alert', $rows);
    }

    private function buildEngagementSummary(): string
    {
        $now = Carbon::now();

        $reads = (int) $this->db()->table('user_read')
            ->where('created_at', '>=', $now->copy()->subDays(7))
            ->count();

        $activeReaders = (int) $this->db()->table('user_read')
            ->where('created_at', '>=', $now->copy()->subDays(7))
            ->distinct('user_id')
            ->count('user_id');

        $avgChapters = $activeReaders > 0 ? round($reads / $activeReaders, 1) : 0;

        $views = (int) $this->db()->table('content')
            ->where('updated_at', '>=', $now->copy()->subDays(7))
            ->sum('view_count');

        $rows = "
            <tr><td style='padding:8px 12px;color:#64748b;'>Total baca 7 hari</td><td style='padding:8px 12px;text-align:right;font-weight:600;'>".number_format($reads)."</td></tr>
            <tr style='background:#f8fafc;'><td style='padding:8px 12px;color:#64748b;'>Pembaca aktif 7 hari</td><td style='padding:8px 12px;text-align:right;font-weight:600;'>".number_format($activeReaders)."</td></tr>
            <tr><td style='padding:8px 12px;color:#64748b;'>Avg chapter/pembaca</td><td style='padding:8px 12px;text-align:right;font-weight:600;'>{$avgChapters}</td></tr>
        ";

        return $this->table('Engagement Summary', $rows);
    }

    private function table(string $title, string $rows): string
    {
        return "<h3 style='font-size:15px;font-weight:600;color:#1e293b;margin:0 0 12px;'>{$title}</h3>
            <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;font-size:13px;'>
                {$rows}
            </table>";
    }

    private function wrap(string $reportName, string $content): string
    {
        $date = Carbon::now('Asia/Jakarta')->translatedFormat('d F Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{$reportName}</title></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Inter',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:32px 16px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
  <tr><td style="background:#0f172a;padding:24px 32px;">
    <div style="font-family:'Courier New',monospace;color:#fff;font-size:18px;font-weight:700;">Novelya Analytics</div>
    <div style="color:rgba(255,255,255,0.4);font-size:12px;margin-top:4px;">{$reportName} · {$date}</div>
  </td></tr>
  <tr><td style="padding:28px 32px;">
    {$content}
    <p style="margin-top:24px;font-size:11px;color:#94a3b8;">
      Email ini dikirim otomatis oleh sistem Novelya Analytics. Jangan balas email ini.
    </p>
  </td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }
}
