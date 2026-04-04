<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Crm\StoreEmailTriggerRequest;
use App\Http\Requests\Admin\Crm\UpdateEmailTriggerRequest;
use App\Models\EmailTemplate;
use App\Models\EmailTrigger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailTriggerController extends Controller
{
    public function index(): View
    {
        $triggers = EmailTrigger::with('template')
            ->withCount('logs')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.crm.triggers.index', compact('triggers'));
    }

    public function create(): View
    {
        return view('admin.crm.triggers.create');
    }

    public function store(StoreEmailTriggerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $trigger = EmailTrigger::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'trigger_type' => $data['trigger_type'],
            'condition' => $data['condition'] ?? null,
            'subject' => $data['subject'],
            'html_body' => $data['html_body'],
            'preview_text' => $data['preview_text'] ?? null,
            'conditions' => $this->buildConditions($data),
            'cooldown_days' => $data['cooldown_days'],
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        // Auto-create an EmailTemplate so the campaign system can use it
        $template = EmailTemplate::create([
            'name' => 'Trigger: '.$trigger->name,
            'subject' => $data['subject'],
            'html_body' => $data['html_body'],
            'preview_text' => $data['preview_text'] ?? null,
            'template_type' => EmailTemplate::TYPE_CUSTOM,
            'is_active' => false,
        ]);

        $trigger->update(['email_template_id' => $template->id]);

        return redirect()->route('admin.crm.triggers.index')
            ->with('success', 'Trigger email berhasil dibuat.');
    }

    public function edit(EmailTrigger $trigger): View
    {
        return view('admin.crm.triggers.edit', compact('trigger'));
    }

    public function update(UpdateEmailTriggerRequest $request, EmailTrigger $trigger): RedirectResponse
    {
        $data = $request->validated();

        $trigger->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'trigger_type' => $data['trigger_type'],
            'condition' => $data['condition'] ?? null,
            'subject' => $data['subject'],
            'html_body' => $data['html_body'],
            'preview_text' => $data['preview_text'] ?? null,
            'conditions' => $this->buildConditions($data),
            'cooldown_days' => $data['cooldown_days'],
        ]);

        // Sync the auto-managed EmailTemplate
        if ($trigger->email_template_id && $trigger->template) {
            $trigger->template->update([
                'name' => 'Trigger: '.$trigger->name,
                'subject' => $data['subject'],
                'html_body' => $data['html_body'],
                'preview_text' => $data['preview_text'] ?? null,
            ]);
        } else {
            $template = EmailTemplate::create([
                'name' => 'Trigger: '.$trigger->name,
                'subject' => $data['subject'],
                'html_body' => $data['html_body'],
                'preview_text' => $data['preview_text'] ?? null,
                'template_type' => EmailTemplate::TYPE_CUSTOM,
                'is_active' => false,
            ]);
            $trigger->update(['email_template_id' => $template->id]);
        }

        return redirect()->route('admin.crm.triggers.index')
            ->with('success', 'Trigger email berhasil diperbarui.');
    }

    public function defaults(Request $request): JsonResponse
    {
        $type = $request->query('type', '');
        $condition = $request->query('condition', '');

        $key = $type.'__'.$condition;

        $map = [
            'pending_payment__invoice_active' => [
                'subject' => 'Hai {name}, selesaikan pembayaran Novelya kamu',
                'preview_text' => 'Satu langkah lagi untuk mengakses ribuan cerita tanpa batas...',
                'html_body' => $this->tplPendingInvoiceActive(),
            ],
            'pending_payment__invoice_expired' => [
                'subject' => '{name}, invoice kamu sudah kedaluwarsa — pilih paket baru yuk!',
                'preview_text' => 'Jangan lewatkan akses tak terbatas ke Novelya. Pilih paket sekarang.',
                'html_body' => $this->tplPendingInvoiceExpired(),
            ],
            'expiry_reminder__before_expiry' => [
                'subject' => 'Paket {plan_name} kamu akan berakhir {days_left} hari lagi',
                'preview_text' => 'Perpanjang sekarang agar tidak terputus dari cerita favoritmu.',
                'html_body' => $this->tplBeforeExpiry(),
            ],
            'expiry_reminder__after_expiry' => [
                'subject' => '{name}, kami kangen kamu di Novelya 👋',
                'preview_text' => 'Cerita favoritmu menunggu — yuk aktifkan langganan lagi.',
                'html_body' => $this->tplAfterExpiry(),
            ],
        ];

        if (! isset($map[$key])) {
            return response()->json(['error' => 'No default for this combination.'], 404);
        }

        return response()->json($map[$key]);
    }

    private function tplPendingInvoiceActive(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Selesaikan Pembayaran</title></head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f5;padding:32px 0;">
  <tr><td align="center">
    <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.07);">
      <!-- Header -->
      <tr><td style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:32px 40px;text-align:center;">
        <p style="margin:0;font-size:24px;font-weight:700;color:#ffffff;letter-spacing:-0.5px;">Novelya</p>
        <p style="margin:8px 0 0;font-size:13px;color:rgba(255,255,255,0.8);">Selesaikan Pembayaranmu</p>
      </td></tr>
      <!-- Body -->
      <tr><td style="padding:36px 40px;">
        <p style="margin:0 0 16px;font-size:16px;color:#1e293b;">Hai, <strong>{name}</strong> 👋</p>
        <p style="margin:0 0 20px;font-size:15px;color:#475569;line-height:1.7;">
          Kami melihat kamu belum menyelesaikan pembayaran untuk paket <strong>{plan_name}</strong> senilai <strong>{amount}</strong>.
          Selesaikan sekarang dan nikmati ribuan cerita tanpa batas!
        </p>
        <!-- CTA -->
        <table cellpadding="0" cellspacing="0" style="margin:24px 0;">
          <tr><td style="background:#6366f1;border-radius:10px;text-align:center;">
            <a href="{invoice_link}" style="display:inline-block;padding:14px 32px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;">
              Bayar Sekarang →
            </a>
          </td></tr>
        </table>
        <p style="margin:0;font-size:13px;color:#94a3b8;">Link pembayaran ini hanya berlaku untuk pesanan kamu. Jika kamu tidak merasa melakukan ini, abaikan email ini.</p>
      </td></tr>
      <!-- Footer -->
      <tr><td style="background:#f8fafc;padding:20px 40px;text-align:center;border-top:1px solid #e2e8f0;">
        <p style="margin:0;font-size:12px;color:#94a3b8;">© 2025 Novelya · <a href="#" style="color:#6366f1;text-decoration:none;">Berhenti berlangganan</a></p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>
