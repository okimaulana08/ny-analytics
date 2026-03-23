<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InsightEngine
{
    private array $insights = [];
    private array $metrics  = [];

    public function analyze(): array
    {
        $this->loadMetrics();
        $this->checkConversionRate();
        $this->checkPlanMix();
        $this->checkRevenueGrowth();
        $this->checkContentConcentration();
        $this->checkEngagementDepth();
        $this->checkCategoryDiversity();
        $this->checkExpiringSoon();
        $this->checkChurnRate();
        $this->checkDormantUsers();
        $this->checkUserGrowth();
        $this->checkFreeReaderOpportunity();
        return $this->insights;
    }

    public function getMetrics(): array
    {
        if (empty($this->metrics)) {
            $this->loadMetrics();
        }
        return $this->metrics;
    }

    public function healthScore(): array
    {
        if (empty($this->metrics)) {
            $this->loadMetrics();
        }
        $m = $this->metrics;

        // Pricing score (0-100): conversion rate, ARPU, revenue growth
        $pricingScore = 0;
        $pricingScore += min(40, ($m['conversion_rate'] / 5) * 40); // 5% = full 40
        $pricingScore += min(30, ($m['arpu'] / 50000) * 30);        // 50k ARPU = full 30
        $revGrowth = $m['prev_month_rev'] > 0 ? ($m['revenue_30d'] - $m['prev_month_rev']) / $m['prev_month_rev'] * 100 : 0;
        $pricingScore += $revGrowth > 0 ? min(30, $revGrowth * 1.5) : max(-20, $revGrowth);

        // Content score (0-100): diversity, engagement depth
        $contentScore = 0;
        $contentScore += $m['top_content_share'] < 40 ? 50 : max(0, 50 - ($m['top_content_share'] - 40) * 2);
        $contentScore += min(50, ($m['avg_chapters_per_view'] / 5) * 50); // 5 ch/view = full 50

        // Retention score (0-100): churn rate, expiring
        $retentionScore = 100;
        $retentionScore -= min(60, $m['churn_rate'] * 3);
        $retentionScore -= min(20, $m['expiring_7d'] * 2);
        $retentionScore -= min(20, ($m['dormant_count'] / max(1, $m['total_users'])) * 100);

        // Growth score (0-100): new users growth, free reader potential
        $growthScore = 50;
        $userGrowthPct = $m['new_users_prev'] > 0 ? ($m['new_users_30d'] - $m['new_users_prev']) / $m['new_users_prev'] * 100 : 0;
        $growthScore += $userGrowthPct > 0 ? min(30, $userGrowthPct) : max(-30, $userGrowthPct);
        $growthScore += min(20, ($m['free_reader_count'] / max(1, $m['total_users'])) * 20 * 2);

        $scores = [
            'pricing'   => (int) max(0, min(100, $pricingScore)),
            'content'   => (int) max(0, min(100, $contentScore)),
            'retention' => (int) max(0, min(100, $retentionScore)),
            'growth'    => (int) max(0, min(100, $growthScore)),
        ];
        $overall = (int) round(array_sum($scores) / 4);

        return ['overall' => $overall, 'breakdown' => $scores];
    }

    // ─── Data Loading ───────────────────────────────────────────────────

    private function loadMetrics(): void
    {
        $db = DB::connection('novel');

        // Basic user counts
        $totalUsers = $db->table('users')->count();

        $paidUsers = $db->table('transactions')
            ->where('status', 'paid')
            ->distinct('user_id')
            ->count('user_id');

        // Conversion rate
        $conversionRate = $totalUsers > 0 ? round($paidUsers / $totalUsers * 100, 2) : 0;

        // ARPU & revenue
        $totalRevenue = $db->table('transactions')
            ->where('status', 'paid')
            ->sum('total_amount') ?? 0;

        $arpu = $paidUsers > 0 ? round($totalRevenue / $paidUsers) : 0;

        // Revenue 30d vs prev 30d
        $rev30d = $db->table('transactions')
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subDays(30))
            ->sum('total_amount') ?? 0;

        $revPrev = $db->table('transactions')
            ->where('status', 'paid')
            ->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
            ->sum('total_amount') ?? 0;

        // Plan mix (top plan)
        $planMix = $db->table('transactions as t')
            ->join('membership_plans as mp', 'mp.id', '=', 't.plan_id')
            ->where('t.status', 'paid')
            ->selectRaw('mp.name, COUNT(*) as cnt')
            ->groupBy('mp.id', 'mp.name')
            ->orderByDesc('cnt')
            ->get();

        $totalTrx    = $planMix->sum('cnt') ?: 1;
        $topPlan     = $planMix->first();
        $topPlanName = $topPlan ? $topPlan->name : '—';
        $topPlanPct  = $topPlan ? round($topPlan->cnt / $totalTrx * 100, 1) : 0;
        $dailyPlanPct = $planMix->where('name', 'like', '%Harian%')->sum('cnt') / $totalTrx * 100;

        // Content concentration
        $totalReadsRow = $db->table('recap_content')->selectRaw('SUM(`read`) as total_reads')->first();
        $totalReads    = ($totalReadsRow->total_reads ?? 0) ?: 1;
        $topContent    = $db->table('recap_content as rc')
            ->join('content as c', 'c.id', '=', 'rc.content_id')
            ->selectRaw('rc.content_id, c.title, SUM(rc.`read`) as content_reads')
            ->groupBy('rc.content_id', 'c.title')
            ->orderByRaw('content_reads DESC')
            ->first();
        $topContentShare = $topContent ? round($topContent->content_reads / $totalReads * 100, 1) : 0;
        $topContentTitle = $topContent ? $topContent->title : '—';

        // Avg chapters per view (from recap_content 30d)
        $engRow = $db->table('recap_content')
            ->where('date', '>=', now()->subDays(30)->toDateString())
            ->selectRaw('SUM(`read`) as total_reads, SUM(`view`) as total_views')
            ->first();
        $avgChaptersPerView = ($engRow && $engRow->total_views > 0)
            ? round($engRow->total_reads / $engRow->total_views, 2)
            : 0;

        // Category diversity (top category share)
        $catData = $db->table('recap_content as rc')
            ->join('content as c', 'c.id', '=', 'rc.content_id')
            ->join('master_content_category as cat', 'cat.id', '=', 'c.category_id')
            ->selectRaw('cat.name as category, SUM(rc.`read`) as cat_reads')
            ->groupBy('cat.id', 'cat.name')
            ->orderByRaw('cat_reads DESC')
            ->get();
        $totalCatReads  = $catData->sum('cat_reads') ?: 1;
        $topCatRow      = $catData->first();
        $topCategoryPct = $topCatRow ? round($topCatRow->cat_reads / $totalCatReads * 100, 1) : 0;
        $topCategoryName = $topCatRow ? $topCatRow->category : '—';

        // Expiring memberships
        $expiring7d = $db->table('user_memberships')
            ->where('is_active', 1)
            ->whereBetween('expired_at', [now(), now()->addDays(7)])
            ->count();

        $expiring3d = $db->table('user_memberships')
            ->where('is_active', 1)
            ->whereBetween('expired_at', [now(), now()->addDays(3)])
            ->count();

        // Churn rate (expired in last 30d / active users 30d ago)
        $expiredLast30d = $db->table('user_memberships')
            ->where('is_active', 0)
            ->where('expired_at', '>=', now()->subDays(30))
            ->where('expired_at', '<=', now())
            ->count();

        $activeUsersBase = $db->table('user_memberships')
            ->where('is_active', 1)
            ->count();
        $activeUsersBase = max(1, $activeUsersBase + $expiredLast30d);
        $churnRate = round($expiredLast30d / $activeUsersBase * 100, 1);

        // Dormant users
        $dormantCount = $db->table('users')
            ->where('last_login_at', '<', now()->subDays(30))
            ->count();

        // User growth
        $newUsers30d = $db->table('users')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        $newUsersPrev = $db->table('users')
            ->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
            ->count();

        // Free readers (read but never paid)
        $freeReaderCount = $db->table('users as u')
            ->join('user_read as ur', 'ur.user_id', '=', 'u.id')
            ->leftJoin('transactions as t', function ($j) {
                $j->on('t.user_id', '=', 'u.id')->where('t.status', 'paid');
            })
            ->whereNull('t.id')
            ->distinct('u.id')
            ->count('u.id');

        $this->metrics = [
            'total_users'          => $totalUsers,
            'paid_users'           => $paidUsers,
            'conversion_rate'      => $conversionRate,
            'total_revenue'        => $totalRevenue,
            'arpu'                 => $arpu,
            'revenue_30d'          => $rev30d,
            'prev_month_rev'       => $revPrev,
            'top_plan'             => $topPlanName,
            'top_plan_pct'         => $topPlanPct,
            'daily_plan_pct'       => round($dailyPlanPct, 1),
            'top_content_share'    => $topContentShare,
            'top_content_title'    => $topContentTitle,
            'avg_chapters_per_view'=> $avgChaptersPerView,
            'top_category_pct'     => $topCategoryPct,
            'top_category_name'    => $topCategoryName,
            'expiring_7d'          => $expiring7d,
            'expiring_3d'          => $expiring3d,
            'churn_rate'           => $churnRate,
            'dormant_count'        => $dormantCount,
            'new_users_30d'        => $newUsers30d,
            'new_users_prev'       => $newUsersPrev,
            'free_reader_count'    => $freeReaderCount,
        ];
    }

    // ─── Rules ──────────────────────────────────────────────────────────

    private function checkConversionRate(): void
    {
        $rate = $this->metrics['conversion_rate'];
        if ($rate < 2) {
            $this->add('pricing', 'urgent',
                'Conversion Rate Sangat Rendah',
                "Hanya {$rate}% pengunjung yang berlangganan. Target industri platform membaca digital: 3–5%.",
                'Pertimbangkan free trial 3 hari atau harga plan Harian lebih terjangkau untuk menurunkan barrier.',
                ['rate' => $rate, 'target' => '3-5%']
            );
        } elseif ($rate < 3.5) {
            $this->add('pricing', 'warning',
                'Conversion Rate Di Bawah Target',
                "Conversion rate saat ini {$rate}% (target >3.5%). Ada ruang peningkatan signifikan.",
                'Coba A/B test harga Harian atau tambah benefit visible untuk mendorong konversi.',
                ['rate' => $rate, 'target' => '3.5%']
            );
        } else {
            $this->add('pricing', 'positive',
                'Conversion Rate Sehat',
                "Conversion rate {$rate}% berada di atas rata-rata industri platform membaca.",
                'Pertahankan strategi onboarding saat ini, fokus pada peningkatan ARPU.',
                ['rate' => $rate]
            );
        }
    }

    private function checkPlanMix(): void
    {
        $dailyPct = $this->metrics['daily_plan_pct'];
        $arpu     = $this->metrics['arpu'];
        if ($dailyPct > 55) {
            $this->add('pricing', 'warning',
                'Dominasi Plan Harian — ARPU Rendah',
                "{$dailyPct}% transaksi menggunakan plan Harian. ARPU saat ini Rp " . number_format($arpu, 0, ',', '.') . ".",
                'Tambahkan insentif upgrade ke plan Mingguan/Bulanan: diskon pertama, atau akses konten eksklusif. Target: kurangi share Harian ke <40%.',
                ['daily_pct' => $dailyPct, 'arpu' => $arpu]
            );
        } elseif ($dailyPct < 30) {
            $this->add('pricing', 'positive',
                'Mix Plan Sehat — ARPU Tinggi',
                "Plan Harian hanya {$dailyPct}% dari transaksi. User lebih memilih plan jangka panjang — ARPU lebih tinggi.",
                'Pertahankan insentif plan Bulanan/Tahunan. Pertimbangkan annual plan bundling.',
                ['daily_pct' => $dailyPct]
            );
        }
    }

    private function checkRevenueGrowth(): void
    {
        $curr = $this->metrics['revenue_30d'];
        $prev = $this->metrics['prev_month_rev'];
        if ($prev <= 0) return;
        $growth = round(($curr - $prev) / $prev * 100, 1);
        if ($growth >= 15) {
            $this->add('pricing', 'positive',
                "Revenue Naik {$growth}% vs Bulan Lalu",
                'Rp ' . number_format($curr, 0, ',', '.') . " vs Rp " . number_format($prev, 0, ',', '.') . " bulan sebelumnya.",
                'Momentum positif. Pertimbangkan reinvestasi ke akuisisi user baru.',
                ['current' => $curr, 'prev' => $prev, 'growth_pct' => $growth]
            );
        } elseif ($growth < -10) {
            $this->add('pricing', 'urgent',
                "Revenue Turun {$growth}% vs Bulan Lalu",
                'Penurunan signifikan: Rp ' . number_format($curr, 0, ',', '.') . " vs Rp " . number_format($prev, 0, ',', '.') . ".",
                'Investigasi: apakah ada masalah pembayaran, churn tinggi, atau penurunan konten baru? Aktifkan win-back campaign.',
                ['current' => $curr, 'prev' => $prev, 'growth_pct' => $growth]
            );
        } elseif ($growth < 0) {
            $this->add('pricing', 'warning',
                "Revenue Sedikit Turun ({$growth}%)",
                'Rp ' . number_format($curr, 0, ',', '.') . " vs Rp " . number_format($prev, 0, ',', '.') . " bulan sebelumnya.",
                'Monitor tren selama 2 minggu ke depan. Pertimbangkan promo renewal untuk menstabilkan revenue.',
                ['current' => $curr, 'prev' => $prev, 'growth_pct' => $growth]
            );
        }
    }

    private function checkContentConcentration(): void
    {
        $share = $this->metrics['top_content_share'];
        $title = $this->metrics['top_content_title'];
        if ($share > 60) {
            $this->add('content', 'urgent',
                'Konsentrasi Konten Berbahaya',
                "\"{$title}\" menyumbang {$share}% total reads — risiko single point of failure jika konten ini berhenti update.",
                'Segera prioritaskan promosi 3–5 konten lain. Buat featured list atau reading path untuk diversifikasi.',
                ['title' => $title, 'share' => $share]
            );
        } elseif ($share > 40) {
            $this->add('content', 'warning',
                'Satu Konten Dominasi Reads',
                "\"{$title}\" menyumbang {$share}% total reads.",
                'Aktifkan fitur rekomendasi konten serupa. Tawarkan diskon chapter awal untuk konten lain di kategori sama.',
                ['title' => $title, 'share' => $share]
            );
        } else {
            $this->add('content', 'positive',
                'Distribusi Reads Sehat',
                "Konten terpopuler hanya {$share}% dari total reads — diversifikasi baik.",
                'Pertahankan strategi publikasi multi-konten yang seimbang.',
                ['share' => $share]
            );
        }
    }

    private function checkEngagementDepth(): void
    {
        $avg = $this->metrics['avg_chapters_per_view'];
        if ($avg < 2) {
            $this->add('content', 'warning',
                'Kedalaman Baca Rendah',
                "Rata-rata hanya {$avg} chapter per view dalam 30 hari. User tidak terhook setelah chapter pertama.",
                'Audit chapter 1–3 semua konten baru. Pertimbangkan batas baca gratis setelah chapter 3 (bukan 1) agar hook lebih kuat.',
                ['avg' => $avg, 'target' => 3]
            );
        } elseif ($avg >= 4) {
            $this->add('content', 'positive',
                'Engagement Mendalam',
                "Rata-rata {$avg} chapter per view — user membaca lebih dari 4 chapter per sesi.",
                'Kedalaman baca sangat baik. Manfaatkan ini sebagai selling point ke advertiser atau sponsor konten.',
                ['avg' => $avg]
            );
        }
    }

    private function checkCategoryDiversity(): void
    {
        $pct  = $this->metrics['top_category_pct'];
        $cat  = $this->metrics['top_category_name'];
        if ($pct > 80) {
            $this->add('content', 'warning',
                "Kategori {$cat} Terlalu Dominan",
                "{$pct}% reads berasal dari kategori {$cat}. Risiko churn jika pengguna suka genre lain.",
                'Tambahkan minimal 2–3 konten dari kategori berbeda per bulan. Pertimbangkan survey genre preferensi user.',
                ['category' => $cat, 'pct' => $pct]
            );
        } elseif ($pct < 50) {
            $this->add('content', 'positive',
                'Diversitas Kategori Baik',
                "Tidak ada satu kategori yang mendominasi (tertinggi: {$cat} {$pct}%). Portofolio konten seimbang.",
                'Pertahankan keseimbangan konten lintas genre.',
                ['top_category' => $cat, 'pct' => $pct]
            );
        }
    }

    private function checkExpiringSoon(): void
    {
        $exp3d = $this->metrics['expiring_3d'];
        $exp7d = $this->metrics['expiring_7d'];
        if ($exp3d > 0) {
            $this->add('retention', 'urgent',
                "{$exp3d} Member Expire dalam 3 Hari",
                "{$exp3d} user membership-nya berakhir dalam 72 jam ke depan — risiko churn langsung.",
                'Kirim WA reminder sekarang dengan penawaran renewal: "Perpanjang sekarang, hemat 10%". Gunakan fitur WA di menu Segmen.',
                ['count' => $exp3d]
            );
        } elseif ($exp7d > 0) {
            $this->add('retention', 'warning',
                "{$exp7d} Member Expire dalam 7 Hari",
                "{$exp7d} user membership-nya berakhir dalam seminggu ke depan.",
                'Siapkan campaign reminder: kirim WA hari ini dan follow-up H-1. Tawarkan bundle atau diskon renewal.',
                ['count' => $exp7d]
            );
        }
    }

    private function checkChurnRate(): void
    {
        $rate = $this->metrics['churn_rate'];
        if ($rate > 30) {
            $this->add('retention', 'urgent',
                "Churn Rate Tinggi: {$rate}%",
                "{$rate}% member tidak renewal dalam 30 hari terakhir.",
                'Aktifkan win-back campaign segera. Audit apakah ada masalah pembayaran atau kualitas konten yang menurun.',
                ['rate' => $rate]
            );
        } elseif ($rate > 15) {
            $this->add('retention', 'warning',
                "Churn Rate Perlu Perhatian: {$rate}%",
                "{$rate}% member tidak renewal bulan ini.",
                'Coba exit survey singkat saat membership hampir habis. Tawarkan pause membership sebagai alternatif cancel.',
                ['rate' => $rate]
            );
        } else {
            $this->add('retention', 'positive',
                "Retention Kuat — Churn {$rate}%",
                "Hanya {$rate}% churn dalam 30 hari — angka yang sangat baik untuk platform konten digital.",
                'Pertahankan kualitas dan frekuensi update konten.',
                ['rate' => $rate]
            );
        }
    }

    private function checkDormantUsers(): void
    {
        $count      = $this->metrics['dormant_count'];
        $totalUsers = $this->metrics['total_users'];
        $pct        = $totalUsers > 0 ? round($count / $totalUsers * 100, 1) : 0;
        if ($pct > 30) {
            $this->add('retention', 'warning',
                "{$count} User Dormant (>30 hari tidak login)",
                "{$pct}% dari total user belum login lebih dari 30 hari.",
                'Kirim push notification atau WA re-engagement: "Ada konten baru yang menunggumu!" dengan link langsung ke chapter terbaru.',
                ['count' => $count, 'pct' => $pct]
            );
        }
    }

    private function checkUserGrowth(): void
    {
        $curr = $this->metrics['new_users_30d'];
        $prev = $this->metrics['new_users_prev'];
        if ($prev <= 0) return;
        $growth = round(($curr - $prev) / $prev * 100, 1);
        if ($growth >= 20) {
            $this->add('growth', 'positive',
                "User Baru Naik {$growth}% vs Bulan Lalu",
                "{$curr} user baru dalam 30 hari vs {$prev} bulan sebelumnya.",
                'Growth organik kuat. Identifikasi channel mana yang paling efektif dan tingkatkan budget/effort di sana.',
                ['current' => $curr, 'prev' => $prev, 'growth_pct' => $growth]
            );
        } elseif ($growth < -10) {
            $this->add('growth', 'warning',
                "User Baru Menurun {$growth}%",
                "Hanya {$curr} user baru vs {$prev} bulan sebelumnya.",
                'Review kampanye akuisisi. Pertimbangkan program referral: user lama dapat reward jika ajak teman subscribe.',
                ['current' => $curr, 'prev' => $prev, 'growth_pct' => $growth]
            );
        }
    }

    private function checkFreeReaderOpportunity(): void
    {
        $count = $this->metrics['free_reader_count'];
        if ($count >= 10) {
            $this->add('growth', 'info',
                "{$count} Pembaca Gratis — Segmen Potensial",
                "{$count} user sudah aktif membaca tapi belum pernah berlangganan. Ini warm audience yang siap dikonversi.",
                'Buat campaign khusus: "Lanjutkan baca tanpa batas — coba 1 hari gratis". Gunakan data chapter terakhir yang dibaca sebagai personalisasi pesan.',
                ['count' => $count]
            );
        }
    }

    // ─── Helper ─────────────────────────────────────────────────────────

    private function add(string $category, string $severity, string $title, string $desc, string $suggestion, array $evidence = []): void
    {
        $this->insights[] = compact('category', 'severity', 'title', 'desc', 'suggestion', 'evidence');
    }
}
