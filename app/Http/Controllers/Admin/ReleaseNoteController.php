<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ReleaseNoteController extends Controller
{
    public function index(): View
    {
        $releases = $this->releases();

        return view('admin.release-notes', compact('releases'));
    }

    /** @return array<int, array<string, mixed>> */
    private function releases(): array
    {
        return [
            [
                'version' => 'v2.0',
                'date' => '2026-04-04',
                'tag' => 'latest',
                'title' => 'Enhanced Trigger Conditions',
                'features' => [
                    [
                        'name' => 'WA Trigger — 4 Kondisi',
                        'route' => 'admin.crm.wa-triggers.index',
                        'description' => 'Pending Payment kini punya 2 kondisi: Invoice Aktif (ada link bayar) dan Invoice Expired (kirim link pilih paket). Expiry Reminder punya 2 kondisi: Sebelum Berakhir dan Setelah Berakhir (untuk user yang sudah expired tapi belum renew). Masing-masing punya 10 default template yang diseed otomatis saat trigger dibuat.',
                        'suggestions' => [
                            'Tambah kondisi untuk pending payment yang sudah > 24 jam',
                            'Notifikasi Slack/email ke admin jika tidak ada penerima dalam 3 hari berturut-turut',
                        ],
                    ],
                    [
                        'name' => 'Email Trigger — Pending Payment + After Expiry',
                        'route' => 'admin.crm.triggers.index',
                        'description' => 'Email trigger kini mendukung tipe Pending Payment (dengan kondisi Invoice Aktif / Invoice Expired) dan kondisi After Expiry untuk Reminder Expiry. Legacy triggers (tanpa condition) tetap berjalan normal via fallback routing.',
                        'suggestions' => [
                            'A/B test subject line per kondisi',
                            'Unsubscribe link per trigger type',
                        ],
                    ],
                ],
            ],
            [
                'version' => 'v1.9',
                'date' => '2026-04-04',
                'tag' => null,
                'title' => 'System Config',
                'features' => [
                    [
                        'name' => 'System Config',
                        'route' => 'admin.system-config',
                        'description' => 'Halaman konfigurasi parameter bisnis non-sensitif yang bisa diedit inline langsung dari UI. Dikelompokkan per kategori: Komunikasi, Analytics & Insight, Scheduler, dan Laporan. Threshold Frequency Monitor kini membaca nilai dari sini.',
                        'suggestions' => [
                            'Tambah riwayat perubahan config (siapa, kapan, nilai lama vs baru)',
                            'Wire-up InsightEngine thresholds ke config ini',
                            'Wire-up ScheduledReportBuilder window configs',
                            'Wire-up Scheduler times ke config ini (butuh dynamic cron)',
                            'Support tipe boolean dengan toggle switch di UI',
                        ],
                    ],
                ],
            ],
            [
                'version' => 'v1.8',
                'date' => '2026-04-04',
                'tag' => null,
                'title' => 'Communication Log & Frequency Monitor',
                'features' => [
                    [
                        'name' => 'Communication Log',
                        'route' => 'admin.communication-logs',
                        'description' => 'Timeline terpadu seluruh pengiriman Email (Trigger + Broadcast) dan WA (Trigger + Notifikasi) ke user. Filter by channel, tipe, tanggal, dan search. Nama user di-enrich dari novel DB.',
                        'suggestions' => [
                            'Export CSV log per periode untuk audit eksternal',
                            'Tambah kolom open/click rate untuk Email Broadcast',
                            'Filter per trigger/campaign spesifik',
                            'Notifikasi real-time jika ada spike pengiriman dalam 1 jam',
                        ],
                    ],
                    [
                        'name' => 'Frequency Monitor',
                        'route' => 'admin.communication-logs.frequency',
                        'description' => 'Per-user frequency report: berapa kali tiap user dihubungi via Email dan WA dalam 7 dan 30 hari terakhir. Row merah = over threshold, kuning = mendekati. Threshold dikonfigurasi via MAX_COMMS_7D dan MAX_COMMS_30D di .env.',
                        'suggestions' => [
                            'Auto-suppress trigger untuk user yang sudah over threshold',
                            'Grafik tren frekuensi per user (sparkline 30 hari)',
                            'Alert email ke admin harian jika ada user baru masuk over limit',
                            'Batas threshold bisa diatur per-user untuk VIP subscriber',
                        ],
                    ],
                ],
            ],
            [
                'version' => 'v1.7',
                'tag' => null,
                'date' => '2026-04-04',
                'tag' => 'latest',
                'title' => 'WhatsApp Trigger Otomatis (WAHA)',
                'features' => [
                    [
                        'name' => 'Trigger WA — Pending Payment',
                        'route' => 'admin.crm.wa-triggers.index',
                        'description' => 'Kirim WA otomatis ke user yang transaksinya masih pending setelah X menit/jam. Rotasi 10+ template pesan yang casual & human agar tidak terdeteksi spam. Cooldown per-user mencegah pesan berulang.',
                        'suggestions' => [
                            'Tambah tombol "Test Kirim" untuk simulasi pesan ke nomor tertentu sebelum aktifkan trigger',
                            'Statistik per trigger: berapa yang convert setelah menerima WA reminder',
                            'Support media (gambar/sticker) agar pesan lebih engaging',
                            'Tambah trigger type: Welcome Payment — selamat datang setelah pertama kali bayar',
                        ],
                    ],
                    [
                        'name' => 'Trigger WA — Expiry Reminder',
                        'route' => 'admin.crm.wa-triggers.index',
                        'description' => 'Kirim WA otomatis X hari sebelum langganan habis. Template acak dari pool 10 variasi untuk menghindari blokir WA. Dijalankan otomatis setiap 5 menit via scheduler.',
                        'suggestions' => [
                            'Cascade trigger: D-7, D-3, D-1 dalam satu flow tanpa setup 3 trigger terpisah',
                            'Include deep-link ke halaman perpanjang di dalam pesan',
                            'Log open/click rate jika menggunakan WA Business API resmi',
                            'Notifikasi ke admin jika WAHA session disconnect (agar trigger tidak silent fail)',
                        ],
                    ],
                ],
            ],
            [
                'version' => 'v1.6',
                'date' => '2026-04-03',
                'tag' => null,
                'title' => 'Log Admin & Release Notes',
                'features' => [
                    [
                        'name' => 'Log Admin',
                        'route' => 'admin.activity-logs',
                        'description' => 'Audit trail seluruh aksi admin yang mengubah data — Create, Update, Delete, Send, Toggle, Generate. Login dicatat lengkap dengan IP address, browser, dan OS. Filter by user, fitur, URL, aksi, dan rentang tanggal. Payload perubahan dapat dilihat via modal JSON.',
                        'suggestions' => [
                            'Export log ke CSV untuk keperluan audit eksternal',
                            'Tambah notifikasi ke admin jika ada aksi Delete yang tidak biasa (misal Delete >10 record sekaligus)',
                            'Retention policy: arsip atau hapus log lebih dari 90 hari secara otomatis',
                            'Tambah log untuk aksi Export/Download PDF agar terpantau siapa men-download apa',
                        ],
                    ],
                    [
                        'name' => 'Release Notes',
                        'route' => 'admin.release-notes',
                        'description' => 'Halaman riwayat pembaruan fitur Novelya Analytics. Setiap fitur ditampilkan sebagai card dengan deskripsi singkat, tombol buka langsung ke fitur, dan saran pengembangan via modal.',
                        'suggestions' => [
                            'Tandai fitur yang sedang "In Development" atau "Planned" dengan badge berbeda',
                            'Tambah link ke GitHub commit atau PR untuk transparansi perubahan teknis',
                            'Subscribe notifikasi email ke admin saat ada release baru',
                        ],
                    ],
                ],
            ],
            [
                'version' => 'v1.5',
                'date' => '2026-04-03',
                'tag' => null,
                'title' => 'Author Analytics, User Journey, Revenue Forecast & Scheduled Reports',
                'features' => [
                    [
                        'name' => 'Author Analytics',
                        'route' => 'admin.reports.authors',
                        'description' => 'Tabel performa per penulis — total baca, view, unique readers, avg rating, dan completion rate. Drill-down per penulis dengan daftar konten dan paying readers proxy.',
                        'suggestions' => [
                            'Tambah filter by genre/kategori untuk melihat dominasi penulis per genre',
                            'Grafik tren reads per bulan per penulis (sparkline)',
                            'Bandingkan performa 2 penulis secara side-by-side',
                            'Export CSV daftar author analytics',
                        ],
                    ],
                    [
                        'name' => 'User Journey Funnel',
                        'route' => 'admin.reports.user-journey',
                        'description' => 'Funnel 4 langkah: Registrasi → Baca Pertama → Bayar Pertama → Renewal. Dilengkapi time-to-convert stats dan cohort table 6 bulan.',
                        'suggestions' => [
                            'Filter funnel berdasarkan periode registrasi (date range)',
                            'Breakdown funnel per channel akuisisi (organic, referral, paid)',
                            'Alert otomatis jika conversion rate drop >5% minggu-ke-minggu',
                            'Cohort heatmap warna (mirip Google Analytics) untuk mempermudah baca tabel',
                        ],
                    ],
                    [
                        'name' => 'Revenue Forecast',
                        'route' => 'admin.reports.revenue-forecast',
                        'description' => 'Proyeksi revenue 30 hari ke depan berdasarkan renewal rate historis dan velocity subscriber baru. Range pesimistis/realistis/optimistis + AI narrative dari Claude.',
                        'suggestions' => [
                            'Simpan forecast harian ke DB untuk melihat akurasi forecast vs aktual',
                            'Tambah variabel skenario manual (misal: jika ada promo diskon 20%)',
                            'Notifikasi email otomatis jika forecast bulan ini < bulan lalu',
                            'Breakdown forecast per segmen user (new vs returning)',
                        ],
                    ],
                    [
                        'name' => 'Scheduled Email Reports',
                        'route' => 'admin.crm.scheduled-reports.index',
                        'description' => 'CRUD untuk laporan email otomatis (mingguan/bulanan). 4 tipe: Revenue Summary, Top Content, Churn Alert, Engagement Summary. Dikirim via Brevo jam 08:00 WIB.',
                        'suggestions' => [
                            'Tambah preview HTML sebelum simpan (mirip broadcast email)',
                            'Tipe report baru: "New Member Summary" dengan breakdown per plan',
                            'Log pengiriman per report untuk audit trail',
                            'Timezone per-recipient untuk tim internasional',
                        ],
                    ],
                    [
                        'name' => 'Email Triggers Otomatis',
                        'route' => 'admin.crm.triggers.index',
                        'description' => 'Trigger berbasis event: Expiry Reminder, Re-engagement (subscriber aktif yang tidak baca N hari), Welcome Payment. Cooldown per-user untuk mencegah spam.',
                        'suggestions' => [
                            'Trigger baru: "Chapter Milestone" — kirim email saat user selesai baca chapter 1 (onboarding)',
                            'A/B testing subject line antar trigger yang sama',
                            'Dashboard statistik trigger: open rate, click rate per trigger',
                            'Dry-run mode: preview siapa yang akan menerima tanpa kirim',
                        ],
                    ],
                    [
                        'name' => 'Chapter Drop-off',
                        'route' => 'admin.reports.chapter-dropoff',
                        'description' => 'Halaman dedicated untuk visualisasi di chapter mana pembaca berhenti. Chart bar + line retention%, tabel detail per chapter dengan color-coded drop-off.',
                        'suggestions' => [
                            'Overlay: bandingkan drop-off 2 konten dalam 1 chart',
                            'Annotasi manual pada chapter (misal: "major plot twist di sini")',
                            'Alert otomatis ke penulis jika drop-off >40% di chapter tertentu',
                        ],
                    ],
                ],
            ],
            [
                'version' => 'v1.4',
                'date' => '2026-04-02',
                'tag' => null,
                'title' => 'Content Report PDF, Title Search & Email Template Built-in Types',
                'features' => [
                    [
                        'name' => 'Content Report PDF Export',
                        'route' => null,
                        'description' => 'Download laporan performa konten lengkap dengan semua chapter sebagai PDF.',
                        'suggestions' => ['Jadwalkan PDF report dikirim otomatis ke stakeholder via Scheduled Reports'],
                    ],
                    [
                        'name' => 'Title Search di Content Report',
                        'route' => 'admin.reports.content',
                        'description' => 'Filter konten berdasarkan judul langsung di tabel laporan.',
                        'suggestions' => ['Gabungkan dengan filter kategori dan author'],
                    ],
                    [
                        'name' => 'Built-in Email Template Types',
                        'route' => 'admin.crm.templates.index',
                        'description' => 'Template siap pakai: story recommendation, payment reminder, promo.',
                        'suggestions' => ['Tambah template "Re-engagement" dan "Onboarding Series" (drip)'],
                    ],
                ],
            ],
            [
                'version' => 'v1.3',
                'date' => '2026-04-01',
                'tag' => null,
                'title' => 'User Recommendation Email & Aktivitas User Search',
                'features' => [
                    [
                        'name' => 'Rekomendasi Konten per User',
                        'route' => null,
                        'description' => 'Halaman rekomendasi per user dengan preview email dan kirim individual langsung dari dashboard.',
                        'suggestions' => ['Otomatisasi via trigger "after first read" untuk onboarding'],
                    ],
                    [
                        'name' => 'Search di Aktivitas User',
                        'route' => 'admin.reports.user-activity',
                        'description' => 'Cari user berdasarkan nama atau email di tabel aktivitas.',
                        'suggestions' => ['Export hasil pencarian ke CSV'],
                    ],
                ],
            ],
        ];
    }
}
