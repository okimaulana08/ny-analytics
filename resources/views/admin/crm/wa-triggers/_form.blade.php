{{-- Basic trigger fields --}}
<div class="space-y-5" x-data="{
    type: '{{ old('type', $trigger->type ?? '') }}',
    condition: '{{ old('condition', $trigger->condition ?? '') }}',
    conditionOptions: {
        pending_payment: [
            { value: 'invoice_active',  label: 'Invoice Aktif — invoice belum expired, kirim link bayar' },
            { value: 'invoice_expired', label: 'Invoice Expired — invoice sudah exp., kirim link pilih paket' },
        ],
        expiry_reminder: [
            { value: 'before_expiry', label: 'Sebelum Berakhir — follow up perpanjang sebelum expired' },
            { value: 'after_expiry',  label: 'Setelah Berakhir — tagih user yang sudah expired & belum renew' },
        ],
    },
    get currentConditions() { return this.conditionOptions[this.type] ?? []; },
    get delayUnit() {
        return (this.condition === 'before_expiry' || this.condition === 'after_expiry') ? 'days' : 'minutes';
    },
    get delayLabel() {
        if (this.condition === 'before_expiry') { return 'hari sebelum membership expiry'; }
        if (this.condition === 'after_expiry')  { return 'hari ke belakang (window pencarian expired)'; }
        return 'menit setelah invoice pending dibuat';
    },
}">

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
        <select name="type" x-model="type"
            class="w-full h-9 px-3 text-sm rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition"
            required {{ isset($trigger) ? 'disabled' : '' }}
            @change="condition = ''">
            <option value="">Pilih jenis...</option>
            <option value="pending_payment" {{ old('type', $trigger->type ?? '') === 'pending_payment' ? 'selected' : '' }}>
                Pending Payment
            </option>
            <option value="expiry_reminder" {{ old('type', $trigger->type ?? '') === 'expiry_reminder' ? 'selected' : '' }}>
                Expiry Reminder
            </option>
        </select>
        @isset($trigger)
            <input type="hidden" name="type" value="{{ $trigger->type }}">
        @endisset
        @error('type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Condition + Inline Timing --}}
    <div x-show="currentConditions.length > 0" x-cloak>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Kondisi <span class="text-red-500">*</span></label>

        @isset($trigger)
            {{-- Locked on edit --}}
            @php
                $condBadgeClass = match($trigger->condition ?? '') {
                    'invoice_active'  => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300',
                    'invoice_expired' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300',
                    'before_expiry'   => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
                    'after_expiry'    => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300',
                    default           => 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300',
                };
            @endphp
            <input type="hidden" name="condition" value="{{ $trigger->condition }}">
            <div class="flex items-center gap-2 h-9">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $condBadgeClass }}">
                    {{ $trigger->conditionLabel() }}
                </span>
                <span class="text-xs text-slate-400">(tidak dapat diubah)</span>
            </div>
        @else
            <select name="condition" x-model="condition"
                class="w-full h-9 px-3 text-sm rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition"
                :required="currentConditions.length > 0">
                <option value="">Pilih kondisi...</option>
                <template x-for="opt in currentConditions" :key="opt.value">
                    <option :value="opt.value" :selected="opt.value === condition" x-text="opt.label"></option>
                </template>
            </select>
        @endisset

        {{-- Inline timing — muncul saat kondisi sudah dipilih --}}
        <div class="mt-2" x-show="condition !== ''" x-cloak>
            <div class="flex items-center gap-2">
                <input type="number" name="delay_value"
                    value="{{ old('delay_value', $trigger->delay_value ?? 30) }}"
                    min="1" max="999"
                    class="w-20 h-9 px-3 text-sm rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition text-center"
                    required>
                <input type="hidden" name="delay_unit" :value="delayUnit">
                <span x-text="delayLabel" class="text-xs text-slate-500 dark:text-slate-400"></span>
            </div>
            @error('delay_value') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        @error('condition') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
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
