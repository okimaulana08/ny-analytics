<?php

namespace App\Services;

use App\Models\EmailTemplate;

class EmailTemplateBuilder
{
    private string $novelyaUrl;

    public function __construct()
    {
        $this->novelyaUrl = config('brevo.novelya_url', 'https://novelya.id');
    }

    /**
     * Build HTML for a built-in template with resolved recipient params.
     * For story_recommendation, pass 'stories' key in $params (array of up to 3 story arrays).
     *
     * @param  array<string, mixed>  $params
     */
    public function build(EmailTemplate $template, array $params): string
    {
        return match ($template->template_type) {
            EmailTemplate::TYPE_STORY_RECOMMENDATION => $this->buildStoryRecommendation(
                $template->template_settings ?? [],
                $params,
                $params['stories'] ?? []
            ),
            EmailTemplate::TYPE_PAYMENT_REMINDER => $this->buildPaymentReminder(
                $template->template_settings ?? [],
                $params
            ),
            EmailTemplate::TYPE_PROMO => $this->buildPromo(
                $template->template_settings ?? [],
                $params
            ),
            default => '',
        };
    }

    /**
     * Generate a sample preview HTML for the editor (with dummy data).
     */
    public function sampleHtml(EmailTemplate $template): string
    {
        $sampleParams = [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'expiry_date' => now()->addDays(7)->format('d M Y'),
            'plan_name' => 'Premium Bulanan',
            'invoice_url' => $this->novelyaUrl.'/payment',
            'payment_status' => 'pending',
        ];

        if ($template->template_type === EmailTemplate::TYPE_STORY_RECOMMENDATION) {
            $sampleStories = $this->sampleStories();

            return $this->buildStoryRecommendation([], $sampleParams, $sampleStories);
        }

        if ($template->template_type === EmailTemplate::TYPE_PAYMENT_REMINDER) {
            return $this->buildPaymentReminder($template->template_settings ?? [], $sampleParams);
        }

        if ($template->template_type === EmailTemplate::TYPE_PROMO) {
            return $this->buildPromo($template->template_settings ?? [], $sampleParams);
        }

        return '';
    }

