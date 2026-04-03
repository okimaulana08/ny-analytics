<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyRevenueCost;
use App\Services\BrevoService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ReportController extends Controller
{
    private function db()
    {
        return DB::connection('novel');
    }

    public function subscription(Request $request): View
    {
        $db = $this->db();

        $kpi = $db->selectOne("
            SELECT
                (SELECT COUNT(DISTINCT user_id) FROM transactions WHERE status='paid') AS ever_paid,
                (SELECT COUNT(*) FROM users) AS total_users,
                (SELECT COALESCE(SUM(total_amount),0) FROM transactions WHERE status='paid') AS total_revenue,
                (SELECT ROUND(COALESCE(SUM(total_amount),0)/NULLIF(COUNT(DISTINCT user_id),0),0)
                 FROM transactions WHERE status='paid') AS arpu
        ");

        $renewalCount = $db->selectOne("
            SELECT COUNT(*) AS cnt FROM (
                SELECT user_id FROM transactions WHERE status='paid'
                GROUP BY user_id HAVING COUNT(*) > 1
            ) r
        ");

        $dailyTrend = $db->select('
            SELECT `date`, new_member, renewal_member,
                   (new_member + renewal_member) AS total
            FROM recap_daily
            WHERE `date` >= CURDATE() - INTERVAL 30 DAY
            ORDER BY `date` ASC
        ');

        $revByPlan = $db->select("
            SELECT mp.name AS plan_name, mp.duration_days, mp.price,
                   COUNT(t.id) AS total_trx,
                   COALESCE(SUM(t.total_amount),0) AS total_revenue
            FROM transactions t
            JOIN membership_plans mp ON mp.id = t.plan_id
            WHERE t.status = 'paid'
            GROUP BY mp.id, mp.name, mp.duration_days, mp.price
            ORDER BY total_revenue DESC
        ");

        $gatewayStats = $db->select("
            SELECT payment_gateway,
                   COUNT(*) AS total,
                   SUM(status='paid') AS paid,
                   SUM(status='failed') AS failed,
                   SUM(status='pending') AS pending,
                   ROUND(SUM(status='paid')*100.0/COUNT(*),1) AS success_rate
            FROM transactions
            GROUP BY payment_gateway
        ");

        $statusBreakdown = $db->select('
            SELECT status, COUNT(*) AS cnt
            FROM transactions
            WHERE created_at >= NOW() - INTERVAL 30 DAY
            GROUP BY status
        ');

        $renewerPage = max(1, (int) $request->query('renewer_page', 1));
        $renewerPerPage = 10;
        $renewerOffset = ($renewerPage - 1) * $renewerPerPage;

        $renewerTotal = (int) ($db->selectOne("
            SELECT COUNT(*) AS cnt FROM (
                SELECT t.user_id
                FROM transactions t
                WHERE t.status = 'paid'
                GROUP BY t.user_id
                HAVING COUNT(t.id) > 1
            ) r
        ")->cnt ?? 0);

        $renewerPages = (int) ceil($renewerTotal / $renewerPerPage) ?: 1;

        $topRenewers = $db->select("
            SELECT u.name, u.email, MAX(p.phone_number) AS phone_number,
                   COUNT(t.id) AS trx_count,
                   SUM(t.total_amount) AS total_spent,
                   MAX(mp.name) AS latest_plan
            FROM transactions t
            JOIN users u ON u.id = t.user_id
            JOIN membership_plans mp ON mp.id = t.plan_id
            LEFT JOIN profile p ON p.user_id = u.id
            WHERE t.status = 'paid'
            GROUP BY t.user_id, u.name, u.email
            HAVING trx_count > 1
            ORDER BY trx_count DESC, total_spent DESC
            LIMIT {$renewerPerPage} OFFSET {$renewerOffset}
        ");

        return view('admin.reports.subscription', compact(
            'kpi', 'renewalCount', 'dailyTrend',
            'revByPlan', 'gatewayStats', 'statusBreakdown',
            'topRenewers', 'renewerPage', 'renewerPages', 'renewerTotal'
        ));
    }

    public function engagement(): View
    {
        $db = $this->db();

        $dailyEngagement = $db->select('
            SELECT `date`, `read`, `view`, `like`, `comment`, `share`
            FROM recap_daily
            WHERE `date` >= CURDATE() - INTERVAL 30 DAY
            ORDER BY `date` ASC
        ');

        $engKpi = $db->selectOne('
            SELECT
                COALESCE(SUM(`read`),0) AS total_reads,
                COALESCE(SUM(`view`),0) AS total_views
            FROM recap_daily
            WHERE `date` >= CURDATE() - INTERVAL 30 DAY
        ');

        $activeReaders = $db->selectOne('
            SELECT COUNT(DISTINCT user_id) AS cnt
            FROM user_read
            WHERE created_at >= CURDATE() - INTERVAL 30 DAY
        ');

        $avgDepth = $db->selectOne('
            SELECT ROUND(COUNT(*) / NULLIF(COUNT(DISTINCT user_id), 0), 1) AS val
            FROM user_read
            WHERE created_at >= CURDATE() - INTERVAL 30 DAY
        ');

        $repeatReaders = $db->selectOne('
            SELECT
                (SELECT COUNT(DISTINCT user_id) FROM user_read
                 WHERE created_at >= CURDATE() - INTERVAL 30 DAY) AS total_readers,
                COUNT(*) AS repeat_readers
            FROM (
                SELECT user_id FROM user_read
                WHERE created_at >= CURDATE() - INTERVAL 30 DAY
                GROUP BY user_id
                HAVING COUNT(DISTINCT DATE(created_at)) >= 2
            ) rep
        ');

        $depthTrend = $db->select('
            SELECT DATE(created_at) AS date,
                   COUNT(*) AS total_reads,
                   COUNT(DISTINCT user_id) AS active_readers,
                   ROUND(COUNT(*) / NULLIF(COUNT(DISTINCT user_id), 0), 2) AS avg_depth
            FROM user_read
            WHERE created_at >= CURDATE() - INTERVAL 30 DAY
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ');

        $topContent = $db->select('
            SELECT c.id, c.title, c.read_count, c.view_count,
                   c.subscribe_count, c.like_count,
                   ROUND(c.rating, 2) AS rating,
                   COUNT(ch.id) AS chapter_count,
                   ROUND(c.read_count * 100.0 / NULLIF(c.view_count, 0), 1) AS read_through_pct
            FROM content c
            LEFT JOIN chapters ch ON ch.content_id = c.id AND ch.is_published = 1
            WHERE c.is_published = 1 AND c.is_deleted = 0
            GROUP BY c.id, c.title, c.read_count, c.view_count,
                     c.subscribe_count, c.like_count, c.rating
            ORDER BY c.read_count DESC
            LIMIT 20
        ');

        $vtcByContent = $db->select('
            SELECT c.title,
                   SUM(rc.`view`) AS total_views,
                   SUM(rc.`read`) AS total_reads,
                   ROUND(SUM(rc.`read`) / NULLIF(SUM(rc.`view`), 0), 2) AS avg_chapters_per_view
            FROM recap_content rc
            JOIN content c ON c.id = rc.content_id
            WHERE rc.date >= CURDATE() - INTERVAL 30 DAY
              AND c.is_published = 1 AND c.is_deleted = 0
            GROUP BY rc.content_id, c.title
            HAVING SUM(rc.`view`) > 0
            ORDER BY avg_chapters_per_view DESC
            LIMIT 15
        ');

        $topContentId = $topContent[0]->id ?? null;
        $topContentTitle = $topContent[0]->title ?? '';
        $chapterFunnel = [];
        if ($topContentId) {
            $chapterFunnel = $db->select('
                SELECT sequence, title, read_count
                FROM chapters
                WHERE content_id = ? AND is_published = 1 AND is_deleted = 0
                ORDER BY sequence ASC
                LIMIT 30
            ', [$topContentId]);
            $maxReads = collect($chapterFunnel)->max('read_count') ?: 1;
            foreach ($chapterFunnel as $ch) {
                $ch->pct = round($ch->read_count * 100 / $maxReads, 1);
            }
        }

        return view('admin.reports.engagement', compact(
            'dailyEngagement', 'engKpi', 'activeReaders', 'avgDepth',
            'repeatReaders', 'depthTrend', 'topContent',
            'vtcByContent', 'chapterFunnel', 'topContentTitle'
        ));
    }

    public function segments(): View
    {
        $db = $this->db();

        $expiringSoon = $db->select('
            SELECT u.name, u.email, p.phone_number, u.last_login_at,
                   um.expired_at, mp.name AS plan_name,
                   DATEDIFF(um.expired_at, NOW()) AS days_left
            FROM user_memberships um
            JOIN users u ON u.id = um.user_id
            JOIN membership_plans mp ON mp.id = um.plan_id
            LEFT JOIN profile p ON p.user_id = u.id
            WHERE um.is_active = 1
              AND um.expired_at BETWEEN NOW() AND NOW() + INTERVAL 7 DAY
            ORDER BY um.expired_at ASC
        ');

        $churned = $db->select("
            SELECT * FROM (
                SELECT u.name, u.email, MAX(p.phone_number) AS phone_number, u.last_login_at,
                       MAX(t.paid_at) AS last_paid_at,
                       MAX(um2.expired_at) AS membership_expired_at,
                       MAX(mp.name) AS last_plan,
                       COUNT(t.id) AS total_trx,
                       SUM(t.total_amount) AS lifetime_value
                FROM users u
                JOIN transactions t ON t.user_id = u.id AND t.status = 'paid'
                JOIN membership_plans mp ON mp.id = t.plan_id
                LEFT JOIN user_memberships um ON um.user_id = u.id AND um.is_active = 1
                LEFT JOIN user_memberships um2 ON um2.user_id = u.id
                LEFT JOIN profile p ON p.user_id = u.id
                WHERE um.user_id IS NULL
                GROUP BY u.id, u.name, u.email, u.last_login_at
            ) ch
            WHERE membership_expired_at < NOW()
              AND last_paid_at < NOW() - INTERVAL 7 DAY
            ORDER BY last_paid_at DESC
        ");

        $neverSubscribed = $db->select("
            SELECT u.name, u.email, p.phone_number, u.created_at, u.last_login_at,
                   COUNT(DISTINCT ur.content_id) AS unique_contents,
                   COUNT(ur.id) AS total_chapters_read,
                   MAX(ur.created_at) AS last_read_at,
                   DATEDIFF(NOW(), MAX(ur.created_at)) AS days_since_read
            FROM users u
            JOIN user_read ur ON ur.user_id = u.id
            LEFT JOIN transactions t ON t.user_id = u.id AND t.status = 'paid'
            LEFT JOIN profile p ON p.user_id = u.id
            WHERE t.user_id IS NULL
            GROUP BY u.id, u.name, u.email, p.phone_number, u.created_at, u.last_login_at
            ORDER BY total_chapters_read DESC
        ");

        $dormant = $db->select("
            SELECT u.name, u.email, p.phone_number, u.last_login_at,
                   u.inactive_reminder_sent_at,
                   DATEDIFF(NOW(), u.last_login_at) AS days_inactive,
                   CASE WHEN t.user_id IS NOT NULL THEN 'lapsed_member'
                        ELSE 'never_subscribed' END AS dormant_type
            FROM users u
            LEFT JOIN (
                SELECT DISTINCT user_id FROM transactions WHERE status = 'paid'
            ) t ON t.user_id = u.id
            LEFT JOIN profile p ON p.user_id = u.id
            WHERE u.last_login_at < NOW() - INTERVAL 30 DAY
              AND (u.inactive_reminder_sent_at IS NULL
                   OR u.inactive_reminder_sent_at < NOW() - INTERVAL 14 DAY)
            ORDER BY u.last_login_at ASC
        ");

        $churnTimeline = $db->select("
            SELECT DATE_FORMAT(expired_at, '%Y-%v') AS week,
                   MIN(DATE(expired_at)) AS week_start,
                   COUNT(*) AS churned_count
            FROM user_memberships
            WHERE expired_at >= CURDATE() - INTERVAL 90 DAY
            GROUP BY DATE_FORMAT(expired_at, '%Y-%v')
            ORDER BY week ASC
        ");

        $segmentCounts = [
            'expiring' => count($expiringSoon),
            'churned' => count($churned),
            'never_subscribed' => count($neverSubscribed),
            'dormant' => count($dormant),
        ];

        return view('admin.reports.segments', compact(
            'expiringSoon', 'churned', 'neverSubscribed', 'dormant',
            'segmentCounts', 'churnTimeline'
        ));
    }

    public function transactions(Request $request): View
    {
        $db = $this->db();
        $status = $request->query('status', '');
        $gateway = $request->query('gateway', '');

        $kpi = $db->selectOne("
            SELECT
                COUNT(*) AS total_trx,
                SUM(status='paid')    AS paid_count,
                SUM(status='pending') AS pending_count,
                SUM(status='failed')  AS failed_count,
                COALESCE(SUM(CASE WHEN status='paid' THEN total_amount ELSE 0 END), 0) AS total_revenue,
                COALESCE(SUM(CASE WHEN status='paid' AND DATE(created_at)=CURDATE() THEN total_amount ELSE 0 END), 0) AS revenue_today,
                SUM(status='paid' AND DATE(created_at)=CURDATE()) AS paid_today
            FROM transactions
        ");

        $page = max(1, (int) $request->query('page', 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];
        if ($status) {
            $where[] = 't.status = ?';
            $params[] = $status;
        }
        if ($gateway) {
            $where[] = 't.payment_gateway = ?';
            $params[] = $gateway;
        }
        $whereClause = $where ? 'WHERE '.implode(' AND ', $where) : '';

        $totalRow = $db->selectOne("
            SELECT COUNT(*) AS cnt
            FROM transactions t
            JOIN users u ON u.id = t.user_id
            JOIN membership_plans mp ON mp.id = t.plan_id
            {$whereClause}
        ", $params);
        $total = (int) ($totalRow->cnt ?? 0);
        $totalPages = (int) ceil($total / $perPage) ?: 1;

        $transactions = $db->select("
            SELECT
                t.id, t.created_at, t.paid_at, t.expired_at, t.payment_url, t.total_amount,
                t.payment_gateway, t.status,
                u.name AS user_name, u.email AS user_email,
                p.phone_number AS user_phone,
                mp.name AS plan_name,
                (SELECT c.title FROM user_read ur
                 JOIN content c ON c.id = ur.content_id
                 WHERE ur.user_id = t.user_id
                   AND ur.created_at <= t.created_at
                 ORDER BY ur.created_at DESC LIMIT 1) AS last_content,
                (SELECT ch.title FROM user_read ur
                 JOIN chapters ch ON ch.id = ur.chapter_id
                 WHERE ur.user_id = t.user_id
                   AND ur.created_at <= t.created_at
                 ORDER BY ur.created_at DESC LIMIT 1) AS last_chapter,
                (SELECT ur.created_at FROM user_read ur
                 WHERE ur.user_id = t.user_id
                   AND ur.created_at <= t.created_at
                 ORDER BY ur.created_at DESC LIMIT 1) AS last_read_at
            FROM transactions t
            JOIN users u ON u.id = t.user_id
            JOIN membership_plans mp ON mp.id = t.plan_id
            LEFT JOIN profile p ON p.user_id = u.id
            {$whereClause}
            ORDER BY t.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ", $params);

        return view('admin.reports.transactions', compact(
            'kpi', 'transactions',
            'page', 'totalPages', 'total', 'perPage'
        ));
    }

    public function userList(Request $request): JsonResponse
    {
        $db = $this->db();
        $segment = $request->query('segment', '');

        $users = match ($segment) {
            'paid_users' => $db->select("
                SELECT u.name, u.email, p.phone_number, u.last_login_at,
                       COUNT(t.id) AS trx_count,
                       SUM(t.total_amount) AS total_spent,
                       MAX(t.paid_at) AS last_paid_at
                FROM users u
                JOIN transactions t ON t.user_id = u.id AND t.status = 'paid'
                LEFT JOIN profile p ON p.user_id = u.id
                GROUP BY u.id, u.name, u.email, p.phone_number, u.last_login_at
                ORDER BY last_paid_at DESC
            "),
            'free_users' => $db->select("
                SELECT u.name, u.email, p.phone_number, u.created_at, u.last_login_at
                FROM users u
                LEFT JOIN transactions t ON t.user_id = u.id AND t.status = 'paid'
                LEFT JOIN profile p ON p.user_id = u.id
                WHERE t.user_id IS NULL
                ORDER BY u.created_at DESC
            "),
            'renewers' => $db->select("
                SELECT u.name, u.email, MAX(p.phone_number) AS phone_number,
                       COUNT(t.id) AS trx_count,
                       SUM(t.total_amount) AS total_spent,
                       MAX(t.paid_at) AS last_paid_at
                FROM users u
                JOIN transactions t ON t.user_id = u.id AND t.status = 'paid'
                LEFT JOIN profile p ON p.user_id = u.id
                GROUP BY u.id, u.name, u.email
                HAVING COUNT(t.id) > 1
                ORDER BY COUNT(t.id) DESC
            "),
            'active_readers' => $db->select('
                SELECT u.name, u.email, MAX(p.phone_number) AS phone_number, u.last_login_at,
                       COUNT(ur.id) AS total_reads,
                       COUNT(DISTINCT DATE(ur.created_at)) AS active_days,
                       MAX(ur.created_at) AS last_read_at
                FROM users u
                JOIN user_read ur ON ur.user_id = u.id
                LEFT JOIN profile p ON p.user_id = u.id
                WHERE ur.created_at >= CURDATE() - INTERVAL 30 DAY
                GROUP BY u.id, u.name, u.email, u.last_login_at
                ORDER BY total_reads DESC
            '),
            'repeat_readers' => $db->select('
                SELECT u.name, u.email, MAX(p.phone_number) AS phone_number, u.last_login_at,
                       COUNT(DISTINCT DATE(ur.created_at)) AS active_days,
                       COUNT(ur.id) AS total_reads,
                       MAX(ur.created_at) AS last_read_at
                FROM users u
                JOIN user_read ur ON ur.user_id = u.id
                LEFT JOIN profile p ON p.user_id = u.id
                WHERE ur.created_at >= CURDATE() - INTERVAL 30 DAY
                GROUP BY u.id, u.name, u.email, u.last_login_at
                HAVING COUNT(DISTINCT DATE(ur.created_at)) >= 2
                ORDER BY active_days DESC
            '),
            default => [],
        };

        return response()->json([
            'segment' => $segment,
            'count' => count($users),
            'users' => $users,
        ]);
    }

    public function realtime(): View
    {
        $db = DB::connection('novel');
        $tabData = [];

        foreach ([1, 6, 24] as $h) {
            // KPI aggregates
            $kpi = $db->selectOne("
                SELECT
                    COUNT(DISTINCT ur.user_id) AS active_users,
                    COUNT(ur.id)               AS total_chapters,
                    COUNT(DISTINCT ur.content_id) AS total_books,
                    (SELECT COUNT(*) FROM transactions
                     WHERE status = 'paid'
                       AND created_at >= NOW() - INTERVAL {$h} HOUR) AS paid_tx
                FROM user_read ur
                WHERE ur.created_at >= NOW() - INTERVAL {$h} HOUR
            ");

            // Per-user activity
            $users = $db->select("
                SELECT
                    u.id, u.name, u.email, p.phone_number,
                    COUNT(ur.id)                  AS chapters_read,
                    COUNT(DISTINCT ur.content_id) AS books_count,
                    MAX(ur.created_at)            AS last_activity,
                    (SELECT COUNT(*) FROM transactions t
                     WHERE t.user_id = u.id AND t.status = 'paid'
                       AND t.created_at >= NOW() - INTERVAL {$h} HOUR) AS paid_tx,
                    (SELECT COUNT(*) FROM transactions t
                     WHERE t.user_id = u.id AND t.status = 'paid') AS ever_paid,
                    (SELECT MAX(um.expired_at) FROM user_memberships um
                     WHERE um.user_id = u.id) AS membership_expires
                FROM user_read ur
                JOIN users u ON u.id = ur.user_id
                LEFT JOIN profile p ON p.user_id = u.id
                WHERE ur.created_at >= NOW() - INTERVAL {$h} HOUR
                GROUP BY u.id, u.name, u.email, p.phone_number
                ORDER BY last_activity DESC
            ");

            // Book list per user (one batch query)
            $bookDetails = [];
            if (! empty($users)) {
                $ids = array_map(fn ($u) => $u->id, $users);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $books = $db->select("
                    SELECT
                        ur.user_id,
                        c.id AS content_id,
                        c.title,
                        COUNT(ur.id) AS chapters_read,
                        MAX(ur.created_at) AS last_read
                    FROM user_read ur
                    JOIN content c ON c.id = ur.content_id
                    WHERE ur.user_id IN ({$placeholders})
                      AND ur.created_at >= NOW() - INTERVAL {$h} HOUR
                    GROUP BY ur.user_id, c.id, c.title
                    ORDER BY ur.user_id, last_read DESC
                ", $ids);

                foreach ($books as $book) {
                    $bookDetails[$book->user_id][] = $book;
                }
            }

            $tabData[$h] = compact('kpi', 'users', 'bookDetails');
        }

        $generatedAt = now()->format('d M Y, H:i:s');

        return view('admin.reports.realtime', compact('tabData', 'generatedAt'));
    }

    // ── Content Analytics ────────────────────────────────────────────────────

    public function contentAnalytics(Request $request): View
    {
        $db = $this->db();

        // KPI
        $kpi = $db->selectOne('
            SELECT
                (SELECT COUNT(*) FROM content WHERE is_published=1 AND is_deleted=0) AS total_content,
                (SELECT COALESCE(SUM(read_count),0) FROM content WHERE is_published=1 AND is_deleted=0) AS total_reads,
                (SELECT COALESCE(SUM(view_count),0) FROM content WHERE is_published=1 AND is_deleted=0) AS total_views,
                (SELECT COALESCE(SUM(subscribe_count),0) FROM content WHERE is_published=1 AND is_deleted=0) AS total_subscribes,
                (SELECT COUNT(DISTINCT user_id) FROM user_read) AS unique_readers,
                (SELECT COUNT(DISTINCT content_id) FROM user_read WHERE DATE(created_at) = CURDATE()) AS active_today,
                (SELECT COUNT(*) FROM content WHERE is_published=1 AND is_deleted=0 AND is_completed=1) AS completed_content
        ');

        // Category breakdown
        $byCategory = $db->select('
            SELECT mcc.name AS category,
                   COUNT(c.id) AS content_count,
                   COALESCE(SUM(c.read_count),0) AS total_reads,
                   COALESCE(SUM(c.view_count),0) AS total_views,
                   COALESCE(SUM(c.subscribe_count),0) AS subscribes,
                   COALESCE(SUM(c.like_count),0) AS likes
            FROM content c
            JOIN master_content_category mcc ON mcc.id = c.category_id
            WHERE c.is_published=1 AND c.is_deleted=0
            GROUP BY mcc.id, mcc.name
            ORDER BY total_reads DESC
        ');

        // Daily trend 30 days (from recap_content)
        $dailyTrend = $db->select('
            SELECT DATE(rc.`date`) AS day,
                   SUM(rc.`read`) AS total_reads,
                   SUM(rc.`view`) AS total_views,
                   SUM(rc.subscribe) AS subscribes
            FROM recap_content rc
            WHERE rc.`date` >= NOW() - INTERVAL 30 DAY
            GROUP BY DATE(rc.`date`)
            ORDER BY day
        ');

        // Build full 30-day array
        $trendByDate = collect($dailyTrend)->keyBy('day');
        $trendDates = [];
        for ($i = 29; $i >= 0; $i--) {
            $trendDates[] = now()->subDays($i)->format('Y-m-d');
        }
        $trend30d = array_map(fn ($d) => [
            'date' => $d,
            'reads' => (int) ($trendByDate->get($d)?->total_reads ?? 0),
            'views' => (int) ($trendByDate->get($d)?->total_views ?? 0),
            'subscribes' => (int) ($trendByDate->get($d)?->subscribes ?? 0),
        ], $trendDates);

        // Top content table (paginated + filtered)
        $sort = $request->query('sort', 'reads');
        $author = trim($request->query('author', ''));
        $title = trim($request->query('title', ''));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $orderMap = [
            'reads' => 'c.read_count DESC',
            'views' => 'c.view_count DESC',
            'subscribes' => 'c.subscribe_count DESC',
            'recent' => 'c.published_at DESC',
        ];
        $orderSql = $orderMap[$sort] ?? $orderMap['reads'];

        $filterWhere = '';
        $filterParam = [];
        if ($title !== '') {
            $filterWhere .= ' AND c.title LIKE ?';
            $filterParam[] = '%'.$title.'%';
        }
        if ($author !== '') {
            $filterWhere .= ' AND u.name LIKE ?';
            $filterParam[] = '%'.$author.'%';
        }

        $countSql = "
            SELECT COUNT(*) AS cnt
            FROM content c
            LEFT JOIN users u ON u.id = c.user_id
            WHERE c.is_published=1 AND c.is_deleted=0 {$filterWhere}
        ";
        $total = $db->selectOne($countSql, $filterParam)->cnt;
        $totalPages = (int) ceil($total / $perPage);

        $contents = $db->select("
            SELECT c.id, c.title, c.read_count, c.view_count, c.subscribe_count,
                   c.like_count, c.share_count, c.comment_count, c.rating,
                   c.is_completed, c.published_at,
                   mcc.name AS category,
                   (SELECT COUNT(*) FROM chapters ch
                    WHERE ch.content_id = c.id AND ch.is_published=1 AND ch.is_deleted=0) AS chapter_count,
                   (SELECT SUM(rc.`read`) FROM recap_content rc
                    WHERE rc.content_id = c.id AND rc.`date` >= NOW() - INTERVAL 30 DAY) AS reads_30d,
                   (SELECT SUM(rc.`view`) FROM recap_content rc
                    WHERE rc.content_id = c.id AND rc.`date` >= NOW() - INTERVAL 30 DAY) AS views_30d,
                   ROUND(c.subscribe_count / NULLIF(c.view_count, 0) * 100, 1) AS convert_rate,
                   ROUND(c.read_count / NULLIF(c.view_count * chapter_counts.avg_ch, 0) * 100, 1) AS read_through_pct,
                   u.name AS author_name
            FROM content c
            LEFT JOIN master_content_category mcc ON mcc.id = c.category_id
            LEFT JOIN users u ON u.id = c.user_id
            LEFT JOIN (
                SELECT content_id, AVG(sequence) AS avg_ch FROM chapters
                WHERE is_published=1 AND is_deleted=0 GROUP BY content_id
            ) chapter_counts ON chapter_counts.content_id = c.id
            WHERE c.is_published=1 AND c.is_deleted=0 {$filterWhere}
            ORDER BY {$orderSql}
            LIMIT {$perPage} OFFSET {$offset}
        ", $filterParam);

        // Top 5 by subscribe (for highlight cards)
        $topBySubscribe = $db->select('
            SELECT c.title, c.subscribe_count, c.read_count, c.view_count,
                   ROUND(c.subscribe_count / NULLIF(c.view_count,0)*100, 1) AS convert_rate
            FROM content c
            WHERE c.is_published=1 AND c.is_deleted=0 AND c.subscribe_count > 0
            ORDER BY c.subscribe_count DESC LIMIT 5
        ');

        return view('admin.reports.content', compact(
            'kpi', 'byCategory', 'trend30d', 'contents',
            'page', 'perPage', 'total', 'totalPages', 'sort',
            'topBySubscribe', 'author', 'title'
        ));
    }

    public function contentSearch(Request $request): JsonResponse
    {
        $q = trim($request->query('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $items = $this->db()->select('
            SELECT c.id, c.title,
                   (SELECT COUNT(*) FROM chapters ch WHERE ch.content_id = c.id AND ch.is_published=1 AND ch.is_deleted=0) AS chapter_count
            FROM content c
            WHERE c.is_published=1 AND c.is_deleted=0 AND c.title LIKE ?
            ORDER BY c.read_count DESC
            LIMIT 10
        ', ['%'.$q.'%']);

        return response()->json($items);
    }

    public function contentReaders(Request $request, string $contentId): JsonResponse
    {
        $db = $this->db();

        $content = $db->selectOne(
            'SELECT id, title FROM content WHERE id = ? AND is_published=1 AND is_deleted=0',
            [$contentId]
        );

        if (! $content) {
            return response()->json(['error' => 'Konten tidak ditemukan.'], 404);
        }

        $readers = $db->select('
            SELECT u.name, u.email, MAX(ur.created_at) AS last_read_at, COUNT(ur.id) AS read_count
            FROM user_read ur
            JOIN users u ON u.id = ur.user_id
            WHERE ur.content_id = ? AND ur.user_id IS NOT NULL
            GROUP BY ur.user_id, u.name, u.email
            ORDER BY last_read_at DESC
            LIMIT 200
        ', [$contentId]);

        return response()->json([
            'title' => $content->title,
            'readers' => array_map(fn ($r) => [
                'name' => $r->name ?: '—',
                'email' => $r->email ?: '—',
                'last_read_at' => $r->last_read_at,
                'read_count' => (int) $r->read_count,
            ], $readers),
        ]);
    }

    public function contentPdf(string $contentId): View|Response
    {
        $db = $this->db();

        $content = $db->selectOne(
            'SELECT id, title, synopsis, is_completed, published_at FROM content WHERE id = ? AND is_published=1 AND is_deleted=0',
            [$contentId]
        );

        if (! $content) {
            abort(404, 'Konten tidak ditemukan.');
        }

        $chapters = $db->select(
            'SELECT sequence, title, body FROM chapters
             WHERE content_id = ? AND is_published = 1 AND is_deleted = 0
             ORDER BY sequence ASC',
            [$contentId]
        );

        return response()->view('admin.reports.content-pdf', compact('content', 'chapters'))
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function chapterDropoff(): View
    {
        return view('admin.reports.chapter-dropoff');
    }

    public function chapterFunnel(string $contentId): JsonResponse
    {
        $db = $this->db();

        $content = $db->selectOne(
            'SELECT id, title FROM content WHERE id = ? AND is_published = 1 AND is_deleted = 0',
            [$contentId]
        );

        if (! $content) {
            return response()->json(['error' => 'Konten tidak ditemukan.'], 404);
        }

        $chapters = $db->select(
            'SELECT sequence, title, read_count
             FROM chapters
             WHERE content_id = ? AND is_published = 1 AND is_deleted = 0
             ORDER BY sequence ASC',
            [$contentId]
        );

        if (empty($chapters)) {
            return response()->json([
                'content' => ['id' => $content->id, 'title' => $content->title, 'total_chapters' => 0],
                'chapters' => [],
            ]);
        }

        $firstCount = (int) $chapters[0]->read_count;
        $prevCount = $firstCount;

        $result = [];
        foreach ($chapters as $ch) {
            $count = (int) $ch->read_count;
            $retentionPct = $firstCount > 0 ? round($count / $firstCount * 100, 1) : 0;
            $dropoffPct = $prevCount > 0 ? round(($prevCount - $count) / $prevCount * 100, 1) : 0;

            $result[] = [
                'sequence' => (int) $ch->sequence,
                'title' => $ch->title,
                'read_count' => $count,
                'retention_pct' => $retentionPct,
                'dropoff_pct' => max(0, $dropoffPct),
            ];

            $prevCount = $count;
        }

        return response()->json([
            'content' => [
                'id' => $content->id,
                'title' => $content->title,
                'total_chapters' => count($chapters),
            ],
            'chapters' => $result,
        ]);
    }

    // ── Acquisition & Referral ───────────────────────────────────────────────

    public function acquisition(Request $request): View
    {
        $db = $this->db();
        $perPage = 10;

        // Funnel KPI
        $funnel = $db->selectOne("
            SELECT
                (SELECT COUNT(*) FROM users) AS registered,
                (SELECT COUNT(*) FROM users WHERE last_login_at IS NOT NULL) AS activated,
                (SELECT COUNT(DISTINCT user_id) FROM user_read) AS readers,
                (SELECT COUNT(DISTINCT user_id) FROM transactions WHERE status='paid') AS paying
        ");

        // Weekly registration trend 12 weeks
        $regWeekly = $db->select('
            SELECT DATE(DATE_SUB(created_at, INTERVAL WEEKDAY(created_at) DAY)) AS week_start,
                   COUNT(*) AS new_users
            FROM users
            WHERE created_at >= NOW() - INTERVAL 12 WEEK
            GROUP BY week_start
            ORDER BY week_start
        ');

        // UTM attribution breakdown — paginated
        $utmTotal = (int) ($db->selectOne('
            SELECT COUNT(*) AS cnt FROM (
                SELECT 1 FROM attribution_events
                GROUP BY COALESCE(NULLIF(utm_source,\'\'),\'organic\'),
                         COALESCE(NULLIF(utm_medium,\'\'),\'-\'),
                         COALESCE(NULLIF(utm_campaign,\'\'),\'-\')
            ) sub
        ')->cnt ?? 0);
        $utmPage = max(1, (int) $request->query('utm_page', 1));
        $utmTotalPages = max(1, (int) ceil($utmTotal / $perPage));
        $utmPage = min($utmPage, $utmTotalPages);
        $utmOffset = ($utmPage - 1) * $perPage;

        $utmBreakdown = $db->select("
            SELECT
                COALESCE(NULLIF(ae.utm_source,''), 'organic') AS source,
                COALESCE(NULLIF(ae.utm_medium,''), '-') AS medium,
                COALESCE(NULLIF(ae.utm_campaign,''), '-') AS campaign,
                COUNT(DISTINCT ae.user_id) AS users,
                COUNT(*) AS events
            FROM attribution_events ae
            GROUP BY source, medium, campaign
            ORDER BY users DESC, events DESC
            LIMIT {$perPage} OFFSET {$utmOffset}
        ");

        // Share activity — platform column removed
        $shareByPlatform = [];

        // Most shared content — paginated
        $sharedTotal = (int) ($db->selectOne('
            SELECT COUNT(DISTINCT us.content_id) AS cnt
            FROM user_share us
            JOIN content c ON c.id = us.content_id
        ')->cnt ?? 0);
        $sharedPage = max(1, (int) $request->query('shared_page', 1));
        $sharedTotalPages = max(1, (int) ceil($sharedTotal / $perPage));
        $sharedPage = min($sharedPage, $sharedTotalPages);
        $sharedOffset = ($sharedPage - 1) * $perPage;

        $mostShared = $db->select("
            SELECT c.title, COUNT(*) AS shares,
                   COUNT(DISTINCT us.user_id) AS unique_users
            FROM user_share us
            JOIN content c ON c.id = us.content_id
            GROUP BY us.content_id, c.title
            ORDER BY shares DESC
            LIMIT {$perPage} OFFSET {$sharedOffset}
        ");

        // Short links — paginated
        $slTotal = (int) ($db->selectOne('SELECT COUNT(*) AS cnt FROM short_links')->cnt ?? 0);
        $slPage = max(1, (int) $request->query('sl_page', 1));
        $slTotalPages = max(1, (int) ceil($slTotal / $perPage));
        $slPage = min($slPage, $slTotalPages);
        $slOffset = ($slPage - 1) * $perPage;

        $shortLinks = $db->select("
            SELECT sl.code, sl.affiliate_code, sl.utm_medium, sl.utm_campaign,
                   sl.created_at,
                   (SELECT COUNT(*) FROM attribution_events ae
                    WHERE ae.affiliate_code = sl.affiliate_code) AS clicks
            FROM short_links sl
            ORDER BY sl.created_at DESC
            LIMIT {$perPage} OFFSET {$slOffset}
        ");

        // UTM vs organic user counts
        $utmUserCount = $db->selectOne('
            SELECT
                COUNT(DISTINCT CASE WHEN utm_source IS NOT NULL AND utm_source != \'\' THEN user_id END) AS from_paid,
                (SELECT COUNT(*) FROM users) -
                COUNT(DISTINCT CASE WHEN utm_source IS NOT NULL AND utm_source != \'\' THEN user_id END) AS organic
            FROM attribution_events
        ');

        // Conversion rate by UTM source
        $conversionBySource = $db->select("
            SELECT
                COALESCE(NULLIF(ae.utm_source,''), 'organic') AS source,
                COUNT(DISTINCT ae.user_id) AS attributed_users,
                COUNT(DISTINCT CASE WHEN t.status='paid' THEN t.user_id END) AS paying_users,
                COALESCE(SUM(CASE WHEN t.status='paid' THEN t.total_amount ELSE 0 END), 0) AS revenue
            FROM attribution_events ae
            LEFT JOIN transactions t ON t.user_id = ae.user_id AND t.status = 'paid'
            GROUP BY source
            ORDER BY revenue DESC
        ");

        return view('admin.reports.acquisition', compact(
            'funnel', 'regWeekly', 'utmBreakdown', 'shareByPlatform',
            'mostShared', 'shortLinks', 'utmUserCount', 'conversionBySource',
            'perPage',
            'utmPage', 'utmTotal', 'utmTotalPages',
            'sharedPage', 'sharedTotal', 'sharedTotalPages', 'sharedOffset',
            'slPage', 'slTotal', 'slTotalPages'
        ));
    }

    public function userActivity(Request $request): View
    {
        $db = $this->db();

        // ── KPI ────────────────────────────────────────────────────────────────
        $kpi = $db->selectOne("
            SELECT
                (SELECT COUNT(*) FROM users) AS total_users,
                (SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()) AS new_today,
                (SELECT COUNT(*) FROM users WHERE created_at >= NOW() - INTERVAL 7 DAY) AS new_7d,
                (SELECT COUNT(*) FROM users WHERE created_at >= NOW() - INTERVAL 30 DAY) AS new_30d,
                (SELECT COUNT(*) FROM log_events WHERE name = 'login' AND DATE(created_at) = CURDATE()) AS logins_today,
                (SELECT COUNT(DISTINCT `user`) FROM log_events WHERE name = 'login' AND created_at >= NOW() - INTERVAL 7 DAY) AS logins_7d,
                (SELECT COUNT(DISTINCT user_id) FROM user_read WHERE DATE(created_at) = CURDATE()) AS active_today,
                (SELECT COUNT(*) FROM users WHERE last_login_at IS NULL) AS never_logged_in
        ");

        // ── 30-day trend: registrations & logins ──────────────────────────────
        $regTrend = $db->select('
            SELECT DATE(created_at) AS `date`, COUNT(*) AS cnt
            FROM users
            WHERE created_at >= NOW() - INTERVAL 30 DAY
            GROUP BY DATE(created_at)
            ORDER BY `date`
        ');

        $loginTrend = $db->select("
            SELECT DATE(created_at) AS `date`, COUNT(*) AS logins
            FROM log_events
            WHERE name = 'login' AND created_at >= NOW() - INTERVAL 30 DAY
            GROUP BY DATE(created_at)
            ORDER BY `date`
        ");

        // Merge into a unified 30-day array keyed by date
        $regByDate = collect($regTrend)->keyBy('date');
        $loginByDate = collect($loginTrend)->keyBy('date');
        $trendDates = [];
        for ($i = 29; $i >= 0; $i--) {
            $trendDates[] = now()->subDays($i)->format('Y-m-d');
        }
        $trendData = array_map(fn ($d) => [
            'date' => $d,
            'reg' => $regByDate->get($d)?->cnt ?? 0,
            'logins' => $loginByDate->get($d)?->logins ?? 0,
        ], $trendDates);

        // ── Active users per hour today ────────────────────────────────────────
        $activeByHour = $db->select('
            SELECT HOUR(created_at) AS hr, COUNT(DISTINCT user_id) AS cnt
            FROM user_read
            WHERE DATE(created_at) = CURDATE()
            GROUP BY HOUR(created_at)
            ORDER BY hr
        ');

        // ── User table (paginated) ─────────────────────────────────────────────
        $filter = $request->query('filter', 'all');  // all | new | subscribed | never_login
        $search = trim((string) $request->query('search', ''));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $whereFilter = match ($filter) {
            'new' => 'AND u.created_at >= NOW() - INTERVAL 7 DAY',
            'subscribed' => 'AND um.is_active = 1',
            'never_login' => 'AND u.last_login_at IS NULL',
            default => '',
        };

        $joinFilter = ($filter === 'subscribed')
            ? 'JOIN user_memberships um ON um.user_id = u.id AND um.is_active = 1'
            : 'LEFT JOIN user_memberships um ON um.user_id = u.id AND um.is_active = 1';

        $whereSearch = '';
        $searchBindings = [];
        if ($search !== '') {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';
            $whereSearch = 'AND (u.name LIKE ? OR u.email LIKE ?)';
            $searchBindings = [$like, $like];
        }

        $countSql = "
            SELECT COUNT(DISTINCT u.id) AS cnt
            FROM users u
            {$joinFilter}
            LEFT JOIN profile p ON p.user_id = u.id
            WHERE 1=1 {$whereFilter} {$whereSearch}
        ";
        $total = $db->selectOne($countSql, $searchBindings)->cnt;
        $totalPages = (int) ceil($total / $perPage);

        $users = $db->select("
            SELECT u.id, u.name, u.email, p.phone_number,
                   u.created_at AS registered_at,
                   u.last_login_at,
                   (SELECT MAX(ur.created_at) FROM user_read ur WHERE ur.user_id = u.id) AS last_read_at,
                   (SELECT COUNT(*) FROM log_events le WHERE le.`user` = u.id AND le.name = 'login') AS login_count,
                   um.is_active AS has_membership,
                   (SELECT mp.name FROM user_memberships um2
                    JOIN membership_plans mp ON mp.id = um2.plan_id
                    WHERE um2.user_id = u.id AND um2.is_active = 1
                    ORDER BY um2.created_at DESC LIMIT 1) AS active_plan,
                   (SELECT um3.expired_at FROM user_memberships um3
                    WHERE um3.user_id = u.id AND um3.is_active = 1
                    ORDER BY um3.created_at DESC LIMIT 1) AS membership_expires_at
            FROM users u
            {$joinFilter}
            LEFT JOIN profile p ON p.user_id = u.id
            WHERE 1=1 {$whereFilter} {$whereSearch}
            GROUP BY u.id, u.name, u.email, p.phone_number, u.created_at, u.last_login_at, um.is_active
            ORDER BY u.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ", $searchBindings);

        // ── Registration source (from attribution_events) ──────────────────────
        $acqSource = $db->select("
            SELECT
                COALESCE(NULLIF(utm_source,''), 'organic') AS source,
                COUNT(DISTINCT user_id) AS cnt
            FROM attribution_events
            GROUP BY source
            ORDER BY cnt DESC
        ");

        return view('admin.reports.user-activity', compact(
            'kpi', 'trendData', 'activeByHour', 'acqSource',
            'users', 'page', 'perPage', 'total', 'totalPages', 'filter', 'search'
        ));
    }

    // ── User Story Recommendation ────────────────────────────────────────────

    public function userRecommend(string $userId): JsonResponse
    {
        $db = $this->db();

        $user = $db->selectOne("
            SELECT u.id, u.name, u.email, p.phone_number,
                   u.created_at AS registered_at,
                   (SELECT COUNT(*) FROM user_read WHERE user_id = u.id) AS total_chapters,
                   (SELECT COUNT(DISTINCT content_id) FROM read_history WHERE user_id = u.id AND is_deleted = 0) AS total_books,
                   EXISTS(
                       SELECT 1 FROM transactions WHERE user_id = u.id AND status = 'paid'
                       AND expired_at > NOW()
                   ) AS has_membership
            FROM users u
            LEFT JOIN profile p ON p.user_id = u.id
            WHERE u.id = ?
        ", [$userId]);

        if (! $user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        $topCategories = $db->select('
            SELECT mcc.id, mcc.name, COUNT(rh.id) AS read_count
            FROM read_history rh
            JOIN content c ON c.id = rh.content_id AND c.is_deleted = 0
            JOIN master_content_category mcc ON mcc.id = c.category_id
            WHERE rh.user_id = ? AND rh.is_deleted = 0
            GROUP BY mcc.id, mcc.name
            ORDER BY read_count DESC
            LIMIT 4
        ', [$userId]);

        $recentReads = $db->select('
            SELECT c.title, mcc.name AS category, rh.created_at
            FROM read_history rh
            JOIN content c ON c.id = rh.content_id AND c.is_deleted = 0
            LEFT JOIN master_content_category mcc ON mcc.id = c.category_id
            WHERE rh.user_id = ? AND rh.is_deleted = 0
            ORDER BY rh.created_at DESC
            LIMIT 6
        ', [$userId]);

        // Build a single diversified-score query.
        // Score = category relevance (0-40) + freshness (0-25) + log-popularity (0-25) + quality (0-10) + discovery noise (0-5)
        // This surfaces new/less-popular content alongside top titles instead of always returning the same highest-subs list.
        $categoryWeights = [40, 30, 20, 10];

        if (empty($topCategories)) {
            // Cold start: no reading history — balance freshness, popularity and discovery
            $recommendations = $db->select('
                SELECT c.id, c.title, c.slug, c.synopsis, c.tags,
                       c.subscribe_count, c.read_count, c.rating,
                       mcc.name AS category,
                       (
                           GREATEST(0, 25 - FLOOR(DATEDIFF(NOW(), COALESCE(c.published_at, c.created_at)) / 3.6))
                           + LEAST(25, FLOOR(LOG(GREATEST(c.subscribe_count + 1, 1)) * 5))
                           + LEAST(10, FLOOR(c.rating * 2))
                           + (RAND() * 5)
                       ) AS rec_score
                FROM content c
                LEFT JOIN master_content_category mcc ON mcc.id = c.category_id
                WHERE c.is_published = 1 AND c.is_deleted = 0
                ORDER BY rec_score DESC
                LIMIT 5
            ');
        } else {
            // Build CASE WHEN for category affinity score
            $catCaseWhen = '';
            $catParams = [];
            foreach ($topCategories as $i => $cat) {
                $weight = $categoryWeights[$i] ?? 5;
                $catCaseWhen .= " WHEN c.category_id = ? THEN {$weight}";
                $catParams[] = $cat->id;
            }

            $recommendations = $db->select("
                SELECT c.id, c.title, c.slug, c.synopsis, c.tags,
                       c.subscribe_count, c.read_count, c.rating,
                       mcc.name AS category,
                       (
                           CASE {$catCaseWhen} ELSE 0 END
                           + GREATEST(0, 25 - FLOOR(DATEDIFF(NOW(), COALESCE(c.published_at, c.created_at)) / 3.6))
                           + LEAST(25, FLOOR(LOG(GREATEST(c.subscribe_count + 1, 1)) * 5))
                           + LEAST(10, FLOOR(c.rating * 2))
                           + (RAND() * 5)
                       ) AS rec_score
                FROM content c
                LEFT JOIN master_content_category mcc ON mcc.id = c.category_id
                WHERE c.is_published = 1 AND c.is_deleted = 0
                  AND c.id NOT IN (
                      SELECT DISTINCT content_id FROM read_history
                      WHERE user_id = ? AND is_deleted = 0
                  )
                ORDER BY rec_score DESC
                LIMIT 5
            ", array_merge($catParams, [$userId]));
        }

        return response()->json([
            'user' => $user,
            'top_categories' => $topCategories,
            'recent_reads' => $recentReads,
            'recommendations' => $recommendations,
        ]);
    }

    public function previewRecommendEmail(string $userId): Response
    {
        ['user' => $user, 'recs' => $recs, 'cats' => $cats] = $this->fetchRecommendData($userId);
        if (! $user) {
            abort(404);
        }

        return response($this->buildRecommendEmailHtml($user, $recs, $cats), 200, ['Content-Type' => 'text/html']);
    }

    public function sendRecommendEmail(Request $request, string $userId): JsonResponse
    {
        ['user' => $user, 'recs' => $recs, 'cats' => $cats] = $this->fetchRecommendData($userId);
        if (! $user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        $email = $request->input('email') ?: $user->email;
        if (! $email) {
            return response()->json(['error' => 'Email penerima tidak tersedia'], 422);
        }

        $subject = $request->input('subject', 'Cerita pilihan untukmu dari Novelya 📚');
        $html = $this->buildRecommendEmailHtml($user, $recs, $cats);

        $result = app(BrevoService::class)->sendBatch(
            [['email' => $email, 'name' => $user->name ?: '', 'params' => []]],
            $subject,
            $html
        );

        if (! $result['success']) {
            return response()->json(['error' => $result['error'] ?? 'Gagal mengirim email'], 500);
        }

        return response()->json(['ok' => true]);
    }

    /** @return array{user: ?object, recs: array, cats: array} */
    private function fetchRecommendData(string $userId): array
    {
        $db = $this->db();
        $user = $db->selectOne('
            SELECT u.id, u.name, u.email
            FROM users u
            WHERE u.id = ?
        ', [$userId]);

        if (! $user) {
            return ['user' => null, 'recs' => [], 'cats' => []];
        }

        $cats = $db->select('
            SELECT mcc.id, mcc.name, COUNT(rh.id) AS read_count
            FROM read_history rh
            JOIN content c ON c.id = rh.content_id AND c.is_deleted = 0
            JOIN master_content_category mcc ON mcc.id = c.category_id
            WHERE rh.user_id = ? AND rh.is_deleted = 0
            GROUP BY mcc.id, mcc.name
            ORDER BY read_count DESC
            LIMIT 4
        ', [$userId]);

        $weights = [40, 30, 20, 10];

        if (empty($cats)) {
            $recs = $db->select('
                SELECT c.title, c.slug, c.synopsis, c.cover_image, mcc.name AS category
                FROM content c
                LEFT JOIN master_content_category mcc ON mcc.id = c.category_id
                WHERE c.is_published = 1 AND c.is_deleted = 0
                ORDER BY (
                    GREATEST(0, 25 - FLOOR(DATEDIFF(NOW(), COALESCE(c.published_at, c.created_at)) / 3.6))
                    + LEAST(25, FLOOR(LOG(GREATEST(c.subscribe_count + 1, 1)) * 5))
                    + LEAST(10, FLOOR(c.rating * 2))
                ) DESC
                LIMIT 3
            ');
        } else {
            $caseWhen = '';
            $catParams = [];
            foreach ($cats as $i => $cat) {
                $w = $weights[$i] ?? 5;
                $caseWhen .= " WHEN c.category_id = ? THEN {$w}";
                $catParams[] = $cat->id;
            }
            $recs = $db->select("
                SELECT c.title, c.slug, c.synopsis, c.cover_image, mcc.name AS category
                FROM content c
                LEFT JOIN master_content_category mcc ON mcc.id = c.category_id
                WHERE c.is_published = 1 AND c.is_deleted = 0
                  AND c.id NOT IN (SELECT DISTINCT content_id FROM read_history WHERE user_id = ? AND is_deleted = 0)
                ORDER BY (
                    CASE {$caseWhen} ELSE 0 END
                    + GREATEST(0, 25 - FLOOR(DATEDIFF(NOW(), COALESCE(c.published_at, c.created_at)) / 3.6))
                    + LEAST(25, FLOOR(LOG(GREATEST(c.subscribe_count + 1, 1)) * 5))
                    + LEAST(10, FLOOR(c.rating * 2))
                ) DESC
                LIMIT 3
            ", array_merge([$userId], $catParams));
        }

        return ['user' => $user, 'recs' => $recs, 'cats' => $cats];
    }

    private function buildRecommendEmailHtml(object $user, array $recs, array $cats): string
    {
        $novelyaUrl = config('brevo.novelya_url', 'https://novelya.id');
        $year = now()->year;
        $name = htmlspecialchars($user->name ?: 'Kamu', ENT_QUOTES, 'UTF-8');

        $genreLine = '';
        if (! empty($cats)) {
            $genres = implode(', ', array_map(
                fn ($c) => htmlspecialchars($c->name, ENT_QUOTES, 'UTF-8'),
                array_slice((array) $cats, 0, 3)
            ));
            $genreLine = "<p style=\"margin:12px 0 0;font-size:13px;color:rgba(255,255,255,0.8);font-family:sans-serif;\">Genre favoritmu: <strong>{$genres}</strong></p>";
        }

        $accentColors = [
            ['#7c3aed', '#4338ca'],
            ['#db2777', '#be185d'],
            ['#0891b2', '#0e7490'],
        ];

        $storyCards = '';
        foreach ($recs as $i => $rec) {
            $title = htmlspecialchars($rec->title ?? '', ENT_QUOTES, 'UTF-8');
            $category = htmlspecialchars($rec->category ?? '', ENT_QUOTES, 'UTF-8');
            $url = $novelyaUrl.'/detail/'.($rec->slug ?? '');
            $coverUrl = ! empty($rec->cover_image) ? htmlspecialchars($rec->cover_image, ENT_QUOTES, 'UTF-8') : '';
            $synopsis = '';
            if (! empty($rec->synopsis)) {
                $text = mb_strlen($rec->synopsis) > 160
                    ? mb_substr($rec->synopsis, 0, 160).'...'
                    : $rec->synopsis;
                $synopsis = '<p style="margin:8px 0 0;font-size:13px;color:#6b7280;line-height:1.65;font-family:sans-serif;">'.htmlspecialchars($text, ENT_QUOTES, 'UTF-8').'</p>';
            }
            [$c1, $c2] = $accentColors[$i] ?? ['#7c3aed', '#4338ca'];
            $num = (string) ($i + 1);
            $catBadge = $category
                ? "<p style=\"margin:6px 0 0;\"><span style=\"font-size:11px;font-weight:700;color:{$c1};background:#f5f3ff;padding:3px 10px;border-radius:20px;font-family:sans-serif;\">{$category}</span></p>"
                : '';
            $coverCell = $coverUrl
                ? "<td width=\"100\" valign=\"top\" style=\"padding-right:18px;\"><a href=\"{$url}\"><img src=\"{$coverUrl}\" width=\"90\" alt=\"\" style=\"display:block;width:90px;height:130px;object-fit:cover;border-radius:10px;border:0;\"></a></td>"
                : "<td width=\"44\" valign=\"top\" style=\"padding-right:14px;\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"36\" height=\"36\" align=\"center\" valign=\"middle\" style=\"width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,{$c1},{$c2});color:#ffffff;font-size:18px;font-weight:800;font-family:sans-serif;text-align:center;\">{$num}</td></tr></table></td>";

            $storyCards .= <<<CARD
<tr>
  <td style="background:#ffffff;padding:6px 32px;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:2px solid #f3f0ff;border-radius:14px;">
      <tr>
        <td style="padding:20px 22px;">
          <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
              {$coverCell}
              <td valign="top">
                <p style="margin:0;font-size:17px;font-weight:700;color:#111827;line-height:1.3;font-family:sans-serif;">{$title}</p>
                {$catBadge}
                {$synopsis}
                <a href="{$url}" style="display:inline-block;margin-top:12px;font-size:13px;font-weight:700;color:{$c1};text-decoration:none;border:2px solid {$c1};padding:6px 18px;border-radius:20px;font-family:sans-serif;">Baca Sekarang &rarr;</a>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>
CARD;
        }

        if ($storyCards === '') {
            $storyCards = '<tr><td style="background:#ffffff;padding:20px 40px;text-align:center;color:#9ca3af;font-size:14px;font-family:sans-serif;">Tidak ada rekomendasi tersedia saat ini.</td></tr>';
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Cerita pilihan untuk {$name}</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f3ff;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f3ff;">
  <tr>
    <td align="center" style="padding:32px 16px;">
      <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

        <tr>
          <td style="background:linear-gradient(135deg,#7c3aed 0%,#4338ca 100%);border-radius:20px 20px 0 0;padding:40px 40px 36px;">
            <p style="margin:0 0 6px;font-size:24px;font-weight:800;color:#ffffff;letter-spacing:-0.5px;font-family:sans-serif;">&#128218; Novelya</p>
            <p style="margin:0 0 24px;font-size:13px;color:rgba(255,255,255,0.65);font-family:sans-serif;">Platform cerita digital terbaik</p>
            <h1 style="margin:0;font-size:30px;font-weight:800;color:#ffffff;line-height:1.25;letter-spacing:-0.5px;font-family:sans-serif;">Hai {$name}! &#128075;</h1>
            <p style="margin:8px 0 0;font-size:17px;font-weight:500;color:rgba(255,255,255,0.9);line-height:1.5;font-family:sans-serif;">Ada cerita-cerita yang cocok banget buat kamu &#127919;</p>
            {$genreLine}
          </td>
        </tr>

        <tr>
          <td style="background:#ffffff;padding:28px 40px 12px;">
            <p style="margin:0;font-size:15px;color:#374151;line-height:1.8;font-family:sans-serif;">
              Kami tahu kamu sibuk, tapi percaya deh &mdash; cerita-cerita di bawah ini worth banget buat dibaca. Dipilihkan khusus berdasarkan selera bacaanmu, jadi kemungkinan besar bakal langsung bikin ketagihan! &#128522;
            </p>
          </td>
        </tr>

        <tr>
          <td style="background:#ffffff;padding:16px 40px 8px;">
            <p style="margin:0;font-size:11px;font-weight:700;color:#9ca3af;letter-spacing:2px;text-transform:uppercase;font-family:sans-serif;">PILIHAN UNTUKMU</p>
          </td>
        </tr>

        {$storyCards}

        <tr><td style="background:#ffffff;height:12px;"></td></tr>

        <tr>
          <td style="background:#ffffff;padding:0 40px;">
            <hr style="border:none;border-top:2px solid #f3f0ff;margin:0;">
          </td>
        </tr>

        <tr>
          <td style="background:#ffffff;padding:28px 40px 36px;text-align:center;">
            <p style="margin:0 0 20px;font-size:15px;color:#6b7280;line-height:1.7;font-family:sans-serif;">
              Masih banyak cerita seru lainnya yang menunggumu!<br>Buka Novelya dan mulai petualangan bacaanmu sekarang.
            </p>
            <a href="{$novelyaUrl}" style="display:inline-block;background:linear-gradient(135deg,#7c3aed,#4338ca);color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 40px;border-radius:50px;letter-spacing:0.3px;font-family:sans-serif;">
              Buka Novelya Sekarang &rarr;
            </a>
          </td>
        </tr>

        <tr>
          <td style="background:#f5f3ff;border-radius:0 0 20px 20px;padding:20px 40px;text-align:center;">
            <p style="margin:0 0 4px;font-size:12px;color:#9ca3af;font-family:sans-serif;">Kamu menerima email ini karena terdaftar di Novelya.</p>
            <p style="margin:0;font-size:12px;color:#c4b5fd;font-family:sans-serif;">&#169; {$year} Novelya. All rights reserved.</p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>
HTML;
    }

    public function revenueDaily(Request $request): View
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $month = max(1, min(12, $month));
        $year = max(2020, min((int) now()->year + 1, $year));

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

        // Revenue per day from novel DB
        $rows = $this->db()->select("
            SELECT DATE(paid_at) AS date,
                   COUNT(*) AS trx_count,
                   COALESCE(SUM(total_amount), 0) AS revenue
            FROM transactions
            WHERE status = 'paid'
              AND paid_at >= ?
              AND paid_at < DATE_ADD(?, INTERVAL 1 DAY)
            GROUP BY DATE(paid_at)
            ORDER BY date ASC
        ", [$startDate, $endDate]);

        $revenueMap = collect($rows)->keyBy('date');

        // Marketing costs from SQLite — use raw query builder so date keys stay as 'Y-m-d' strings
        $costs = DB::connection('sqlite')
            ->table('daily_revenue_costs')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->pluck('marketing_cost', 'date')
            ->map(fn ($v) => (int) $v);

        // Build full day-by-day array
        $days = [];
        $prevRevenue = null;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $row = $revenueMap[$dateStr] ?? null;
            $revenue = $row ? (int) $row->revenue : 0;
            $trxCount = $row ? (int) $row->trx_count : 0;
            $marketingCost = $costs[$dateStr] ?? 0;
            $profit = $revenue - $marketingCost;

            $growth = null;
            if ($prevRevenue !== null && $prevRevenue > 0) {
                $growth = round(($revenue - $prevRevenue) / $prevRevenue * 100, 1);
            } elseif ($prevRevenue === 0 && $revenue > 0) {
                $growth = null; // infinity, show as new
            }

            $days[] = [
                'date' => $dateStr,
                'day' => $d,
                'trx_count' => $trxCount,
                'revenue' => $revenue,
                'marketing_cost' => $marketingCost,
                'profit' => $profit,
                'growth' => $growth,
                'has_data' => $revenue > 0,
            ];

            $prevRevenue = $revenue;
        }

        // Summary KPIs
        $totalRevenue = collect($days)->sum('revenue');
        $totalTrx = collect($days)->sum('trx_count');
        $totalMarketingCost = collect($days)->sum('marketing_cost');
        $totalProfit = $totalRevenue - $totalMarketingCost;
        $bestDay = collect($days)->sortByDesc('revenue')->first();

        $activeDays = collect($days)->where('has_data', true)->count();
        $avgRevenue = $activeDays > 0 ? round($totalRevenue / $activeDays) : 0;

        $years = range(2023, (int) now()->year);

        return view('admin.reports.revenue_daily', compact(
            'days', 'month', 'year', 'years',
            'totalRevenue', 'totalTrx', 'totalMarketingCost', 'totalProfit',
            'bestDay', 'avgRevenue', 'activeDays'
        ));
    }

    public function userDaily(Request $request): View
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $month = max(1, min(12, $month));
        $year = max(2020, min((int) now()->year + 1, $year));

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

        // Unique DAU from user_read (distinct user_id per day — excludes anonymous sessions)
        $dauRows = $this->db()->select('
            SELECT DATE(created_at) AS date, COUNT(DISTINCT user_id) AS akses
            FROM user_read
            WHERE created_at >= ? AND created_at < DATE_ADD(?, INTERVAL 1 DAY)
              AND user_id IS NOT NULL
            GROUP BY DATE(created_at)
        ', [$startDate, $endDate]);

        $dauMap = collect($dauRows)->keyBy('date');

        // New users + read count from recap_daily (pre-aggregated)
        $recapRows = $this->db()->select('
            SELECT `date`, new_user, `read`
            FROM recap_daily
            WHERE `date` >= ? AND `date` <= ?
        ', [$startDate, $endDate]);

        $recapMap = collect($recapRows)->keyBy('date');

        $days = [];
        $prevAkses = null;
        $prevNewUser = null;

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);

            $akses = (int) ($dauMap[$dateStr]->akses ?? 0);
            $newUser = (int) ($recapMap[$dateStr]->new_user ?? 0);
            $read = (int) ($recapMap[$dateStr]->read ?? 0);

            $aksesGrowth = null;
            if ($prevAkses !== null && $prevAkses > 0) {
                $aksesGrowth = round(($akses - $prevAkses) / $prevAkses * 100, 1);
            } elseif ($prevAkses === 0 && $akses > 0) {
                $aksesGrowth = null;
            }

            $newUserGrowth = null;
            if ($prevNewUser !== null && $prevNewUser > 0) {
                $newUserGrowth = round(($newUser - $prevNewUser) / $prevNewUser * 100, 1);
            } elseif ($prevNewUser === 0 && $newUser > 0) {
                $newUserGrowth = null;
            }

            $days[] = [
                'date' => $dateStr,
                'day' => $d,
                'akses' => $akses,
                'new_user' => $newUser,
                'read' => $read,
                'akses_growth' => $aksesGrowth,
                'new_user_growth' => $newUserGrowth,
                'has_data' => $akses > 0 || $newUser > 0,
            ];

            $prevAkses = $akses;
            $prevNewUser = $newUser;
        }

        $totalAkses = collect($days)->sum('akses');
        $totalNewUser = collect($days)->sum('new_user');
        $totalRead = collect($days)->sum('read');

        $activeDays = collect($days)->where('has_data', true)->count();
        $avgAkses = $activeDays > 0 ? round($totalAkses / $activeDays) : 0;
        $avgNewUser = $activeDays > 0 ? round($totalNewUser / $activeDays, 1) : 0;

        $peakAksesDay = collect($days)->sortByDesc('akses')->first();
        $peakNewUserDay = collect($days)->sortByDesc('new_user')->first();

        $years = range(2023, (int) now()->year);

        return view('admin.reports.user_daily', compact(
            'days', 'month', 'year', 'years',
            'totalAkses', 'totalNewUser', 'totalRead',
            'activeDays', 'avgAkses', 'avgNewUser',
            'peakAksesDay', 'peakNewUserDay'
        ));
    }

    public function saveMarketingCost(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
            'cost' => 'required|integer|min:0',
        ]);

        DailyRevenueCost::updateOrCreate(
            ['date' => $request->date],
            ['marketing_cost' => $request->cost]
        );

        // Return updated profit
        $rows = $this->db()->select("
            SELECT COALESCE(SUM(total_amount), 0) AS revenue
            FROM transactions
            WHERE status = 'paid' AND DATE(paid_at) = ?
        ", [$request->date]);

        $revenue = $rows[0]->revenue ?? 0;
        $profit = $revenue - $request->cost;

        return response()->json(['profit' => $profit, 'revenue' => $revenue]);
    }

    // ── Author Analytics ─────────────────────────────────────────────────────

    public function authorAnalytics(Request $request): View
    {
        $db = $this->db();

        $sort = $request->query('sort', 'reads');
        $search = trim($request->query('search', ''));

        $orderMap = [
            'reads' => 'total_reads DESC',
            'views' => 'total_views DESC',
            'rating' => 'avg_rating DESC',
            'readers' => 'unique_readers DESC',
            'content' => 'content_count DESC',
        ];
        $orderSql = $orderMap[$sort] ?? $orderMap['reads'];

        $searchWhere = '';
        $searchParam = [];
        if ($search !== '') {
            $searchWhere = ' AND u.name LIKE ?';
            $searchParam[] = '%'.$search.'%';
        }

        $authors = $db->select("
            SELECT
                u.id, u.name, u.email,
                COUNT(DISTINCT c.id)           AS content_count,
                COALESCE(SUM(c.read_count),0)  AS total_reads,
                COALESCE(SUM(c.view_count),0)  AS total_views,
                COALESCE(SUM(c.subscribe_count),0) AS total_subscribes,
                ROUND(AVG(NULLIF(c.rating, 0)), 2) AS avg_rating,
                COUNT(DISTINCT ur.user_id)     AS unique_readers
            FROM users u
            JOIN content c ON c.user_id = u.id AND c.is_published = 1 AND c.is_deleted = 0
            LEFT JOIN user_read ur ON ur.content_id = c.id
            WHERE 1=1 {$searchWhere}
            GROUP BY u.id, u.name, u.email
            HAVING content_count > 0
            ORDER BY {$orderSql}
        ", $searchParam);

        return view('admin.reports.authors', compact('authors', 'sort', 'search'));
    }

    public function authorDetail(string $userId): View
    {
        $db = $this->db();

        $author = $db->selectOne(
            'SELECT id, name, email, created_at FROM users WHERE id = ?',
            [$userId]
        );

        if (! $author) {
            abort(404, 'Author tidak ditemukan.');
        }

        $contents = $db->select('
            SELECT c.id, c.title, c.is_published, c.read_count, c.view_count, c.subscribe_count,
                   c.rating, c.is_completed, c.published_at,
                   mcc.name AS category,
                   (SELECT COUNT(*) FROM chapters ch WHERE ch.content_id = c.id AND ch.is_published=1 AND ch.is_deleted=0) AS chapter_count,
                   COUNT(DISTINCT ur.user_id) AS unique_readers,
                   (SELECT ch_first.read_count FROM chapters ch_first
                    WHERE ch_first.content_id = c.id AND ch_first.is_published=1 AND ch_first.is_deleted=0
                    ORDER BY ch_first.sequence ASC LIMIT 1) AS first_ch_reads,
                   (SELECT ch_last.read_count FROM chapters ch_last
                    WHERE ch_last.content_id = c.id AND ch_last.is_published=1 AND ch_last.is_deleted=0
                    ORDER BY ch_last.sequence DESC LIMIT 1) AS last_ch_reads
            FROM content c
            LEFT JOIN master_content_category mcc ON mcc.id = c.category_id
            LEFT JOIN user_read ur ON ur.content_id = c.id
            WHERE c.user_id = ? AND c.is_published = 1 AND c.is_deleted = 0
            GROUP BY c.id, c.title, c.is_published, c.read_count, c.view_count, c.subscribe_count,
                     c.rating, c.is_completed, c.published_at, mcc.name
            ORDER BY c.read_count DESC
        ', [$userId]);

        $paying_readers = $db->selectOne("
            SELECT COUNT(DISTINCT t.user_id) AS cnt
            FROM transactions t
            WHERE t.status = 'paid'
              AND EXISTS (
                  SELECT 1 FROM user_read ur
                  JOIN content c ON c.id = ur.content_id AND c.user_id = ?
                  WHERE ur.user_id = t.user_id
              )
        ", [$userId]);

        $completionRates = array_filter(array_map(function ($c) {
            if (! $c->first_ch_reads || $c->first_ch_reads == 0) {
                return null;
            }

            return round($c->last_ch_reads / $c->first_ch_reads * 100, 1);
        }, $contents));

        $avgCompletion = count($completionRates) > 0
            ? round(array_sum($completionRates) / count($completionRates), 1)
            : null;

        // Build $stats object that the view expects
        $stats = (object) [
            'content_count'  => count($contents),
            'total_reads'    => array_sum(array_column($contents, 'read_count')),
            'unique_readers' => array_sum(array_column($contents, 'unique_readers')),
            'paying_readers' => (int) ($paying_readers->cnt ?? 0),
        ];

        return view('admin.reports.author-detail', compact(
            'author', 'contents', 'stats', 'avgCompletion'
        ));
    }

    // ── User Journey ─────────────────────────────────────────────────────────

    public function userJourney(): View
    {
        $db = $this->db();

        // Funnel counts
        $funnel = $db->selectOne("
            SELECT
                (SELECT COUNT(*) FROM users) AS registered,
                (SELECT COUNT(DISTINCT user_id) FROM user_read) AS ever_read,
                (SELECT COUNT(DISTINCT user_id) FROM transactions WHERE status='paid') AS ever_paid,
                (SELECT COUNT(*) FROM (
                    SELECT user_id FROM transactions WHERE status='paid'
                    GROUP BY user_id HAVING COUNT(*) > 1
                ) r) AS renewed
        ");

        // Time-to-convert averages
        $timeToRead = $db->selectOne('
            SELECT ROUND(AVG(days_to_read), 1) AS avg_days
            FROM (
                SELECT u.id,
                    DATEDIFF(MIN(ur.created_at), u.created_at) AS days_to_read
                FROM users u
                JOIN user_read ur ON ur.user_id = u.id
                GROUP BY u.id
                HAVING days_to_read >= 0
            ) t
        ');

        $timeToPay = $db->selectOne("
            SELECT ROUND(AVG(days_to_pay), 1) AS avg_days
            FROM (
                SELECT ur_first.user_id,
                    DATEDIFF(MIN(t.paid_at), MIN(ur_first.first_read)) AS days_to_pay
                FROM (
                    SELECT user_id, MIN(created_at) AS first_read
                    FROM user_read GROUP BY user_id
                ) ur_first
                JOIN transactions t ON t.user_id = ur_first.user_id AND t.status = 'paid'
                GROUP BY ur_first.user_id
                HAVING days_to_pay >= 0
            ) t
        ");

        $timeToRenew = $db->selectOne("
            SELECT ROUND(AVG(days_to_renew), 1) AS avg_days
            FROM (
                SELECT user_id,
                    DATEDIFF(
                        MIN(CASE WHEN rn = 2 THEN paid_at END),
                        MIN(CASE WHEN rn = 1 THEN paid_at END)
                    ) AS days_to_renew
                FROM (
                    SELECT user_id, paid_at,
                        ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY paid_at ASC) AS rn
                    FROM transactions WHERE status = 'paid'
                ) ranked
                WHERE rn <= 2
                GROUP BY user_id
                HAVING days_to_renew > 0
            ) t
        ");

        // Monthly cohort — last 6 months
        $cohorts = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth()->format('Y-m-d');
            $monthEnd = now()->subMonths($i)->endOfMonth()->format('Y-m-d');
            $label = now()->subMonths($i)->format('M Y');

            $row = $db->selectOne("
                SELECT
                    COUNT(DISTINCT u.id) AS registered,
                    COUNT(DISTINCT ur.user_id) AS ever_read,
                    COUNT(DISTINCT t.user_id) AS ever_paid,
                    COUNT(DISTINCT renew.user_id) AS renewed
                FROM users u
                LEFT JOIN user_read ur ON ur.user_id = u.id
                LEFT JOIN transactions t ON t.user_id = u.id AND t.status = 'paid'
                LEFT JOIN (
                    SELECT user_id FROM transactions WHERE status = 'paid'
                    GROUP BY user_id HAVING COUNT(*) > 1
                ) renew ON renew.user_id = u.id
                WHERE u.created_at BETWEEN ? AND ?
            ", [$monthStart, $monthEnd.' 23:59:59']);

            $cohorts[] = [
                'label' => $label,
                'registered' => (int) $row->registered,
                'ever_read' => (int) $row->ever_read,
                'ever_paid' => (int) $row->ever_paid,
                'renewed' => (int) $row->renewed,
            ];
        }

        return view('admin.reports.user-journey', compact(
            'funnel', 'timeToRead', 'timeToPay', 'timeToRenew', 'cohorts'
        ));
    }

    // ── Revenue Forecasting ──────────────────────────────────────────────────

    public function revenueForecast(): View
    {
        $db = $this->db();

        // Expiring in next 30 days by plan
        $expiringByPlan = $db->select("
            SELECT mp.id AS plan_id, mp.name AS plan_name, mp.price,
                   COUNT(DISTINCT t.user_id) AS expiring_count
            FROM transactions t
            JOIN membership_plans mp ON mp.id = t.plan_id
            WHERE t.status = 'paid'
              AND t.expired_at BETWEEN NOW() AND NOW() + INTERVAL 30 DAY
            GROUP BY mp.id, mp.name, mp.price
            ORDER BY expiring_count DESC
        ");

        // Historical renewal rate per plan (last 90 days):
        // renewal = user who made another paid transaction within 14 days after expiry
        $renewalRateRows = $db->select("
            SELECT mp.id AS plan_id,
                   COUNT(DISTINCT t.user_id) AS expired_count,
                   COUNT(DISTINCT renew.user_id) AS renewed_count
            FROM transactions t
            JOIN membership_plans mp ON mp.id = t.plan_id
            LEFT JOIN transactions renew ON renew.user_id = t.user_id
                AND renew.status = 'paid'
                AND renew.paid_at BETWEEN t.expired_at AND DATE_ADD(t.expired_at, INTERVAL 14 DAY)
                AND renew.id != t.id
            WHERE t.status = 'paid'
              AND t.expired_at BETWEEN NOW() - INTERVAL 90 DAY AND NOW()
            GROUP BY mp.id
        ");

        $renewalRateMap = [];
        foreach ($renewalRateRows as $row) {
            $renewalRateMap[$row->plan_id] = $row->expired_count > 0
                ? round($row->renewed_count / $row->expired_count, 3)
                : 0.3; // default 30% if no data
        }

        // Revenue from renewals forecast
        $renewalForecast = 0;
        $forecastByPlan = [];
        foreach ($expiringByPlan as $plan) {
            $rate = $renewalRateMap[$plan->plan_id] ?? 0.3;
            $expected = round($plan->expiring_count * $rate * $plan->price);
            $renewalForecast += $expected;
            $forecastByPlan[] = [
                'plan_name' => $plan->plan_name,
                'price' => $plan->price,
                'expiring' => $plan->expiring_count,
                'renewal_rate' => round($rate * 100, 1),
                'expected_rev' => $expected,
            ];
        }

        // New subscriptions forecast
        $newUserStats = $db->selectOne("
            SELECT
                COUNT(*) / 30 AS avg_new_per_day,
                (SELECT COUNT(DISTINCT user_id) FROM transactions WHERE status='paid') /
                NULLIF((SELECT COUNT(*) FROM users), 0) AS conversion_rate,
                (SELECT AVG(mp2.price) FROM membership_plans mp2 WHERE mp2.price > 0) AS avg_price
            FROM users
            WHERE created_at >= NOW() - INTERVAL 30 DAY
        ");

        $avgNewPerDay = (float) ($newUserStats->avg_new_per_day ?? 0);
        $conversionRate = (float) ($newUserStats->conversion_rate ?? 0);
        $avgPlanPrice = (float) ($newUserStats->avg_price ?? 0);
        $newSubForecast = round($avgNewPerDay * 30 * $conversionRate * $avgPlanPrice);

        $baseForecast = $renewalForecast + $newSubForecast;
        $pessimistic = round($baseForecast * 0.8);
        $optimistic = round($baseForecast * 1.2);

        // Historical revenue last 3 months
        $historicalRevenue = $db->select("
            SELECT
                DATE_FORMAT(paid_at, '%Y-%m') AS month,
                COALESCE(SUM(total_amount), 0) AS revenue
            FROM transactions
            WHERE status = 'paid'
              AND paid_at >= NOW() - INTERVAL 3 MONTH
              AND paid_at < DATE_FORMAT(NOW(), '%Y-%m-01')
            GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
            ORDER BY month
        ");

        $hasAiKey = ! empty(config('services.anthropic.key'));

        return view('admin.reports.revenue-forecast', compact(
            'expiringByPlan', 'forecastByPlan',
            'renewalForecast', 'newSubForecast',
            'baseForecast', 'pessimistic', 'optimistic',
            'historicalRevenue', 'avgNewPerDay', 'conversionRate',
            'hasAiKey'
        ));
    }

    public function revenueForecastAi(Request $request): JsonResponse
    {
        $apiKey = config('services.anthropic.key');
        if (empty($apiKey)) {
            return response()->json(['error' => 'API key tidak dikonfigurasi.'], 400);
        }

        if (Cache::has('revenue_forecast_ai_narrative')) {
            return response()->json([
                'narrative' => Cache::get('revenue_forecast_ai_narrative'),
                'cached_at' => Cache::get('revenue_forecast_ai_cached_at'),
                'from_cache' => true,
            ]);
        }

        $baseForecast = $request->input('base_forecast', 0);
        $pessimistic = $request->input('pessimistic', 0);
        $optimistic = $request->input('optimistic', 0);
        $renewalPct = $request->input('renewal_pct', 0);
        $newSubPct = $request->input('new_sub_pct', 0);
        $conversionRate = $request->input('conversion_rate', 0);

        $prompt = <<<PROMPT
Kamu adalah analis keuangan untuk platform baca novel digital Novelya (Indonesia).

DATA FORECAST REVENUE BULAN DEPAN:
- Prediksi realistis: Rp {$baseForecast}
- Pesimistis (-20%): Rp {$pessimistic}
- Optimistis (+20%): Rp {$optimistic}
- Kontribusi dari renewal: {$renewalPct}%
- Kontribusi dari subscriber baru: {$newSubPct}%
- Conversion rate saat ini: {$conversionRate}%

Berikan analisis singkat dalam Bahasa Indonesia:
1. **Interpretasi Forecast** (1-2 kalimat: apakah angka ini sehat? context-nya apa?)
2. **Faktor Utama** (2 bullet point: apa yang paling mempengaruhi forecast ini)
3. **1 Rekomendasi Spesifik** untuk meningkatkan revenue bulan depan

Format markdown. Singkat dan actionable. Maksimal 200 kata.
PROMPT;

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => 'claude-haiku-4-5-20251001',
                    'max_tokens' => 600,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                ]);

            if (! $response->successful()) {
                return response()->json(['error' => 'API error: '.$response->status()], 500);
            }

            $narrative = $response->json('content.0.text');
            $cachedAt = now()->setTimezone('Asia/Jakarta')->format('d M Y H:i');

            Cache::put('revenue_forecast_ai_narrative', $narrative, now()->addHour());
            Cache::put('revenue_forecast_ai_cached_at', $cachedAt, now()->addHour());

            return response()->json(['narrative' => $narrative, 'cached_at' => $cachedAt, 'from_cache' => false]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menghubungi AI: '.$e->getMessage()], 500);
        }
    }
}
