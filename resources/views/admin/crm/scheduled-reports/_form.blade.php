{{-- Report Name --}}
<div class="mb-4">
    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Nama Report *</label>
    <input type="text" name="name" value="{{ old('name', $scheduledReport->name ?? '') }}"
        class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition"
        placeholder="Contoh: Weekly Revenue Report" required>
    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
</div>

{{-- Description --}}
<div class="mb-4">
    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Deskripsi</label>
    <input type="text" name="description" value="{{ old('description', $scheduledReport->description ?? '') }}"
        class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition"
        placeholder="Opsional">
</div>

{{-- Report Type --}}
<div class="mb-4">
    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Tipe Report *</label>
    <select name="report_type"
        class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition" required>
        <option value="" class="dark:bg-slate-800 dark:text-white">-- Pilih tipe --</option>
        <option value="revenue_summary" class="dark:bg-slate-800 dark:text-white" {{ old('report_type', $scheduledReport->report_type ?? '') === 'revenue_summary' ? 'selected' : '' }}>Revenue Summary</option>
        <option value="top_content" class="dark:bg-slate-800 dark:text-white" {{ old('report_type', $scheduledReport->report_type ?? '') === 'top_content' ? 'selected' : '' }}>Top Content</option>
        <option value="churn_alert" class="dark:bg-slate-800 dark:text-white" {{ old('report_type', $scheduledReport->report_type ?? '') === 'churn_alert' ? 'selected' : '' }}>Churn Alert</option>
        <option value="engagement_summary" class="dark:bg-slate-800 dark:text-white" {{ old('report_type', $scheduledReport->report_type ?? '') === 'engagement_summary' ? 'selected' : '' }}>Engagement Summary</option>
    </select>
    @error('report_type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
</div>

{{-- Frequency --}}
<div class="mb-4" x-data="{ freq: '{{ old('frequency', $scheduledReport->frequency ?? 'weekly') }}' }">
    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Frekuensi *</label>
    <select name="frequency" x-model="freq"
        class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition" required>
        <option value="weekly" class="dark:bg-slate-800 dark:text-white">Mingguan</option>
        <option value="monthly" class="dark:bg-slate-800 dark:text-white">Bulanan</option>
    </select>

    <div x-show="freq === 'weekly'" class="mt-3">
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Hari Pengiriman</label>
        <select name="day_of_week"
            class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
            @foreach(['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $i => $day)
                <option value="{{ $i }}" class="dark:bg-slate-800 dark:text-white" {{ (int) old('day_of_week', $scheduledReport->day_of_week ?? 1) === $i ? 'selected' : '' }}>{{ $day }}</option>
            @endforeach
        </select>
    </div>

    <div x-show="freq === 'monthly'" class="mt-3">
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Tanggal Pengiriman</label>
        <input type="number" name="day_of_month" min="1" max="31"
            value="{{ old('day_of_month', $scheduledReport->day_of_month ?? 1) }}"
            class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
    </div>
</div>

{{-- Recipients --}}
<div class="mb-4" x-data="recipientList({{ json_encode(old('recipients', $scheduledReport->recipients ?? [['email' => '', 'name' => '']])) }})">
    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Penerima *</label>
    <div class="space-y-2">
        <template x-for="(r, i) in recipients" :key="i">
            <div class="flex gap-2 items-center">
                <input type="email" :name="'recipients[' + i + '][email]'" x-model="r.email"
                    placeholder="email@example.com"
                    class="flex-1 h-9 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition" required>
                <input type="text" :name="'recipients[' + i + '][name]'" x-model="r.name"
                    placeholder="Nama (opsional)"
                    class="w-40 h-9 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
                <button type="button" @click="remove(i)" x-show="recipients.length > 1"
                    class="w-9 h-9 rounded-xl border border-red-200 dark:border-red-500/20 text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 flex items-center justify-center transition flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </template>
    </div>
    <button type="button" @click="add()"
        class="mt-2 text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">+ Tambah penerima</button>
    @error('recipients') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
</div>

@push('scripts')
<script>
function recipientList(initial) {
    return {
        recipients: initial.length ? initial : [{ email: '', name: '' }],
        add() { this.recipients.push({ email: '', name: '' }); },
        remove(i) { this.recipients.splice(i, 1); },
    };
}
</script>
@endpush
