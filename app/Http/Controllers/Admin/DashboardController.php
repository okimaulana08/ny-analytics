<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = Carbon::now()->toDateString();

        // Today's paid transactions
        $paidToday = Transaction::where('status', 'paid')
            ->whereDate('created_at', $today)
            ->selectRaw('COUNT(*) as count, SUM(total_amount) as total')
            ->first();

        // Today's pending transactions
        $pendingToday = Transaction::where('status', 'pending')
            ->whereDate('created_at', $today)
            ->count();

        // Today's user access (views)
        $userAccessToday = DB::connection('novel')
            ->table('user_view')
            ->whereDate('created_at', $today)
            ->count();

        // Top 10 books by reads today
        $topReads = DB::connection('novel')
            ->table('user_read')
            ->join('content', 'content.id', '=', 'user_read.content_id')
            ->whereDate('user_read.created_at', $today)
            ->where('content.is_deleted', false)
            ->where('content.is_published', true)
            ->groupBy('content.id', 'content.title')
            ->orderByDesc('reads_today')
            ->limit(10)
            ->get(['content.id', 'content.title', DB::raw('COUNT(user_read.id) as reads_today')]);

        // Recent transactions (paginated, default 10)
        $txPage    = max(1, (int) $request->query('tx_page', 1));
        $txPerPage = 10;
        $txOffset  = ($txPage - 1) * $txPerPage;

        $txTotal = (int) DB::connection('novel')->selectOne("
            SELECT COUNT(*) AS cnt FROM transactions
            WHERE status IN ('paid','pending') AND DATE(created_at) = ?
        ", [$today])->cnt;
        $txTotalPages = (int) ceil($txTotal / $txPerPage) ?: 1;

        $recentTransactions = DB::connection('novel')->select("
            SELECT t.id, t.created_at, t.total_amount, t.status,
                   u.name, u.email, p.phone_number
            FROM transactions t
            JOIN users u ON u.id = t.user_id
            LEFT JOIN profile p ON p.user_id = u.id
            WHERE t.status IN ('paid','pending') AND DATE(t.created_at) = ?
            ORDER BY t.created_at DESC
            LIMIT {$txPerPage} OFFSET {$txOffset}
        ", [$today]);

        // 7-day paid transaction chart data
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date  = Carbon::now()->subDays($i)->toDateString();
            $label = Carbon::now()->subDays($i)->format('d/m');
            $row   = Transaction::where('status', 'paid')
                ->whereDate('created_at', $date)
                ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount),0) as total')
                ->first();
            $chartData[] = [
                'label' => $label,
                'count' => (int) ($row->count ?? 0),
                'total' => (float) ($row->total ?? 0),
            ];
        }

        return view('admin.dashboard', compact(
            'paidToday',
            'pendingToday',
            'userAccessToday',
            'topReads',
            'recentTransactions',
            'chartData',
            'txPage',
            'txTotalPages',
            'txTotal',
            'txPerPage'
        ));
    }
}
