<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Crm\StoreWaTriggerRequest;
use App\Http\Requests\Admin\Crm\UpdateWaTriggerRequest;
use App\Models\WaTrigger;
use App\Models\WaTriggerTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WaTriggerController extends Controller
{
    public function index(): View
    {
        $triggers = WaTrigger::withCount(['templates', 'logs'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.crm.wa-triggers.index', compact('triggers'));
    }

    public function create(): View
    {
        return view('admin.crm.wa-triggers.create');
    }

    public function store(StoreWaTriggerRequest $request): RedirectResponse
    {
        $trigger = WaTrigger::create($request->validated());

        $this->seedTemplates($trigger);

        return redirect()->route('admin.crm.wa-triggers.edit', $trigger)
            ->with('success', 'Trigger WA berhasil dibuat. Silakan review dan edit template pesan di bawah.');
    }

    public function edit(WaTrigger $waTrigger): View
    {
        $waTrigger->load('templates');

        return view('admin.crm.wa-triggers.edit', ['trigger' => $waTrigger]);
    }

    public function update(UpdateWaTriggerRequest $request, WaTrigger $waTrigger): RedirectResponse
    {
        $data = $request->validated();

        $waTrigger->update([
            'name' => $data['name'],
            'type' => $data['type'],
            'delay_value' => $data['delay_value'],
            'delay_unit' => $data['delay_unit'],
            'cooldown_hours' => $data['cooldown_hours'],
        ]);

        // Delete removed templates
        if (! empty($data['delete_template_ids'])) {
            WaTriggerTemplate::where('wa_trigger_id', $waTrigger->id)
                ->whereIn('id', $data['delete_template_ids'])
                ->delete();
        }

        // Upsert templates
        if (! empty($data['templates'])) {
            foreach ($data['templates'] as $tpl) {
                if (! empty($tpl['id'])) {
                    WaTriggerTemplate::where('id', $tpl['id'])
                        ->where('wa_trigger_id', $waTrigger->id)
                        ->update([
                            'body' => $tpl['body'],
                            'is_active' => ! empty($tpl['is_active']),
                        ]);
                } else {
                    WaTriggerTemplate::create([
                        'wa_trigger_id' => $waTrigger->id,
                        'body' => $tpl['body'],
                        'is_active' => ! empty($tpl['is_active']),
                    ]);
                }
            }
        }

        return redirect()->route('admin.crm.wa-triggers.edit', $waTrigger)
            ->with('success', 'Trigger WA berhasil diperbarui.');
    }

    public function toggle(WaTrigger $waTrigger): RedirectResponse
    {
        $waTrigger->update(['is_active' => ! $waTrigger->is_active]);

        $status = $waTrigger->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Trigger \"{$waTrigger->name}\" berhasil {$status}.");
    }

    public function destroy(WaTrigger $waTrigger): RedirectResponse
    {
        $waTrigger->delete();

        return redirect()->route('admin.crm.wa-triggers.index')
            ->with('success', 'Trigger WA berhasil dihapus.');
    }

    private function seedTemplates(WaTrigger $trigger): void
    {
        $templates = match ($trigger->type) {
            WaTrigger::TYPE_PENDING_PAYMENT => $this->pendingPaymentTemplates(),
            WaTrigger::TYPE_EXPIRY_REMINDER => $this->expiryReminderTemplates(),
            default => [],
        };

        foreach ($templates as $body) {
            WaTriggerTemplate::create([
                'wa_trigger_id' => $trigger->id,
                'body' => $body,
                'is_active' => true,
            ]);
        }
    }

    /** @return list<string> */
    private function pendingPaymentTemplates(): array
    {
        return [
            'Hei {name} 👋 Pembayaran {plan_name}-mu belum selesai nih. Yuk selesaikan sekarang biar langsung bisa lanjut baca!',
            'Eh {name}, transaksimu masih pending lho 😅 Cuma butuh beberapa menit kok buat selesaikannya.',
            'Hai {name}! Kamu udah milih {plan_name}, tinggal selangkah lagi. Selesaikan pembayarannya ya 🙏',
            'Halo {name} 😊 Kami lihat transaksi {plan_name}-mu belum dibayar. Mau kami bantu?',
            'Woy {name} 👀 Pembayaranmu udah {minutes_ago} menit pending nih, jangan lupa diselesaikan!',
            '{name}! Masih nunggu kamu nih 😄 Selesaikan pembayaran {plan_name} sekarang ya.',
            'Hi {name}, kayaknya kamu lagi sibuk. Tapi jangan lupa selesaikan transaksi {plan_name}-mu ya 😉',
            'Hei {name} 🌟 Sayang banget kalau dibatalin. Yuk selesaikan pembayaran {plan_name} sekarang!',
            '{name}, transaksimu masih pending 😅 Butuh bantuan? Hubungi kami atau langsung selesaikan ya.',
            'Halo {name}! Pembayaran {plan_name}-mu tinggal tunggu konfirmasi. Cek dan selesaikan ya 💪',
        ];
    }

    /** @return list<string> */
    private function expiryReminderTemplates(): array
    {
        return [
            'Hei {name} 👋 Langganan {plan_name}-mu akan berakhir {days_left} hari lagi ({expired_at}). Jangan sampai keputus ya!',
            'Hai {name}, masih {days_left} hari lagi nih sebelum {plan_name}-mu habis. Perpanjang sekarang biar gak ketinggalan cerita 📚',
            '{name}! Langgananmu berakhir {expired_at}. Perpanjang sekarang dan lanjutkan petualangan membacamu 🚀',
            'Halo {name} 😊 Tinggal {days_left} hari lagi nih! Jangan sampai {plan_name}-mu kedaluwarsa ya.',
            'Hi {name}, ingat-ingat ya — {plan_name}-mu habis tanggal {expired_at}. Perpanjang sekarang lebih hemat! 💸',
            '{name}! Masih {days_left} hari sebelum {plan_name}-mu berakhir. Segera perpanjang biar bacaanmu gak terganggu 📖',
            'Hei {name} 🙏 Kami mau ingatkan, {plan_name}-mu akan berakhir {days_left} hari lagi. Perpanjang yuk!',
            'Hai {name}! Jangan sampai ketinggalan chapter terbaru — perpanjang {plan_name}-mu sebelum {expired_at} ya 🌟',
            '{name}, waktu terus berjalan ⏰ {plan_name}-mu habis {days_left} hari lagi. Perpanjang sekarang, masih banyak cerita seru!',
            'Halo {name}! Kami ingetin nih — langgananmu berakhir {expired_at}. Mau lanjut baca? Perpanjang {plan_name} sekarang 💪',
        ];
    }
}
