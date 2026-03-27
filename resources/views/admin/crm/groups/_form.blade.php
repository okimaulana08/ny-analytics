{{-- Nama --}}
<div>
    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
        Nama Grup <span class="text-red-500">*</span>
    </label>
    <input type="text" name="name" value="{{ old('name', $group->name ?? '') }}" required placeholder="Nama grup penerima"
        class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200 dark:border-white/[0.08]' }}">
    @error('name')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
</div>

{{-- Deskripsi --}}
<div>
    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Deskripsi</label>
    <input type="text" name="description" value="{{ old('description', $group->description ?? '') }}" placeholder="Deskripsi singkat (opsional)"
        class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">
</div>

{{-- Tipe Grup --}}
<div>
    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Tipe Grup <span class="text-red-500">*</span></label>
    @php $currentType = old('type', $group->type ?? 'static'); @endphp
    <div class="flex items-center gap-4">
        <label class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
            <input type="radio" name="type" value="static" {{ $currentType === 'static' ? 'checked' : '' }} onchange="toggleGroupType('static')" class="accent-blue-600">
            Static (daftar email tetap)
        </label>
        <label class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
            <input type="radio" name="type" value="dynamic" {{ $currentType === 'dynamic' ? 'checked' : '' }} onchange="toggleGroupType('dynamic')" class="accent-blue-600">
            Dinamis (berdasarkan kriteria)
        </label>
    </div>
</div>

{{-- Static: daftar email --}}
<div id="section-static" class="{{ $currentType !== 'static' ? 'hidden' : '' }}">
    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Anggota Grup</label>
    <div id="members-list" class="space-y-2">
        @php $members = old('members', isset($group) && $group->type === 'static' ? $group->members->map(fn($m) => ['email' => $m->email, 'name' => $m->name])->toArray() : [['email' => '', 'name' => '']]); @endphp
        @foreach($members as $i => $member)
        <div class="flex items-center gap-2 member-row">
            <input type="email" name="members[{{ $i }}][email]" value="{{ $member['email'] ?? '' }}" placeholder="email@example.com"
                class="flex-1 h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40">
            <input type="text" name="members[{{ $i }}][name]" value="{{ $member['name'] ?? '' }}" placeholder="Nama (opsional)"
                class="w-48 h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40">
            <button type="button" onclick="removeMember(this)"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endforeach
    </div>
    <button type="button" onclick="addMember()"
        class="mt-2 h-8 px-3 text-xs font-medium text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-500/30 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-500/10 transition-colors">
        + Tambah Email
    </button>
    @error('members')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
</div>

{{-- Dynamic: filter --}}
<div id="section-dynamic" class="{{ $currentType !== 'dynamic' ? 'hidden' : '' }}">
    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Filter Dinamis <span class="text-red-500">*</span></label>
    @php
        $currentFilter = old('criteria.filter', ($group->criteria ?? [])['filter'] ?? '');
        $currentParams = old('criteria.params', ($group->criteria ?? [])['params'] ?? []);
    @endphp
    <select name="criteria[filter]" id="criteria-filter" onchange="updateCriteriaParams()"
        class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-slate-800 dark:[color-scheme:dark] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500/40">
        <option value="">— Pilih Filter —</option>
        <option value="user_baru" {{ $currentFilter === 'user_baru' ? 'selected' : '' }}>User Baru (registrasi N hari terakhir)</option>
        <option value="akan_expired" {{ $currentFilter === 'akan_expired' ? 'selected' : '' }}>User Akan Expired (dalam N hari)</option>
        <option value="belum_bayar" {{ $currentFilter === 'belum_bayar' ? 'selected' : '' }}>User Belum Bayar</option>
        <option value="user_loyal" {{ $currentFilter === 'user_loyal' ? 'selected' : '' }}>User Loyal (N+ transaksi)</option>
        <option value="baru_bayar_hari_ini" {{ $currentFilter === 'baru_bayar_hari_ini' ? 'selected' : '' }}>User Baru Bayar Hari Ini</option>
        <option value="user_aktif" {{ $currentFilter === 'user_aktif' ? 'selected' : '' }}>Semua User Aktif</option>
        <option value="user_gratis" {{ $currentFilter === 'user_gratis' ? 'selected' : '' }}>User Gratis (belum pernah berlangganan)</option>
        <option value="user_expired" {{ $currentFilter === 'user_expired' ? 'selected' : '' }}>User Expired (subscription sudah habis)</option>
        <option value="user_baru_minggu_ini" {{ $currentFilter === 'user_baru_minggu_ini' ? 'selected' : '' }}>User Baru Minggu Ini (7 hari terakhir)</option>
        <option value="akan_expired_3hari" {{ $currentFilter === 'akan_expired_3hari' ? 'selected' : '' }}>Akan Expired 3 Hari Lagi (urgensi tinggi)</option>
        <option value="user_dorman" {{ $currentFilter === 'user_dorman' ? 'selected' : '' }}>User Dorman (tidak aktif N hari)</option>
    </select>

    {{-- Filter-specific params --}}
    <div id="param-days" class="{{ in_array($currentFilter, ['user_baru', 'akan_expired', 'user_dorman']) ? '' : 'hidden' }} mt-3">
        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Jumlah Hari</label>
        <input type="number" name="criteria[params][days]" value="{{ $currentParams['days'] ?? 30 }}" min="1" max="365"
            class="w-32 h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500/40">
    </div>

    <div id="param-min-trx" class="{{ $currentFilter === 'user_loyal' ? '' : 'hidden' }} mt-3">
        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Minimal Transaksi</label>
        <input type="number" name="criteria[params][min_trx]" value="{{ $currentParams['min_trx'] ?? 3 }}" min="1"
            class="w-32 h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500/40">
    </div>
</div>
