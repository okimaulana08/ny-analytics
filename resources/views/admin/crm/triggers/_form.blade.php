<div class="space-y-4">
    {{-- Nama --}}
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nama Trigger <span class="text-red-400">*</span></label>
        <input type="text" name="name" value="{{ old('name', $trigger->name ?? '') }}"
            class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition"
            placeholder="cth. Reminder 7 Hari Sebelum Expiry" required>
        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- Deskripsi --}}
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Deskripsi</label>
        <input type="text" name="description" value="{{ old('description', $trigger->description ?? '') }}"
            class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition"
            placeholder="Opsional — keterangan singkat">
    </div>

    {{-- Jenis Trigger --}}
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Jenis Trigger <span class="text-red-400">*</span></label>
        <select name="trigger_type" id="trigger_type" required
            class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
            <option value="">— Pilih jenis —</option>
            <option value="expiry_reminder" {{ old('trigger_type', $trigger->trigger_type ?? '') === 'expiry_reminder' ? 'selected' : '' }}>
                Reminder Expiry — kirim ke user yang membership-nya mau habis
            </option>
            <option value="re_engagement" {{ old('trigger_type', $trigger->trigger_type ?? '') === 're_engagement' ? 'selected' : '' }}>
                Re-engagement — kirim ke subscriber aktif yang lama tidak baca
            </option>
            <option value="welcome_payment" {{ old('trigger_type', $trigger->trigger_type ?? '') === 'welcome_payment' ? 'selected' : '' }}>
                Welcome Pembayaran — kirim ke user yang baru pertama kali bayar hari ini
            </option>
        </select>
        @error('trigger_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- Kondisi: Expiry Reminder --}}
    <div id="cond_expiry_reminder" class="hidden">
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
            Berapa hari sebelum expiry? <span class="text-red-400">*</span>
        </label>
        <div class="flex items-center gap-2">
            <input type="number" name="days_before" value="{{ old('days_before', $trigger->conditions['days_before'] ?? 7) }}"
                min="1" max="30"
                class="w-24 h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition text-center">
            <span class="text-sm text-slate-500">hari sebelum membership expiry</span>
        </div>
        @error('days_before') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- Kondisi: Re-engagement --}}
    <div id="cond_re_engagement" class="hidden">
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
            Tidak aktif berapa hari? <span class="text-red-400">*</span>
        </label>
        <div class="flex items-center gap-2">
            <input type="number" name="inactive_days" value="{{ old('inactive_days', $trigger->conditions['inactive_days'] ?? 7) }}"
                min="1" max="90"
                class="w-24 h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition text-center">
            <span class="text-sm text-slate-500">hari tanpa membaca chapter</span>
        </div>
        @error('inactive_days') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- Template Email --}}
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Template Email <span class="text-red-400">*</span></label>
        <select name="email_template_id" required
            class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
            <option value="">— Pilih template —</option>
            @foreach($templates as $template)
                <option value="{{ $template->id }}" {{ old('email_template_id', $trigger->email_template_id ?? '') == $template->id ? 'selected' : '' }}>
                    {{ $template->name }}
                    @if($template->template_type !== 'custom')
                        ({{ $template->template_type }})
                    @endif
                </option>
            @endforeach
        </select>
        @error('email_template_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- Cooldown --}}
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
            Cooldown (hari) <span class="text-red-400">*</span>
        </label>
        <div class="flex items-center gap-2">
            <input type="number" name="cooldown_days" value="{{ old('cooldown_days', $trigger->cooldown_days ?? 14) }}"
                min="1" max="365"
                class="w-24 h-10 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition text-center">
            <span class="text-sm text-slate-500">hari — user tidak akan menerima trigger yang sama sebelum periode ini habis</span>
        </div>
        @error('cooldown_days') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>
</div>

<script>
(function () {
    const typeSelect = document.getElementById('trigger_type');
    const condPanels = {
        expiry_reminder: document.getElementById('cond_expiry_reminder'),
        re_engagement:   document.getElementById('cond_re_engagement'),
    };

    function updateConditions() {
        const val = typeSelect.value;
        Object.entries(condPanels).forEach(([key, el]) => {
            if (el) {
                el.classList.toggle('hidden', key !== val);
            }
        });
    }

    typeSelect.addEventListener('change', updateConditions);
    updateConditions();
})();
</script>
