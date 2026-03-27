<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = $this->templates();

        foreach ($templates as $tmpl) {
            EmailTemplate::updateOrCreate(
                ['name' => $tmpl['name']],
                array_merge($tmpl, ['is_active' => true])
            );
        }
    }

    private function baseStyle(): string
    {
        return '
        body { margin:0; padding:0; background:#f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .wrapper { max-width:600px; margin:0 auto; background:#ffffff; }
        .header { background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); padding:40px 40px 30px; text-align:center; }
        .header img { width:40px; margin-bottom:12px; }
        .header h1 { color:#ffffff; font-size:22px; font-weight:700; margin:0; letter-spacing:-0.5px; }
        .header p { color:#a5b4fc; font-size:13px; margin:6px 0 0; }
        .body { padding:36px 40px; }
        .body h2 { color:#1e293b; font-size:20px; font-weight:700; margin:0 0 8px; }
        .body p { color:#475569; font-size:15px; line-height:1.7; margin:0 0 16px; }
        .highlight { background:#f0f9ff; border-left:4px solid #6366f1; padding:14px 18px; border-radius:0 10px 10px 0; margin:20px 0; }
        .highlight p { margin:0; color:#1e40af; font-size:14px; }
        .cta { text-align:center; margin:28px 0; }
        .cta a { display:inline-block; background: linear-gradient(135deg, #6366f1, #8b5cf6); color:#ffffff; font-size:15px; font-weight:600; padding:14px 36px; border-radius:50px; text-decoration:none; letter-spacing:0.3px; }
        .features { display:table; width:100%; margin:20px 0; border-spacing:8px; }
        .feature { display:table-cell; background:#f8fafc; border-radius:12px; padding:16px; text-align:center; width:33%; }
        .feature .icon { font-size:24px; margin-bottom:6px; }
        .feature strong { display:block; color:#1e293b; font-size:13px; font-weight:600; }
        .feature span { color:#64748b; font-size:12px; }
        .footer { background:#f1f5f9; padding:24px 40px; text-align:center; }
        .footer p { color:#94a3b8; font-size:12px; margin:0 0 4px; line-height:1.6; }
        .footer a { color:#6366f1; text-decoration:none; }
        ';
    }

    private function templates(): array
    {
        $style = $this->baseStyle();

        return [
            // ─────────────────────────────────────────────────────────────
            // 1. REKOMENDASI CERITA KE USER GRATIS
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Rekomendasi Cerita — User Gratis',
                'subject' => '📚 {{name}}, ada cerita yang sayang dilewatkan!',
                'preview_text' => 'Ribuan chapter menunggu kamu — baca sekarang, gratis!',
                'html_body' => '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'.$style.'</style></head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>📚 Novelya</h1>
    <p>Temukan cerita yang mengubah hari-harimu</p>
  </div>
  <div class="body">
    <h2>Hai, {{name}}! 👋</h2>
    <p>Selamat datang di Novelya! Kami senang kamu sudah bergabung. Tapi kami sadar — kamu belum sempat menikmati semua yang tersedia.</p>
    <p>Ribuan cerita original dari penulis berbakat Indonesia menunggu kamu. Dari romance yang bikin baper, thriller yang menegangkan, sampai fantasy yang membawa kamu ke dunia lain.</p>

    <div class="highlight">
      <p>✨ <strong>Minggu ini trending:</strong> Ratusan chapter baru ditambahkan setiap hari. Jangan sampai ketinggalan!</p>
    </div>

    <div class="features">
      <div class="feature">
        <div class="icon">📖</div>
        <strong>1.000+</strong>
        <span>Judul cerita</span>
      </div>
      <div class="feature">
        <div class="icon">✍️</div>
        <strong>Update Harian</strong>
        <span>Chapter baru tiap hari</span>
      </div>
      <div class="feature">
        <div class="icon">❤️</div>
        <strong>Komunitas</strong>
        <span>Ribuan pembaca aktif</span>
      </div>
    </div>

    <p>Dengan berlangganan Premium, kamu bisa baca <strong>semua chapter tanpa batas</strong> — tidak ada iklan, tidak ada tunggu unlock. Langsung baca sampai tamat!</p>

    <div class="cta">
      <a href="{{app_url}}">Mulai Baca Sekarang →</a>
    </div>

    <p style="font-size:13px; color:#94a3b8; text-align:center;">Sudah lebih dari 50.000 pembaca menikmati Novelya Premium. Kapan giliranmu?</p>
  </div>
  <div class="footer">
    <p>Kamu menerima email ini karena terdaftar di <strong>Novelya</strong> sebagai <a href="#">{{email}}</a></p>
    <p><a href="#">Berhenti berlangganan email</a></p>
  </div>
</div>
</body></html>',
            ],

            // ─────────────────────────────────────────────────────────────
            // 2. TRANSAKSI PENDING LEBIH DARI 1 JAM
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Transaksi Pending — Belum Selesai',
                'subject' => '⏳ {{name}}, pembayaranmu belum selesai',
                'preview_text' => 'Selesaikan pembayaran sebelum kedaluwarsa — akses Premium menantimu',
                'html_body' => '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'.$style.'
                .alert { background:#fff7ed; border:1px solid #fed7aa; border-radius:12px; padding:18px 20px; margin:20px 0; text-align:center; }
                .alert p { margin:0; color:#9a3412; font-size:14px; font-weight:500; }
                .steps { margin:20px 0; }
                .step { display:flex; align-items:flex-start; margin-bottom:12px; }
                .step-num { background:#6366f1; color:#fff; width:24px; height:24px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; flex-shrink:0; margin-right:12px; margin-top:2px; }
                .step p { margin:0; color:#475569; font-size:14px; }
                </style></head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>📚 Novelya</h1>
    <p>Selesaikan pembayaranmu</p>
  </div>
  <div class="body">
    <h2>Hei {{name}}, hampir sampai! 🎉</h2>
    <p>Kami lihat kamu sudah memilih paket Premium — tinggal satu langkah lagi! Pembayaranmu masih menunggu konfirmasi.</p>

    <div class="alert">
      <p>⚠️ Pesanan akan <strong>kedaluwarsa otomatis</strong> jika tidak diselesaikan dalam waktu dekat</p>
    </div>

    <p>Cara menyelesaikan pembayaran:</p>
    <div class="steps">
      <div class="step">
        <span class="step-num">1</span>
        <p>Buka aplikasi atau website Novelya</p>
      </div>
      <div class="step">
        <span class="step-num">2</span>
        <p>Masuk ke menu <strong>Riwayat Transaksi</strong></p>
      </div>
      <div class="step">
        <span class="step-num">3</span>
        <p>Pilih transaksi pending dan selesaikan pembayaran</p>
      </div>
    </div>

    <div class="cta">
      <a href="{{app_url}}">Selesaikan Pembayaran →</a>
    </div>

    <p>Butuh bantuan? Balas email ini atau hubungi tim support kami. Kami siap membantu!</p>
    <p style="font-size:13px; color:#94a3b8;">Jika kamu tidak merasa melakukan transaksi ini, abaikan saja email ini.</p>
  </div>
  <div class="footer">
    <p>Email dikirim ke <a href="#">{{email}}</a> · <a href="#">Novelya</a></p>
  </div>
</div>
</body></html>',
            ],

            // ─────────────────────────────────────────────────────────────
            // 3. SUBSCRIPTION AKAN EXPIRED — PENGINGAT
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Subscription Akan Expired — Perpanjang Sekarang',
                'subject' => '🔔 {{name}}, Premium-mu berakhir {{expiry_date}}',
                'preview_text' => 'Perpanjang sekarang dan jangan putus cerita favoritmu',
                'html_body' => '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'.$style.'
                .expire-box { background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius:16px; padding:20px 24px; margin:20px 0; text-align:center; }
                .expire-box .date { font-size:28px; font-weight:800; color:#92400e; margin:4px 0; }
                .expire-box p { margin:0; color:#78350f; font-size:13px; }
                .plan-badge { display:inline-block; background:#6366f1; color:#fff; font-size:12px; font-weight:600; padding:4px 14px; border-radius:20px; margin-bottom:12px; }
                </style></head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>📚 Novelya</h1>
    <p>Jangan sampai terputus</p>
  </div>
  <div class="body">
    <h2>{{name}}, waktumu hampir habis ⏰</h2>
    <span class="plan-badge">{{plan_name}}</span>
    <p>Subscription Premium kamu akan segera berakhir. Perpanjang sekarang agar kamu tidak kehilangan akses ke semua cerita favoritmu.</p>

    <div class="expire-box">
      <p>Berakhir pada</p>
      <div class="date">{{expiry_date}}</div>
      <p>Setelah tanggal ini, akses ke chapter premium akan terkunci</p>
    </div>

    <p>Jika tidak diperpanjang, kamu akan kehilangan:</p>
    <ul style="color:#475569; font-size:15px; line-height:2;">
      <li>Akses ke semua chapter premium</li>
      <li>Baca tanpa iklan</li>
      <li>Baca chapter terbaru sebelum pengguna biasa</li>
    </ul>

    <div class="cta">
      <a href="{{app_url}}">Perpanjang Premium Sekarang →</a>
    </div>

    <p style="font-size:13px; text-align:center; color:#94a3b8;">Sudah lebih dari setahun kamu bersama kami. Terima kasih atas kepercayaanmu! ❤️</p>
  </div>
  <div class="footer">
    <p>Dikirim ke <a href="#">{{email}}</a> · <a href="#">Kelola preferensi email</a></p>
  </div>
</div>
</body></html>',
            ],

            // ─────────────────────────────────────────────────────────────
            // 4. WELCOME SETELAH BAYAR PERTAMA KALI
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Selamat Datang Premium — Pembayaran Berhasil',
                'subject' => '🎉 Selamat {{name}}! Premium aktif — yuk mulai baca!',
                'preview_text' => 'Pembayaran berhasil! Semua chapter premium kini terbuka untukmu',
                'html_body' => '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'.$style.'
                .success-banner { background: linear-gradient(135deg, #d1fae5, #a7f3d0); border-radius:16px; padding:24px; text-align:center; margin:0 0 24px; }
                .success-banner .check { font-size:40px; margin-bottom:8px; }
                .success-banner h3 { color:#065f46; font-size:18px; font-weight:700; margin:0 0 4px; }
                .success-banner p { color:#047857; font-size:13px; margin:0; }
                .perk { display:flex; align-items:center; padding:10px 0; border-bottom:1px solid #f1f5f9; }
                .perk .icon { font-size:20px; margin-right:14px; flex-shrink:0; }
                .perk p { margin:0; color:#334155; font-size:14px; }
                .perk strong { display:block; color:#1e293b; }
                </style></head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>📚 Novelya</h1>
    <p>Premium aktif — selamat menikmati!</p>
  </div>
  <div class="body">
    <div class="success-banner">
      <div class="check">✅</div>
      <h3>Pembayaran Berhasil!</h3>
      <p>Paket {{plan_name}} kamu sudah aktif hingga {{expiry_date}}</p>
    </div>

    <h2>Halo {{name}}, selamat bergabung! 🎊</h2>
    <p>Kamu baru saja membuka dunia yang penuh cerita. Berikut yang bisa kamu nikmati sekarang:</p>

    <div class="perk">
      <span class="icon">📖</span>
      <p><strong>Baca chapter tanpa batas</strong>Semua konten premium terbuka penuh, tidak ada batasan.</p>
    </div>
    <div class="perk">
      <span class="icon">🚫</span>
      <p><strong>Bebas iklan</strong>Pengalaman baca yang bersih dan nyaman.</p>
    </div>
    <div class="perk">
      <span class="icon">⚡</span>
      <p><strong>Akses early chapter</strong>Baca update terbaru lebih dulu dari pembaca biasa.</p>
    </div>
    <div class="perk" style="border-bottom:none;">
      <span class="icon">📱</span>
      <p><strong>Multi-device</strong>Baca di mana saja — HP, tablet, atau komputer.</p>
    </div>

    <div class="cta">
      <a href="{{app_url}}">Mulai Baca Sekarang →</a>
    </div>
  </div>
  <div class="footer">
    <p>Pertanyaan? Balas email ini atau kunjungi <a href="{{app_url}}">novelya.id</a></p>
    <p>Dikirim ke <a href="#">{{email}}</a></p>
  </div>
</div>
</body></html>',
            ],

            // ─────────────────────────────────────────────────────────────
            // 5. RE-ENGAGEMENT USER DORMAN / EXPIRED (lama)
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Re-engagement — Kami Merindukanmu',
                'subject' => '💌 {{name}}, sudah lama tidak melihatmu di Novelya',
                'preview_text' => 'Cerita-cerita baru menantimu — kembali dan baca gratis hari ini',
                'html_body' => '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'.$style.'
                .nostalgia { text-align:center; padding:24px 0; }
                .nostalgia .emoji { font-size:48px; margin-bottom:12px; }
                .offer-box { background: linear-gradient(135deg, #ede9fe, #ddd6fe); border-radius:16px; padding:20px 24px; margin:20px 0; text-align:center; }
                .offer-box h3 { color:#4c1d95; font-size:18px; font-weight:700; margin:0 0 8px; }
                .offer-box p { color:#5b21b6; font-size:14px; margin:0; }
                </style></head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>📚 Novelya</h1>
    <p>Kami kangen kamu!</p>
  </div>
  <div class="body">
    <div class="nostalgia">
      <div class="emoji">😔</div>
    </div>

    <h2>{{name}}, sudah lama tidak kita berjumpa...</h2>
    <p>Kami perhatikan kamu sudah cukup lama tidak mampir ke Novelya. Kami kangen! Dan lebih penting — banyak cerita seru yang belum kamu baca.</p>

    <p>Sejak terakhir kali kamu hadir, kami telah menambahkan:</p>
    <ul style="color:#475569; font-size:15px; line-height:2;">
      <li>Ratusan judul cerita baru</li>
      <li>Ribuan chapter original yang di-update tiap hari</li>
      <li>Fitur bookmark dan komentar yang lebih canggih</li>
    </ul>

    <div class="offer-box">
      <h3>🎁 Spesial untuk kamu</h3>
      <p>Kembali hari ini dan nikmati pengalaman baca yang lebih baik. Cerita-cerita favoritmu masih menunggu.</p>
    </div>

    <p>Tidak perlu alasan panjang — cukup satu klik untuk kembali ke dunia cerita yang kamu cintai.</p>

    <div class="cta">
      <a href="{{app_url}}">Kembali ke Novelya →</a>
    </div>

    <p style="font-size:13px; text-align:center; color:#94a3b8;">Terima kasih sudah pernah bersama kami. Kami harap cerita kami bisa menemanimu lagi ❤️</p>
  </div>
  <div class="footer">
    <p>Dikirim ke <a href="#">{{email}}</a> · <a href="#">Berhenti menerima email</a></p>
  </div>
</div>
</body></html>',
            ],

            // ─────────────────────────────────────────────────────────────
            // 6. DORMAN RINGAN — NUDGE SETELAH 7 HARI TIDAK AKTIF
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Dorman Ringan — Seminggu Tidak Kelihatan',
                'subject' => '👀 {{name}}, kamu masih di sini?',
                'preview_text' => 'Sudah seminggu nih — ada yang kamu lewatkan di Novelya',
                'html_body' => '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'.$style.'
                .peek-box { text-align:center; padding:20px 0 8px; }
                .peek-box .emoji { font-size:52px; }
                .quick-links { display:table; width:100%; margin:20px 0; }
                .quick-link { display:table-cell; text-align:center; padding:0 8px; }
                .quick-link a { display:block; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px 10px; text-decoration:none; color:#475569; font-size:13px; font-weight:500; }
                .quick-link a:hover { background:#ede9fe; border-color:#c4b5fd; color:#5b21b6; }
                .quick-link .ql-icon { font-size:22px; margin-bottom:4px; display:block; }
                </style></head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>📚 Novelya</h1>
    <p>Kamu terlewat sesuatu nih…</p>
  </div>
  <div class="body">
    <div class="peek-box"><div class="emoji">👀</div></div>
    <h2>Hai {{name}}, seminggu berlalu…</h2>
    <p>Terakhir kali kamu aktif sudah seminggu lalu. Banyak yang terjadi di Novelya — chapter baru, cerita yang baru masuk, dan update dari pengarang favoritmu.</p>

    <div class="highlight">
      <p>📖 <strong>Jangan sampai alur ceritamu terputus.</strong> Lanjutkan dari mana kamu berhenti — semuanya masih tersimpan.</p>
    </div>

    <p>Apa yang ingin kamu baca hari ini?</p>
    <div class="quick-links">
      <div class="quick-link">
        <a href="{{app_url}}"><span class="ql-icon">🔥</span>Trending</a>
      </div>
      <div class="quick-link">
        <a href="{{app_url}}"><span class="ql-icon">⭐</span>Favoritku</a>
      </div>
      <div class="quick-link">
        <a href="{{app_url}}"><span class="ql-icon">🆕</span>Terbaru</a>
      </div>
    </div>

    <div class="cta">
      <a href="{{app_url}}">Lanjut Baca →</a>
    </div>

    <p style="font-size:13px; color:#94a3b8; text-align:center;">Subscription kamu masih aktif. Sayang kalau tidak dipakai! 😊</p>
  </div>
  <div class="footer">
    <p>Dikirim ke <a href="#">{{email}}</a> · <a href="#">Novelya</a></p>
  </div>
</div>
</body></html>',
            ],

            // ─────────────────────────────────────────────────────────────
            // 7. EXPIRY MENDESAK — 1 HARI LAGI
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Expiry Mendesak — Berakhir Besok',
                'subject' => '🚨 {{name}}, Premium-mu berakhir BESOK!',
                'preview_text' => 'Perpanjang hari ini — jangan sampai akses terputus tengah cerita',
                'html_body' => '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'.$style.'
                .urgent-banner { background: linear-gradient(135deg, #fef2f2, #fee2e2); border:2px solid #fca5a5; border-radius:16px; padding:20px 24px; margin:20px 0; text-align:center; }
                .urgent-banner .countdown { font-size:42px; font-weight:900; color:#dc2626; letter-spacing:-1px; line-height:1; margin:8px 0; }
                .urgent-banner p { margin:4px 0 0; color:#991b1b; font-size:13px; font-weight:500; }
                .plan-badge { display:inline-block; background:#6366f1; color:#fff; font-size:12px; font-weight:600; padding:4px 14px; border-radius:20px; margin-bottom:12px; }
                .cta a { background: linear-gradient(135deg, #dc2626, #b91c1c) !important; }
                </style></head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>📚 Novelya</h1>
    <p>Waktu hampir habis!</p>
  </div>
  <div class="body">
    <h2>⚠️ {{name}}, ini peringatan terakhir!</h2>
    <span class="plan-badge">{{plan_name}}</span>

    <div class="urgent-banner">
      <p>Subscription kamu berakhir dalam</p>
      <div class="countdown">24 JAM</div>
      <p>Pada {{expiry_date}} — perpanjang sekarang sebelum terlambat</p>
    </div>

    <p>Setelah besok, kamu tidak akan bisa lagi mengakses:</p>
    <ul style="color:#475569; font-size:15px; line-height:2.2;">
      <li><strong>Semua chapter premium</strong> yang sedang kamu ikuti</li>
      <li>Baca tanpa iklan</li>
      <li>Update chapter terbaru lebih awal</li>
    </ul>

    <p>Perpanjang sekarang dan lanjutkan cerita favoritmu tanpa jeda sedetik pun.</p>

    <div class="cta">
      <a href="{{app_url}}">Perpanjang Sekarang — Sebelum Terlambat →</a>
    </div>

    <p style="font-size:12px; text-align:center; color:#94a3b8;">Perpanjangan hanya butuh 1 menit. Ceritamu menunggumu! 📖</p>
  </div>
  <div class="footer">
    <p>Dikirim ke <a href="#">{{email}}</a> · <a href="#">Novelya</a></p>
  </div>
</div>
</body></html>',
            ],

            // ─────────────────────────────────────────────────────────────
            // 8. RENEWAL BERHASIL — KONFIRMASI PERPANJANGAN
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Renewal Berhasil — Terima Kasih!',
                'subject' => '🔄 {{name}}, Premium diperpanjang — terima kasih!',
                'preview_text' => 'Perpanjangan berhasil! Lanjut baca cerita favoritmu tanpa gangguan',
                'html_body' => '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'.$style.'
                .renewal-banner { background: linear-gradient(135deg, #ede9fe, #ddd6fe); border-radius:16px; padding:24px; text-align:center; margin:0 0 24px; }
                .renewal-banner .icon { font-size:40px; margin-bottom:8px; }
                .renewal-banner h3 { color:#4c1d95; font-size:18px; font-weight:700; margin:0 0 4px; }
                .renewal-banner p { color:#5b21b6; font-size:13px; margin:0; }
                .info-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f1f5f9; font-size:14px; }
                .info-row .label { color:#64748b; }
                .info-row .value { color:#1e293b; font-weight:600; }
                </style></head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>📚 Novelya</h1>
    <p>Renewal berhasil!</p>
  </div>
  <div class="body">
    <div class="renewal-banner">
      <div class="icon">🔄</div>
      <h3>Perpanjangan Berhasil!</h3>
      <p>Paket {{plan_name}} kamu sudah aktif hingga {{expiry_date}}</p>
    </div>

    <h2>{{name}}, terima kasih sudah setia! 💜</h2>
    <p>Kamu sudah membuktikan bahwa kamu adalah pembaca sejati. Kami sangat menghargai kepercayaan kamu pada Novelya.</p>

    <div style="background:#f8fafc; border-radius:12px; padding:16px 20px; margin:20px 0;">
      <div class="info-row">
        <span class="label">Paket</span>
        <span class="value">{{plan_name}}</span>
      </div>
      <div class="info-row">
        <span class="label">Aktif hingga</span>
        <span class="value">{{expiry_date}}</span>
      </div>
      <div class="info-row" style="border-bottom:none;">
        <span class="label">Status</span>
        <span class="value" style="color:#059669;">✓ Aktif</span>
      </div>
    </div>

    <p>Ribuan chapter premium sudah menunggumu. Lanjutkan petualangan membacamu sekarang!</p>

    <div class="cta">
      <a href="{{app_url}}">Lanjut Baca →</a>
    </div>
  </div>
  <div class="footer">
    <p>Pertanyaan? Balas email ini untuk bantuan.</p>
    <p>Dikirim ke <a href="#">{{email}}</a> · <a href="#">Novelya</a></p>
  </div>
</div>
</body></html>',
            ],

            // ─────────────────────────────────────────────────────────────
            // 9. PROMO / FLASH OFFER — PENAWARAN TERBATAS
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Promo Spesial — Penawaran Terbatas',
                'subject' => '🎁 {{name}}, ada penawaran spesial khusus untukmu!',
                'preview_text' => 'Penawaran terbatas waktu — dapatkan akses Premium dengan harga terbaik',
                'html_body' => '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'.$style.'
                .promo-hero { background: linear-gradient(135deg, #f59e0b, #d97706); border-radius:20px; padding:28px 24px; text-align:center; margin:0 0 24px; }
                .promo-hero .badge { display:inline-block; background:rgba(255,255,255,0.25); color:#fff; font-size:11px; font-weight:700; padding:4px 14px; border-radius:20px; margin-bottom:10px; letter-spacing:1px; text-transform:uppercase; }
                .promo-hero h3 { color:#fff; font-size:26px; font-weight:900; margin:0 0 8px; line-height:1.2; }
                .promo-hero p { color:rgba(255,255,255,0.85); font-size:13px; margin:0; }
                .timer-note { background:#fff7ed; border:1px solid #fed7aa; border-radius:10px; padding:12px 16px; text-align:center; margin:20px 0; }
                .timer-note p { margin:0; color:#9a3412; font-size:13px; font-weight:500; }
                .cta a { background: linear-gradient(135deg, #f59e0b, #d97706) !important; }
                </style></head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>📚 Novelya</h1>
    <p>Penawaran eksklusif untukmu</p>
  </div>
  <div class="body">
    <div class="promo-hero">
      <span class="badge">✨ Penawaran Spesial</span>
      <h3>Akses Premium<br>Harga Terbaik!</h3>
      <p>Khusus untuk kamu — penawaran ini tidak tersedia untuk umum</p>
    </div>

    <h2>Halo {{name}}! 👋</h2>
    <p>Kami menyiapkan penawaran spesial yang sayang sekali untuk dilewatkan. Ini adalah kesempatan terbaik untuk mendapatkan akses penuh ke semua konten premium Novelya.</p>

    <div class="timer-note">
      <p>⏰ <strong>Penawaran terbatas waktu</strong> — segera ambil sebelum kehabisan!</p>
    </div>

    <p>Dengan Premium kamu bisa menikmati:</p>
    <ul style="color:#475569; font-size:15px; line-height:2.2;">
      <li>✅ Baca <strong>semua chapter</strong> tanpa batasan</li>
      <li>✅ Bebas iklan — fokus hanya pada cerita</li>
      <li>✅ Akses lebih dari <strong>1.000+ judul</strong> cerita original</li>
      <li>✅ Update chapter terbaru setiap hari</li>
    </ul>

    <div class="cta">
      <a href="{{app_url}}">Klaim Penawaran Sekarang →</a>
    </div>

    <p style="font-size:12px; text-align:center; color:#94a3b8;">Penawaran ini dikirim khusus untukmu. Jangan lewatkan! 🎁</p>
  </div>
  <div class="footer">
    <p>Dikirim ke <a href="#">{{email}}</a> · <a href="#">Berhenti menerima email promosi</a></p>
  </div>
</div>
</body></html>',
            ],

            // ─────────────────────────────────────────────────────────────
            // 10. NEWSLETTER BULANAN — KONTEN & UPDATE TERBARU
            // ─────────────────────────────────────────────────────────────
            [
                'name' => 'Newsletter Bulanan — Update Terbaru',
                'subject' => '📰 {{name}}, ini yang terbaru di Novelya bulan ini!',
                'preview_text' => 'Cerita baru, chapter terbanyak, dan yang paling banyak dibaca bulan ini',
                'html_body' => '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'.$style.'
                .section-title { font-size:11px; font-weight:700; color:#6366f1; text-transform:uppercase; letter-spacing:1.5px; margin:0 0 12px; }
                .story-card { background:#f8fafc; border-radius:12px; padding:14px 16px; margin-bottom:10px; display:flex; align-items:flex-start; }
                .story-rank { background:#6366f1; color:#fff; font-size:12px; font-weight:700; width:24px; height:24px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; margin-right:12px; margin-top:2px; }
                .story-info strong { display:block; color:#1e293b; font-size:14px; font-weight:600; margin-bottom:2px; }
                .story-info span { color:#64748b; font-size:12px; }
                .stat-row { display:table; width:100%; margin:20px 0; }
                .stat-cell { display:table-cell; text-align:center; }
                .stat-cell .num { font-size:28px; font-weight:800; color:#6366f1; display:block; }
                .stat-cell .lbl { font-size:11px; color:#94a3b8; }
                .divider { border:none; border-top:1px solid #f1f5f9; margin:24px 0; }
                </style></head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>📚 Novelya</h1>
    <p>Newsletter Bulanan</p>
  </div>
  <div class="body">
    <h2>Hai {{name}}! 👋</h2>
    <p>Inilah rangkuman update terbaik Novelya bulan ini. Ada banyak hal seru yang mungkin kamu lewatkan!</p>

    <div class="stat-row">
      <div class="stat-cell">
        <span class="num">500+</span>
        <span class="lbl">Chapter baru</span>
      </div>
      <div class="stat-cell">
        <span class="num">50+</span>
        <span class="lbl">Judul baru</span>
      </div>
      <div class="stat-cell">
        <span class="num">10rb+</span>
        <span class="lbl">Pembaca aktif</span>
      </div>
    </div>

    <hr class="divider">

    <p class="section-title">🔥 Paling Banyak Dibaca Bulan Ini</p>

    <div class="story-card">
      <span class="story-rank">1</span>
      <div class="story-info">
        <strong>Temukan cerita terpopuler bulan ini</strong>
        <span>Romance · Baru diupdate · 500+ pembaca</span>
      </div>
    </div>
    <div class="story-card">
      <span class="story-rank">2</span>
      <div class="story-info">
        <strong>Koleksi thriller terbaru sudah hadir</strong>
        <span>Thriller · Tamat · 350+ pembaca</span>
      </div>
    </div>
    <div class="story-card">
      <span class="story-rank">3</span>
      <div class="story-info">
        <strong>Fantasy epik yang wajib dibaca</strong>
        <span>Fantasy · Ongoing · 280+ pembaca</span>
      </div>
    </div>

    <hr class="divider">

    <div class="highlight">
      <p>✨ <strong>Konten baru terus ditambahkan</strong> setiap hari oleh penulis-penulis berbakat Indonesia. Pastikan kamu tidak ketinggalan!</p>
    </div>

    <div class="cta">
      <a href="{{app_url}}">Eksplorasi Semua Cerita →</a>
    </div>
  </div>
  <div class="footer">
    <p>Newsletter Novelya · Dikirim ke <a href="#">{{email}}</a></p>
    <p><a href="#">Berhenti menerima newsletter</a></p>
  </div>
</div>
</body></html>',
            ],
        ];
    }
}