HTML;
    }

    private function tplPendingInvoiceExpired(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Invoice Kedaluwarsa</title></head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f5;padding:32px 0;">
  <tr><td align="center">
    <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.07);">
      <!-- Header -->
      <tr><td style="background:linear-gradient(135deg,#f59e0b,#ef4444);padding:32px 40px;text-align:center;">
        <p style="margin:0;font-size:24px;font-weight:700;color:#ffffff;letter-spacing:-0.5px;">Novelya</p>
        <p style="margin:8px 0 0;font-size:13px;color:rgba(255,255,255,0.85);">Invoice Sudah Kedaluwarsa</p>
      </td></tr>
      <!-- Body -->
      <tr><td style="padding:36px 40px;">
        <p style="margin:0 0 16px;font-size:16px;color:#1e293b;">Hai, <strong>{name}</strong></p>
        <p style="margin:0 0 20px;font-size:15px;color:#475569;line-height:1.7;">
          Invoice untuk paket <strong>{plan_name}</strong> senilai <strong>{amount}</strong> sudah kedaluwarsa.
          Tapi jangan khawatir — kamu masih bisa memilih paket baru dan mulai berlangganan sekarang!
        </p>
        <!-- CTA -->
        <table cellpadding="0" cellspacing="0" style="margin:24px 0;">
          <tr><td style="background:#f59e0b;border-radius:10px;text-align:center;">
            <a href="{subscription_url}" style="display:inline-block;padding:14px 32px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;">
              Pilih Paket Sekarang →
            </a>
          </td></tr>
        </table>
        <p style="margin:0;font-size:13px;color:#94a3b8;">Nikmati ribuan cerita seru di Novelya kapan saja dan di mana saja.</p>
      </td></tr>
      <!-- Footer -->
      <tr><td style="background:#f8fafc;padding:20px 40px;text-align:center;border-top:1px solid #e2e8f0;">
        <p style="margin:0;font-size:12px;color:#94a3b8;">© 2025 Novelya · <a href="#" style="color:#f59e0b;text-decoration:none;">Berhenti berlangganan</a></p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>
