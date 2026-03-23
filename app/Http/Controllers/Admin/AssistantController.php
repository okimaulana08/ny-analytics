<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\InsightEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AssistantController extends Controller
{
    public function stakeholder()
    {
        $engine   = new InsightEngine();
        $insights = $engine->analyze();
        $metrics  = $engine->getMetrics();
        $health   = $engine->healthScore();

        // Sort insights: urgent → warning → info → positive
        $order = ['urgent' => 0, 'warning' => 1, 'info' => 2, 'positive' => 3];
        usort($insights, fn($a, $b) => ($order[$a['severity']] ?? 9) <=> ($order[$b['severity']] ?? 9));

        // Group by severity for display
        $grouped = [];
        foreach ($insights as $ins) {
            $grouped[$ins['severity']][] = $ins;
        }

        $hasAiKey = !empty(config('services.anthropic.key'));

        // Check cached AI narrative
        $aiNarrative  = Cache::get('stakeholder_ai_narrative');
        $aiCachedAt   = Cache::get('stakeholder_ai_cached_at');

        return view('admin.assistant.stakeholder', compact(
            'insights', 'grouped', 'metrics', 'health', 'hasAiKey', 'aiNarrative', 'aiCachedAt'
        ));
    }

    public function generateAiInsight(Request $request)
    {
        $apiKey = config('services.anthropic.key');
        if (empty($apiKey)) {
            return response()->json(['error' => 'API key tidak dikonfigurasi.'], 400);
        }

        // Return cache if still valid
        if (Cache::has('stakeholder_ai_narrative')) {
            return response()->json([
                'narrative'         => Cache::get('stakeholder_ai_narrative'),
                'cached_at'         => Cache::get('stakeholder_ai_cached_at'),
                'cache_expires_in'  => $this->cacheExpiresIn(),
                'from_cache'        => true,
            ]);
        }

        $engine  = new InsightEngine();
        $m       = $engine->getMetrics();
        $insights = $engine->analyze();

        // Build rule insight summary
        $urgentInsights  = array_filter($insights, fn($i) => $i['severity'] === 'urgent');
        $warningInsights = array_filter($insights, fn($i) => $i['severity'] === 'warning');
        $insightSummary  = '';
        foreach ($urgentInsights as $ins) {
            $insightSummary .= "🔴 [URGENT] {$ins['title']}: {$ins['desc']}\n";
        }
        foreach ($warningInsights as $ins) {
            $insightSummary .= "🟡 [WARNING] {$ins['title']}: {$ins['desc']}\n";
        }
        $insightSummary = $insightSummary ?: 'Tidak ada insight kritis terdeteksi.';

        $revGrowth = $m['prev_month_rev'] > 0
            ? round(($m['revenue_30d'] - $m['prev_month_rev']) / $m['prev_month_rev'] * 100, 1)
            : 0;

        $prompt = <<<PROMPT
Kamu adalah analis bisnis senior untuk platform membaca novel digital bernama Novelya (Indonesia).
Berikan analisis bisnis actionable dalam Bahasa Indonesia berdasarkan data berikut:

METRIK 30 HARI TERAKHIR:
- Total user: {$m['total_users']} | Baru 30h: {$m['new_users_30d']} (prev: {$m['new_users_prev']})
- Conversion rate: {$m['conversion_rate']}% | Target industri: 3-5%
- ARPU: Rp {$m['arpu']} | Revenue 30h: Rp {$m['revenue_30d']}
- Plan terlaris: {$m['top_plan']} ({$m['top_plan_pct']}% transaksi) | Plan Harian: {$m['daily_plan_pct']}%
- Revenue growth vs bulan lalu: {$revGrowth}%
- Churn rate 30h: {$m['churn_rate']}% | Expiring <7h: {$m['expiring_7d']} user
- Pembaca gratis (belum bayar): {$m['free_reader_count']} user
- User dormant (>30h tidak login): {$m['dormant_count']} user
- Avg chapter/view: {$m['avg_chapters_per_view']} | Konten terpopuler: {$m['top_content_share']}% reads
- Kategori terdominan: {$m['top_category_name']} ({$m['top_category_pct']}% reads)

RULE-BASED INSIGHTS YANG SUDAH TERDETEKSI:
{$insightSummary}

Berikan:
1. **Ringkasan Kondisi Bisnis** (2-3 kalimat, jujur dan tajam)
2. **3 Prioritas Aksi Utama** dengan angka spesifik dan timeline (misal: "Dalam 7 hari: Turunkan harga Harian dari Rp 2.490 → Rp 1.990 untuk 2 minggu, target conversion naik ke 4%")
3. **1 Peringatan Risiko** yang paling kritis dan perlu diwaspadai

Format markdown. Langsung ke poin, tidak perlu pembuka basa-basi.
PROMPT;

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key'         => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'      => 'claude-haiku-4-5-20251001',
                    'max_tokens' => 1500,
                    'messages'   => [['role' => 'user', 'content' => $prompt]],
                ]);

            if (!$response->successful()) {
                return response()->json(['error' => 'API error: ' . $response->status()], 500);
            }

            $narrative = $response->json('content.0.text');
            $cachedAt  = now()->setTimezone('Asia/Jakarta')->format('d M Y H:i');

            Cache::put('stakeholder_ai_narrative', $narrative, now()->addHour());
            Cache::put('stakeholder_ai_cached_at', $cachedAt, now()->addHour());

            return response()->json([
                'narrative'        => $narrative,
                'cached_at'        => $cachedAt,
                'cache_expires_in' => '60 menit',
                'from_cache'       => false,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menghubungi AI: ' . $e->getMessage()], 500);
        }
    }

    private function cacheExpiresIn(): string
    {
        $ttl = Cache::getStore()->get(Cache::getPrefix() . 'stakeholder_ai_narrative');
        return '< 60 menit';
    }
}
