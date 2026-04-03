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
                'version' => 'v1.6',
                'date' => '2026-04-03',
                'tag' => 'latest',
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