    // ── Story Recommendation ──────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>  $params
     * @param  array<int, array{story_title: string, story_cover: string, story_synopsis: string, story_url: string}>  $stories
     */
    private function buildStoryRecommendation(array $settings, array $params, array $stories): string
    {
        $name = htmlspecialchars($params['name'] ?? 'Kamu', ENT_QUOTES, 'UTF-8');
        $year = now()->year;
        $accentColors = [['#7c3aed', '#4338ca'], ['#db2777', '#be185d'], ['#0891b2', '#0e7490']];

        $storyCards = '';
        foreach (array_slice($stories, 0, 3) as $i => $story) {
            $title = htmlspecialchars($story['story_title'] ?? '', ENT_QUOTES, 'UTF-8');
            $url = htmlspecialchars($story['story_url'] ?? $this->novelyaUrl, ENT_QUOTES, 'UTF-8');
            $cover = htmlspecialchars($story['story_cover'] ?? '', ENT_QUOTES, 'UTF-8');
            $category = htmlspecialchars($story['story_category'] ?? '', ENT_QUOTES, 'UTF-8');
            [$c1, $c2] = $accentColors[$i] ?? ['#7c3aed', '#4338ca'];

            $synopsis = '';
            if (! empty($story['story_synopsis'])) {
                $text = mb_strlen($story['story_synopsis']) > 150
                    ? mb_substr($story['story_synopsis'], 0, 150).'...'
                    : $story['story_synopsis'];
                $synopsis = '<p style="margin:8px 0 0;font-size:13px;color:#6b7280;line-height:1.65;font-family:sans-serif;">'.htmlspecialchars($text, ENT_QUOTES, 'UTF-8').'</p>';
            }

            $catBadge = $category
                ? "<p style=\"margin:6px 0 0;\"><span style=\"font-size:11px;font-weight:700;color:{$c1};background:#f5f3ff;padding:3px 10px;border-radius:20px;font-family:sans-serif;\">{$category}</span></p>"
                : '';

            $coverCell = $cover
                ? "<td width=\"100\" valign=\"top\" style=\"padding-right:18px;\"><a href=\"{$url}\"><img src=\"{$cover}\" width=\"90\" alt=\"\" style=\"display:block;width:90px;height:130px;object-fit:cover;border-radius:10px;border:0;\"></a></td>"
                : "<td width=\"44\" valign=\"top\" style=\"padding-right:14px;\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td width=\"36\" height=\"36\" align=\"center\" valign=\"middle\" style=\"width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,{$c1},{$c2});color:#fff;font-size:18px;font-weight:800;font-family:sans-serif;\">".($i + 1).'</td></tr></table></td>';

            $storyCards .= <<<CARD
<tr>
  <td style="background:#ffffff;padding:6px 32px;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:2px solid #f3f0ff;border-radius:14px;">
      <tr><td style="padding:20px 22px;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
          {$coverCell}
          <td valign="top">
            <p style="margin:0;font-size:17px;font-weight:700;color:#111827;line-height:1.3;font-family:sans-serif;">{$title}</p>
            {$catBadge}
            {$synopsis}
            <a href="{$url}" style="display:inline-block;margin-top:12px;font-size:13px;font-weight:700;color:{$c1};text-decoration:none;border:2px solid {$c1};padding:6px 18px;border-radius:20px;font-family:sans-serif;">Baca Sekarang &rarr;</a>
          </td>
        </tr></table>
      </td></tr>
    </table>
  </td>
</tr>
CARD;
        }

        if ($storyCards === '') {
            $storyCards = '<tr><td style="padding:20px 40px;text-align:center;color:#9ca3af;font-size:14px;font-family:sans-serif;">Tidak ada rekomendasi tersedia saat ini.</td></tr>';
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Cerita pilihan untuk {$name}</title></head>
<body style="margin:0;padding:0;background-color:#f5f3ff;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f5f3ff;">
  <tr><td align="center" style="padding:32px 16px;">
    <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">
      <tr><td style="background:linear-gradient(135deg,#7c3aed,#4338ca);border-radius:20px 20px 0 0;padding:40px 40px 36px;">
        <p style="margin:0 0 4px;font-size:24px;font-weight:800;color:#fff;font-family:sans-serif;">&#128218; Novelya</p>
        <p style="margin:0 0 22px;font-size:13px;color:rgba(255,255,255,0.65);font-family:sans-serif;">Platform cerita digital terbaik</p>
        <h1 style="margin:0;font-size:30px;font-weight:800;color:#fff;line-height:1.25;font-family:sans-serif;">Hai {$name}! &#128075;</h1>
        <p style="margin:8px 0 0;font-size:17px;color:rgba(255,255,255,0.9);font-family:sans-serif;">Ada cerita-cerita yang cocok banget buat kamu &#127919;</p>
      </td></tr>
      <tr><td style="background:#fff;padding:28px 40px 12px;">
        <p style="margin:0;font-size:15px;color:#374151;line-height:1.8;font-family:sans-serif;">
          Kami tahu kamu sibuk, tapi percaya deh &mdash; cerita-cerita di bawah ini <em>worth banget</em> buat dibaca. Dipilihkan khusus berdasarkan selera bacaanmu, jadi kemungkinan besar bakal langsung bikin ketagihan! &#128522;
        </p>
      </td></tr>
      <tr><td style="background:#fff;padding:16px 40px 8px;">
        <p style="margin:0;font-size:11px;font-weight:700;color:#9ca3af;letter-spacing:2px;text-transform:uppercase;font-family:sans-serif;">PILIHAN UNTUKMU</p>
      </td></tr>
      {$storyCards}
      <tr><td style="background:#fff;height:12px;"></td></tr>
      <tr><td style="background:#fff;padding:0 40px;"><hr style="border:none;border-top:2px solid #f3f0ff;margin:0;"></td></tr>
      <tr><td style="background:#fff;padding:28px 40px 36px;text-align:center;">
        <p style="margin:0 0 20px;font-size:15px;color:#6b7280;line-height:1.7;font-family:sans-serif;">Masih banyak cerita seru lainnya yang menunggumu!<br>Buka Novelya dan mulai petualangan bacaanmu sekarang.</p>
        <a href="{$this->novelyaUrl}" style="display:inline-block;background:linear-gradient(135deg,#7c3aed,#4338ca);color:#fff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 40px;border-radius:50px;font-family:sans-serif;">Buka Novelya Sekarang &rarr;</a>
      </td></tr>
      <tr><td style="background:#f5f3ff;border-radius:0 0 20px 20px;padding:20px 40px;text-align:center;">
        <p style="margin:0 0 4px;font-size:12px;color:#9ca3af;font-family:sans-serif;">Kamu menerima email ini karena terdaftar di Novelya.</p>
        <p style="margin:0;font-size:12px;color:#c4b5fd;font-family:sans-serif;">&#169; {$year} Novelya. All rights reserved.</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>
HTML;
    }

    // ── Payment Reminder ──────────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>  $params
     */
    private function buildPaymentReminder(array $settings, array $params): string
    {
        $name = htmlspecialchars($params['name'] ?? 'Kamu', ENT_QUOTES, 'UTF-8');
        $planName = htmlspecialchars($params['plan_name'] ?? 'Premium', ENT_QUOTES, 'UTF-8');
        $expiryDate = htmlspecialchars($params['expiry_date'] ?? '-', ENT_QUOTES, 'UTF-8');
        $invoiceUrl = htmlspecialchars($params['invoice_url'] ?? $this->novelyaUrl.'/payment', ENT_QUOTES, 'UTF-8');
        $year = now()->year;

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Pengingat Pembayaran</title></head>
<body style="margin:0;padding:0;background-color:#fffbeb;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#fffbeb;">
  <tr><td align="center" style="padding:32px 16px;">
    <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

      <tr><td style="background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:20px 20px 0 0;padding:40px 40px 36px;">
        <p style="margin:0 0 4px;font-size:24px;font-weight:800;color:#fff;font-family:sans-serif;">&#128218; Novelya</p>
        <p style="margin:0 0 22px;font-size:13px;color:rgba(255,255,255,0.7);font-family:sans-serif;">Platform cerita digital terbaik</p>
        <p style="margin:0 0 6px;font-size:32px;">&#9200;</p>
        <h1 style="margin:0;font-size:27px;font-weight:800;color:#fff;line-height:1.25;font-family:sans-serif;">Hai {$name}, jangan lupa perpanjang!</h1>
        <p style="margin:10px 0 0;font-size:16px;color:rgba(255,255,255,0.9);font-family:sans-serif;">Akses Premium-mu akan segera berakhir.</p>
      </td></tr>

      <tr><td style="background:#fff;padding:32px 40px 24px;">
        <p style="margin:0 0 20px;font-size:15px;color:#374151;line-height:1.75;font-family:sans-serif;">
          Hei {$name}! Kami hanya ingin mengingatkan bahwa langganan <strong>{$planName}</strong>-mu akan berakhir pada <strong>{$expiryDate}</strong>. Perpanjang sekarang supaya kamu bisa terus menikmati semua cerita tanpa gangguan ya!
        </p>

        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#fffbeb;border-radius:14px;border:2px solid #fde68a;margin-bottom:24px;">
          <tr><td style="padding:20px 24px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="font-size:13px;color:#92400e;font-family:sans-serif;padding:4px 0;">Paket</td>
                <td style="font-size:13px;font-weight:700;color:#111827;text-align:right;font-family:sans-serif;">{$planName}</td>
              </tr>
              <tr>
                <td style="font-size:13px;color:#92400e;font-family:sans-serif;padding:4px 0;">Berakhir pada</td>
                <td style="font-size:13px;font-weight:700;color:#dc2626;text-align:right;font-family:sans-serif;">{$expiryDate}</td>
              </tr>
            </table>
          </td></tr>
        </table>

        <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
          <tr><td align="center">
            <a href="{$invoiceUrl}" style="display:inline-block;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;text-decoration:none;font-size:16px;font-weight:700;padding:15px 48px;border-radius:50px;font-family:sans-serif;">Perpanjang Sekarang &rarr;</a>
          </td></tr>
        </table>

        <p style="margin:20px 0 0;font-size:13px;color:#9ca3af;text-align:center;font-family:sans-serif;">Atau kunjungi <a href="{$invoiceUrl}" style="color:#f59e0b;text-decoration:none;">{$invoiceUrl}</a></p>
      </td></tr>

      <tr><td style="background:#fff;padding:0 40px 32px;text-align:center;">
        <p style="margin:0;font-size:14px;color:#6b7280;line-height:1.7;font-family:sans-serif;">
          Kalau kamu sudah perpanjang, abaikan email ini ya! &#128522;<br>
          Tetap semangat membaca &mdash; kami sudah siapkan banyak cerita seru untukmu.
        </p>
      </td></tr>

      <tr><td style="background:#fffbeb;border-radius:0 0 20px 20px;padding:20px 40px;text-align:center;">
        <p style="margin:0 0 4px;font-size:12px;color:#9ca3af;font-family:sans-serif;">Kamu menerima email ini karena terdaftar di Novelya.</p>
        <p style="margin:0;font-size:12px;color:#fbbf24;font-family:sans-serif;">&#169; {$year} Novelya. All rights reserved.</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>
HTML;
    }

    // ── Promo ─────────────────────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>  $params
     */
    private function buildPromo(array $settings, array $params): string
    {
        $name = htmlspecialchars($params['name'] ?? 'Kamu', ENT_QUOTES, 'UTF-8');
        $bannerUrl = htmlspecialchars($settings['banner_url'] ?? '', ENT_QUOTES, 'UTF-8');
        $headline = htmlspecialchars($settings['promo_headline'] ?? 'Penawaran Spesial Untukmu!', ENT_QUOTES, 'UTF-8');
        $bodyText = $settings['promo_body'] ?? 'Jangan lewatkan penawaran eksklusif ini, hanya untuk kamu!';
        $ctaUrl = htmlspecialchars($settings['cta_url'] ?? $this->novelyaUrl, ENT_QUOTES, 'UTF-8');
        $ctaText = htmlspecialchars($settings['cta_text'] ?? 'Klaim Sekarang', ENT_QUOTES, 'UTF-8');
        $promoCode = ! empty($settings['promo_code']) ? htmlspecialchars($settings['promo_code'], ENT_QUOTES, 'UTF-8') : '';
        $year = now()->year;

        $bannerImg = $bannerUrl
            ? "<tr><td style=\"padding:0;\"><a href=\"{$ctaUrl}\"><img src=\"{$bannerUrl}\" width=\"600\" alt=\"{$headline}\" style=\"display:block;width:100%;height:auto;border:0;\"></a></td></tr>"
            : '<tr><td style="background:linear-gradient(135deg,#7c3aed,#4338ca);padding:60px 40px;text-align:center;"><p style="margin:0;font-size:40px;">&#127881;</p></td></tr>';

        $promoCodeBlock = $promoCode ? <<<BLOCK
        <table cellpadding="0" cellspacing="0" border="0" style="width:100%;margin:20px 0;">
          <tr><td align="center">
            <table cellpadding="0" cellspacing="0" border="0">
              <tr><td style="background:#f5f3ff;border:2px dashed #7c3aed;border-radius:12px;padding:14px 32px;text-align:center;">
                <p style="margin:0 0 4px;font-size:11px;font-weight:700;color:#7c3aed;letter-spacing:2px;text-transform:uppercase;font-family:sans-serif;">KODE PROMO</p>
                <p style="margin:0;font-size:24px;font-weight:800;color:#4338ca;letter-spacing:3px;font-family:monospace;">{$promoCode}</p>
              </td></tr>
            </table>
          </td></tr>
        </table>
BLOCK : '';

        $bodyHtml = nl2br(htmlspecialchars($bodyText, ENT_QUOTES, 'UTF-8'));

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>{$headline}</title></head>
<body style="margin:0;padding:0;background-color:#f5f3ff;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f5f3ff;">
  <tr><td align="center" style="padding:32px 16px;">
    <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;border-radius:20px;overflow:hidden;">

      {$bannerImg}

      <tr><td style="background:#fff;padding:36px 40px 28px;text-align:center;">
        <h1 style="margin:0 0 10px;font-size:28px;font-weight:800;color:#111827;line-height:1.25;font-family:sans-serif;">{$headline}</h1>
        <p style="margin:0 0 4px;font-size:15px;color:#374151;line-height:1.75;font-family:sans-serif;">Hai <strong>{$name}</strong>! &#128075;</p>
        <p style="margin:10px 0 0;font-size:15px;color:#6b7280;line-height:1.75;font-family:sans-serif;">{$bodyHtml}</p>
      </td></tr>

      <tr><td style="background:#fff;padding:0 40px 32px;text-align:center;">
        {$promoCodeBlock}
        <a href="{$ctaUrl}" style="display:inline-block;background:linear-gradient(135deg,#7c3aed,#4338ca);color:#fff;text-decoration:none;font-size:16px;font-weight:700;padding:15px 48px;border-radius:50px;font-family:sans-serif;">{$ctaText} &rarr;</a>
      </td></tr>

      <tr><td style="background:#f5f3ff;padding:20px 40px;text-align:center;">
        <p style="margin:0 0 4px;font-size:12px;color:#9ca3af;font-family:sans-serif;">Kamu menerima email ini karena terdaftar di Novelya.</p>
        <p style="margin:0;font-size:12px;color:#c4b5fd;font-family:sans-serif;">&#169; {$year} Novelya. All rights reserved.</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>
HTML;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * @return array<int, array{story_title: string, story_cover: string, story_synopsis: string, story_url: string, story_category: string}>
     */
    private function sampleStories(): array
    {
        $base = $this->novelyaUrl;

        return [
            [
                'story_title' => 'Cinta di Antara Halaman',
                'story_cover' => 'https://placehold.co/90x130/7c3aed/ffffff?text=Romansa',
                'story_synopsis' => 'Dua jiwa yang bertemu di perpustakaan kota, terikat oleh buku yang sama, terpisah oleh takdir yang berbeda...',
                'story_url' => $base.'/detail/cinta-di-antara-halaman',
                'story_category' => 'Romansa',
            ],
            [
                'story_title' => 'Labirin Kota Hujan',
                'story_cover' => 'https://placehold.co/90x130/0891b2/ffffff?text=Misteri',
                'story_synopsis' => 'Detektif muda harus memecahkan misteri hilangnya artefak kuno di tengah kota yang tidak pernah berhenti hujan.',
                'story_url' => $base.'/detail/labirin-kota-hujan',
                'story_category' => 'Misteri',
            ],
            [
                'story_title' => 'Sang Pewaris Sihir',
                'story_cover' => 'https://placehold.co/90x130/db2777/ffffff?text=Fantasi',
                'story_synopsis' => 'Ketika kekuatan tersembunyi mulai bangkit, seorang gadis biasa harus memilih antara dunia lamanya dan takdir yang menunggunya.',
                'story_url' => $base.'/detail/sang-pewaris-sihir',
                'story_category' => 'Fantasi',
            ],
        ];
    }
}
