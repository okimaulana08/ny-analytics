<?php

namespace Database\Seeders;

use App\Models\AppConfig;
use Illuminate\Database\Seeder;

class SystemConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            // --- Komunikasi ---
            [
                'group' => 'Komunikasi',
                'label' => 'Max Pesan per 7 Hari',
                'key' => 'comms_max_7d',
                'value' => '3',
                'type' => 'integer',
                'description' => 'Batas maksimum total Email+WA yang dikirim ke 1 user dalam 7 hari. User melebihi batas ini akan ditandai merah di Frequency Monitor.',
            ],
            [
                'group' => 'Komunikasi',
                'label' => 'Max Pesan per 30 Hari',
                'key' => 'comms_max_30d',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Batas maksimum total Email+WA yang dikirim ke 1 user dalam 30 hari.',
            ],

            // --- Analytics & Insight ---
            [
                'group' => 'Analytics & Insight',
                'label' => 'Conversion Rate — Urgent (%)',
                'key' => 'insight_conversion_urgent',
                'value' => '2',
                'type' => 'float',
                'description' => 'Alert merah di stakeholder insight jika conversion rate (free→paid) di bawah nilai ini.',
            ],
            [
                'group' => 'Analytics & Insight',
                'label' => 'Conversion Rate — Warning (%)',
                'key' => 'insight_conversion_warning',
                'value' => '3.5',
                'type' => 'float',
                'description' => 'Alert kuning di stakeholder insight jika conversion rate di bawah nilai ini.',
            ],
            [
                'group' => 'Analytics & Insight',
                'label' => 'ARPU Target (Rp)',
                'key' => 'insight_arpu_target',
                'value' => '50000',
                'type' => 'integer',
                'description' => 'Target ARPU (Average Revenue Per User) untuk mencapai health score maksimal pada komponen pricing.',
            ],
            [
                'group' => 'Analytics & Insight',
                'label' => 'Daily Plan Share — Warning (%)',
                'key' => 'insight_daily_plan_warning',
                'value' => '55',
                'type' => 'integer',
                'description' => 'Alert jika proporsi plan harian melebihi nilai ini dari total transaksi. Terlalu banyak plan harian = revenue tidak stabil.',
            ],
            [
                'group' => 'Analytics & Insight',
                'label' => 'Churn Rate — Urgent (%)',
                'key' => 'insight_churn_urgent',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Alert merah jika churn rate (subscriber tidak renew) melebihi nilai ini dalam 30 hari.',
            ],
            [
                'group' => 'Analytics & Insight',
                'label' => 'Churn Rate — Warning (%)',
                'key' => 'insight_churn_warning',
                'value' => '15',
                'type' => 'integer',
                'description' => 'Alert kuning jika churn rate melebihi nilai ini dalam 30 hari.',
            ],
            [
                'group' => 'Analytics & Insight',
                'label' => 'Konsentrasi Konten — Urgent (%)',
                'key' => 'insight_concentration_urgent',
                'value' => '60',
                'type' => 'integer',
                'description' => 'Alert merah jika 1 konten mendominasi lebih dari nilai ini persen dari total baca.',
            ],
            [
                'group' => 'Analytics & Insight',
                'label' => 'Konsentrasi Konten — Warning (%)',
                'key' => 'insight_concentration_warning',
                'value' => '40',
                'type' => 'integer',
                'description' => 'Alert kuning jika 1 konten mendominasi lebih dari nilai ini persen dari total baca.',
            ],
            [
                'group' => 'Analytics & Insight',
                'label' => 'Avg Chapter/View — Warning',
                'key' => 'insight_avg_chapters_warning',
                'value' => '2',
                'type' => 'float',
                'description' => 'Alert jika rata-rata chapter yang dibaca per sesi di bawah nilai ini. Indikator engagement rendah.',
            ],
            [
                'group' => 'Analytics & Insight',
                'label' => 'User Growth — Positive (%)',
                'key' => 'insight_user_growth_positive',
                'value' => '20',
                'type' => 'integer',
                'description' => 'Insight positif ditampilkan jika pertumbuhan user baru month-over-month mencapai atau melebihi nilai ini.',
            ],

            // --- Scheduler ---
            [
                'group' => 'Scheduler',
                'label' => 'Jam Kirim Email Trigger',
                'key' => 'schedule_email_triggers',
                'value' => '07:00',
                'type' => 'string',
                'description' => 'Jam pengiriman trigger email otomatis setiap hari (format HH:MM, timezone WIB). Butuh deploy ulang scheduler untuk berlaku.',
            ],
            [
                'group' => 'Scheduler',
                'label' => 'Jam Kirim Scheduled Reports',
                'key' => 'schedule_reports_send',
                'value' => '08:00',
                'type' => 'string',
                'description' => 'Jam pengiriman laporan email terjadwal setiap hari (format HH:MM, timezone WIB). Butuh deploy ulang scheduler untuk berlaku.',
            ],
            [
                'group' => 'Scheduler',
                'label' => 'Jam Kirim Daily Summary WA',
                'key' => 'schedule_daily_summary',
                'value' => '23:55',
                'type' => 'string',
                'description' => 'Jam pengiriman daily summary WhatsApp setiap hari (format HH:MM, timezone WIB). Butuh deploy ulang scheduler untuk berlaku.',
            ],

            // --- Laporan ---
            [
                'group' => 'Laporan',
                'label' => 'Window Revenue Summary (hari)',
                'key' => 'report_revenue_window',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Periode data (hari ke belakang) untuk laporan Revenue Summary di Scheduled Reports.',
            ],
            [
                'group' => 'Laporan',
                'label' => 'Window Churn Alert (hari)',
                'key' => 'report_churn_window',
                'value' => '7',
                'type' => 'integer',
                'description' => 'Periode data untuk Churn Alert report: berapa hari ke depan untuk expiry, dan ke belakang untuk churn.',
            ],
            [
                'group' => 'Laporan',
                'label' => 'Window Engagement Summary (hari)',
                'key' => 'report_engagement_window',
                'value' => '7',
                'type' => 'integer',
                'description' => 'Periode data untuk Engagement Summary report: reads, views, active readers dalam N hari terakhir.',
            ],
            [
                'group' => 'Laporan',
                'label' => 'Dormant User Window (hari)',
                'key' => 'report_dormant_window',
                'value' => '14',
                'type' => 'integer',
                'description' => 'User dianggap dormant jika tidak membaca apapun selama N hari. Digunakan di Churn Alert report.',
            ],
            [
                'group' => 'Laporan',
                'label' => 'Jumlah Top Konten di Report',
                'key' => 'report_top_content_limit',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Jumlah konten yang ditampilkan di Top Content report dalam Scheduled Reports.',
            ],

            // --- Novel Generator ---
            [
                'group' => 'Novel Generator',
                'label' => 'Model AI — Ringkasan',
                'key' => 'novel.overview_model',
                'value' => 'claude-sonnet-4-6',
                'type' => 'string',
                'description' => 'Claude model yang digunakan untuk generate gambaran umum cerita (Stage 1).',
            ],
            [
                'group' => 'Novel Generator',
                'label' => 'Model AI — Outline',
                'key' => 'novel.outline_model',
                'value' => 'claude-sonnet-4-6',
                'type' => 'string',
                'description' => 'Claude model yang digunakan untuk generate outline semua bab (Stage 2).',
            ],
            [
                'group' => 'Novel Generator',
                'label' => 'Model AI — Konten Bab',
                'key' => 'novel.content_model',
                'value' => 'claude-sonnet-4-6',
                'type' => 'string',
                'description' => 'Claude model yang digunakan untuk generate konten bab penuh (Stage 3).',
            ],
            [
                'group' => 'Novel Generator',
                'label' => 'Max Output Tokens — Konten',
                'key' => 'novel.content_max_tokens',
                'value' => '4096',
                'type' => 'integer',
                'description' => 'Batas token output untuk generate konten bab. Kurangi jika konten terlalu panjang.',
            ],
            [
                'group' => 'Novel Generator',
                'label' => 'Target Jumlah Kata per Bab',
                'key' => 'novel.target_word_count',
                'value' => '2000',
                'type' => 'integer',
                'description' => 'Target jumlah kata yang diinstruksikan ke AI saat generate konten bab.',
            ],
        ];

        foreach ($configs as $config) {
            AppConfig::updateOrCreate(['key' => $config['key']], $config);
        }
    }
}
