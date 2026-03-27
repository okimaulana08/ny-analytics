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
            // 5. RE-ENGAGEMENT USER DORMAN / EXPIRED
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
        ];
    }
}
