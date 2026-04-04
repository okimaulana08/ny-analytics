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
            'condition' => $data['condition'],
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
        $condition = $trigger->condition ?? ($trigger->type === WaTrigger::TYPE_PENDING_PAYMENT
            ? WaTrigger::COND_INVOICE_ACTIVE
            : WaTrigger::COND_BEFORE_EXPIRY);

        $templates = match ($condition) {
            WaTrigger::COND_INVOICE_ACTIVE => $this->invoiceActiveTemplates(),
            WaTrigger::COND_INVOICE_EXPIRED => $this->invoiceExpiredTemplates(),
            WaTrigger::COND_BEFORE_EXPIRY => $this->beforeExpiryTemplates(),
            WaTrigger::COND_AFTER_EXPIRY => $this->afterExpiryTemplates(),
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
    private function invoiceActiveTemplates(): array
    {
        return [
            'Hei {name} 👋 Pembayaran {plan_name}-mu belum selesai nih. Selesaikan sekarang di sini ya: {invoice_link}',
            'Hai {name}! Transaksimu masih pending 😅 Yuk langsung bayar sebelum invoicenya kedaluwarsa → {invoice_link}',
            '{name}, kamu udah pilih {plan_name}! Tinggal selangkah lagi. Selesaikan pembayaran {amount} di sini: {invoice_link}',
            'Halo {name} 😊 Kami lihat transaksimu belum dibayar. Klik sini buat bayar: {invoice_link}',
            'Woy {name} 👀 Pembayaranmu udah {minutes_ago} menit pending! Cepat bayar sebelum invoicenya kadaluarsa ya → {invoice_link}',
            '{name}! Masih nunggu kamu nih 😄 Bayar {plan_name} sekarang: {invoice_link}',
            'Hi {name}, jangan sampai invoice-nya expired! Selesaikan pembayaran {amount} sekarang 🙏 {invoice_link}',
            'Hei {name} 🌟 Sayang banget kalau gagal. Bayar {plan_name}-mu sekarang sebelum terlambat: {invoice_link}',
            '{name}, invoicemu masih aktif 😊 Selesaikan di sini biar langsung bisa baca: {invoice_link}',
            'Halo {name}! Jangan sampai kelewat ya — bayar {plan_name} sekarang: {invoice_link} 💪',
        ];
    }

    /** @return list<string> */
    private function invoiceExpiredTemplates(): array
    {
        return [
            'Hei {name} 👋 Kayaknya tadi ada kendala bayar {plan_name}. Mau coba lagi? Pilih paket di sini: {subscription_url}',
            'Hai {name}! Invoicemu sudah tidak aktif, tapi kamu masih bisa berlangganan 😊 Cek paket terbaru: {subscription_url}',
            '{name}, invoice sebelumnya sudah kadaluarsa. Gapapa, bikin transaksi baru di sini yuk: {subscription_url}',
            'Halo {name} 😊 Ada kendala dengan pembayaran tadi? Coba lagi lewat link ini: {subscription_url}',
            'Woy {name}! Invoice-nya udah expired tapi kamu masih bisa subscribe {plan_name} lho 💪 → {subscription_url}',
            '{name}, masih pengen baca {plan_name}? Yuk langsung pilih paket di sini: {subscription_url} 📚',
            'Hi {name} 🌟 Jangan sampai ketinggalan cerita! Berlangganan lagi sekarang: {subscription_url}',
            'Hei {name}! Tadi kayaknya gagal bayar ya? Gapapa, coba lagi dari awal: {subscription_url}',
            '{name}, kami perhatiin kamu belum berhasil berlangganan. Ada yang bisa kami bantu? Atau langsung pilih paket: {subscription_url}',
            'Halo {name} 😄 Masih tertarik sama {plan_name}? Yuk berlangganan sekarang: {subscription_url} 🚀',
        ];
    }

    /** @return list<string> */
    private function beforeExpiryTemplates(): array
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

    /** @return list<string> */
    private function afterExpiryTemplates(): array
    {
        return [
            'Hei {name} 👋 Langganan {plan_name}-mu sudah berakhir sejak {expired_at}. Kamu kangen bacaan seru? Yuk perpanjang!',
            'Hai {name}! Kami kangen kamu 😢 {plan_name}-mu berakhir {expired_at}. Balik lagi yuk, banyak cerita baru nih!',
            '{name}, sudah beberapa hari nih sejak {plan_name}-mu kedaluwarsa. Mau lanjut baca? Berlangganan lagi yuk 📚',
            'Halo {name} 😊 Kamu masih punya waktu untuk perpanjang {plan_name}-mu. Jangan sampai ketinggalan ya!',
            'Hi {name}! Langgananmu berakhir {expired_at}. Banyak cerita seru yang nunggu kamu balik 🌟',
            '{name}, kami tunggu kamu kembali! {plan_name}-mu sudah expired. Perpanjang sekarang biar bisa lanjut baca 📖',
            'Hei {name} 🙏 Sudah lama nih gak baca bareng. Perpanjang {plan_name} sekarang dan nikmati semua konten!',
            'Hai {name}! Masih inget {plan_name}? Berakhir {expired_at} — tapi kamu bisa balik kapan aja 😄',
            '{name}, jangan sampai ketinggalan update terbaru! {plan_name}-mu sudah expired. Perpanjang sekarang 💪',
            'Halo {name}! Kami rindu pembacamu 😊 {plan_name} berakhir {expired_at}. Yuk balik lagi, banyak cerita nunggu!',
        ];
    }
}