HTML;
    }

    private function tplBeforeExpiry(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Langganan Akan Berakhir</title></head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f5;padding:32px 0;">
  <tr><td align="center">
    <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.07);">
      <!-- Header -->
      <tr><td style="background:linear-gradient(135deg,#3b82f6,#6366f1);padding:32px 40px;text-align:center;">
        <p style="margin:0;font-size:24px;font-weight:700;color:#ffffff;letter-spacing:-0.5px;">Novelya</p>
        <p style="margin:8px 0 0;font-size:13px;color:rgba(255,255,255,0.85);">Reminder Perpanjang Langganan</p>
      </td></tr>
      <!-- Body -->
      <tr><td style="padding:36px 40px;">
        <p style="margin:0 0 16px;font-size:16px;color:#1e293b;">Hai, <strong>{name}</strong> 👋</p>
        <p style="margin:0 0 20px;font-size:15px;color:#475569;line-height:1.7;">
          Langganan <strong>{plan_name}</strong> kamu akan berakhir pada <strong>{expired_at}</strong>
          — tinggal <strong>{days_left} hari lagi</strong>!
          Perpanjang sekarang agar tidak terputus dari cerita-cerita favoritmu.
        </p>
        <!-- Countdown box -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
          <tr>
            <td style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:16px 20px;text-align:center;">
              <p style="margin:0;font-size:32px;font-weight:800;color:#3b82f6;">{days_left}</p>
              <p style="margin:4px 0 0;font-size:13px;color:#64748b;">hari sebelum berakhir</p>
            </td>
          </tr>
        </table>
        <!-- CTA -->
        <table cellpadding="0" cellspacing="0">
          <tr><td style="background:#3b82f6;border-radius:10px;text-align:center;">
            <a href="#" style="display:inline-block;padding:14px 32px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;">
              Perpanjang Sekarang →
            </a>
          </td></tr>
        </table>
      </td></tr>
      <!-- Footer -->
      <tr><td style="background:#f8fafc;padding:20px 40px;text-align:center;border-top:1px solid #e2e8f0;">
        <p style="margin:0;font-size:12px;color:#94a3b8;">© 2025 Novelya · <a href="#" style="color:#3b82f6;text-decoration:none;">Berhenti berlangganan</a></p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>
HTML;
    }

    private function tplAfterExpiry(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Kami Kangen Kamu</title></head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f5;padding:32px 0;">
  <tr><td align="center">
    <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.07);">
      <!-- Header -->
      <tr><td style="background:linear-gradient(135deg,#8b5cf6,#ec4899);padding:32px 40px;text-align:center;">
        <p style="margin:0;font-size:24px;font-weight:700;color:#ffffff;letter-spacing:-0.5px;">Novelya</p>
        <p style="margin:8px 0 0;font-size:13px;color:rgba(255,255,255,0.85);">Kami Rindu Kamu 💜</p>
      </td></tr>
      <!-- Body -->
      <tr><td style="padding:36px 40px;">
        <p style="margin:0 0 16px;font-size:16px;color:#1e293b;">Hai, <strong>{name}</strong> 👋</p>
        <p style="margin:0 0 20px;font-size:15px;color:#475569;line-height:1.7;">
          Langganan <strong>{plan_name}</strong> kamu sudah berakhir sejak <strong>{expired_at}</strong>.
          Cerita-cerita favoritmu masih menunggu — yuk kembali dan lanjutkan petualangan membacamu!
        </p>
        <!-- Benefit list -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;background:#fdf4ff;border-radius:10px;padding:16px 20px;">
          <tr><td>
            <p style="margin:0 0 8px;font-size:13px;color:#7c3aed;">✦ Akses ribuan cerita tanpa batas</p>
            <p style="margin:0 0 8px;font-size:13px;color:#7c3aed;">✦ Baca offline kapan saja</p>
            <p style="margin:0;font-size:13px;color:#7c3aed;">✦ Update cerita terbaru setiap hari</p>
          </td></tr>
        </table>
        <!-- CTA -->
        <table cellpadding="0" cellspacing="0">
          <tr><td style="background:#8b5cf6;border-radius:10px;text-align:center;">
            <a href="#" style="display:inline-block;padding:14px 32px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;">
              Aktifkan Lagi Sekarang →
            </a>
          </td></tr>
        </table>
      </td></tr>
      <!-- Footer -->
      <tr><td style="background:#f8fafc;padding:20px 40px;text-align:center;border-top:1px solid #e2e8f0;">
        <p style="margin:0;font-size:12px;color:#94a3b8;">© 2025 Novelya · <a href="#" style="color:#8b5cf6;text-decoration:none;">Berhenti berlangganan</a></p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>
HTML;
    }

    public function toggle(EmailTrigger $trigger): RedirectResponse
    {
        $trigger->update(['is_active' => ! $trigger->is_active]);

        $status = $trigger->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Trigger \"{$trigger->name}\" berhasil {$status}.");
    }

    public function destroy(EmailTrigger $trigger): RedirectResponse
    {
        $trigger->delete();

        return redirect()->route('admin.crm.triggers.index')
            ->with('success', 'Trigger email berhasil dihapus.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    private function buildConditions(array $data): ?array
    {
        return match ($data['trigger_type']) {
            EmailTrigger::TYPE_EXPIRY_REMINDER => match ($data['condition'] ?? '') {
                EmailTrigger::COND_AFTER_EXPIRY => ['days_after' => (int) ($data['days_after'] ?? 7)],
                default => ['days_before' => (int) ($data['days_before'] ?? 7)],
            },
            EmailTrigger::TYPE_PENDING_PAYMENT => ['delay_minutes' => (int) ($data['delay_minutes'] ?? 30)],
            default => null,
        };
    }
}
