{{-- Basic trigger fields --}}
<div class="space-y-5">

    {{-- Name --}}
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Nama Trigger <span class="text-red-500">*</span></label>
        <input type="text" name="name" value="{{ old('name', $trigger->name ?? '') }}"
            class="w-full h-9 px-3 text-sm rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition"
            placeholder="cth: Reminder Pending 30 Menit"
            required>
        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Type --}}
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Jenis Trigger <span class="text-red-500">*</span></label>
        <select name="type"
            class="w-full h-9 px-3 text-sm rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition"
            required {{ isset($trigger) ? 'disabled' : '' }}>
            <option value="">Pilih jenis...</option>
            <option value="pending_payment" {{ old('type', $trigger->type ?? '') === 'pending_payment' ? 'selected' : '' }}>
                Pending Payment — reminder setelah X menit/jam belum bayar
            </option>
            <option value="expiry_reminder" {{ old('type', $trigger->type ?? '') === 'expiry_reminder' ? 'selected' : '' }}>
                Expiry Reminder — pengingat X hari sebelum langganan habis
            </option>
        </select>
        {{-- Keep type value when disabled on edit --}}
        @isset($trigger)
            <input type="hidden" name="type" value="{{ $trigger->type }}">
        @endisset
        @error('type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Delay --}}
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">
            Delay
            <span class="font-normal text-slate-400 ml-1">— seberapa lama setelah event baru trigger dikirim</span>
        </label>
        <div class="flex gap-2">
            <input type="number" name="delay_value" value="{{ old('delay_value', $trigger->delay_value ?? 30) }}"
                min="1" max="999"
                class="w-32 h-9 px-3 text-sm rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition"
                required>
            <select name="delay_unit"
                class="flex-1 h-9 px-3 text-sm rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition"
                required>
                <option value="minutes" {{ old('delay_unit', $trigger->delay_unit ?? 'minutes') === 'minutes' ? 'selected' : '' }}>Menit</option>
                <option value="hours" {{ old('delay_unit', $trigger->delay_unit ?? '') === 'hours' ? 'selected' : '' }}>Jam</option>
                <option value="days" {{ old('delay_unit', $trigger->delay_unit ?? '') === 'days' ? 'selected' : '' }}>Hari</option>
            </select>
        </div>
        @error('delay_value') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        @error('delay_unit') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Cooldown --}}
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">
            Cooldown (jam)
            <span class="font-normal text-slate-400 ml-1">— jarak minimum antar pesan ke user yang sama</span>
        </label>
        <input type="number" name="cooldown_hours" value="{{ old('cooldown_hours', $trigger->cooldown_hours ?? 24) }}"
            min="1" max="720"
            class="w-32 h-9 px-3 text-sm rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition"
            required>
        @error('cooldown_hours') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

</div>
