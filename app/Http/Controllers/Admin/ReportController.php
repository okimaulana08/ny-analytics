<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyRevenueCost;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $authorWhere = '';
        $authorParam = [];
        if ($author !== '') {
            $authorWhere = 'AND u.name LIKE ?';
            $authorParam = ['%'.$author.'%'];
        }

        $countSql = "
            SELECT COUNT(*) AS cnt
            FROM content c
            LEFT JOIN users u ON u.id = c.user_id
            WHERE c.is_published=1 AND c.is_deleted=0 {$authorWhere}
        ";
        $total = $db->selectOne($countSql, $authorParam)->cnt;
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
            WHERE c.is_published=1 AND c.is_deleted=0 {$authorWhere}
            ORDER BY {$orderSql}
            LIMIT {$perPage} OFFSET {$offset}
        ", $authorParam);

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
            'topBySubscribe', 'author'
        ));
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

        $countSql = "
            SELECT COUNT(DISTINCT u.id) AS cnt
            FROM users u
            {$joinFilter}
            LEFT JOIN profile p ON p.user_id = u.id
            WHERE 1=1 {$whereFilter}
        ";
        $total = $db->selectOne($countSql)->cnt;
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
            WHERE 1=1 {$whereFilter}
            GROUP BY u.id, u.name, u.email, p.phone_number, u.created_at, u.last_login_at, um.is_active
            ORDER BY u.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");

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
            'users', 'page', 'perPage', 'total', 'totalPages', 'filter'
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
}
