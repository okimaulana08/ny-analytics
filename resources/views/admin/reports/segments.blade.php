@extends('layouts.admin')
@section('title', 'Segmen User')
@section('page-title', 'Segmen User — CRM')

@section('content')

{{-- KPI Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">

    {{-- A: Expiring --}}
    <button onclick="showTab('expiring')" class="glass-card p-5 text-left cursor-pointer tab-card" data-tab="expiring">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-1 rounded-full">7 Hari</span>
        </div>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white">{{ $segmentCounts['expiring'] }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-semibold">Expiring Soon</p>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Member mau habis → kirim renewal reminder</p>
    </button>

    {{-- B: Churned --}}
    <button onclick="showTab('churned')" class="glass-card p-5 text-left cursor-pointer tab-card" data-tab="churned">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-red-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-500/10 px-2 py-1 rounded-full">Churn</span>
        </div>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white">{{ $segmentCounts['churned'] }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-semibold">Churned</p>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Pernah member, sudah expired → win-back</p>
    </button>

    {{-- C: Never Subscribed --}}
    <button onclick="showTab('never')" class="glass-card p-5 text-left cursor-pointer tab-card" data-tab="never">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 px-2 py-1 rounded-full">Convert</span>
        </div>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white">{{ $segmentCounts['never_subscribed'] }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-semibold">Readers Gratis</p>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Aktif baca, belum pernah subscribe</p>
    </button>

    {{-- D: Dormant --}}
    <button onclick="showTab('dormant')" class="glass-card p-5 text-left cursor-pointer tab-card" data-tab="dormant">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-slate-400/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-white/[0.06] px-2 py-1 rounded-full">30+ hari</span>
        </div>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white">{{ $segmentCounts['dormant'] }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-semibold">Dormant</p>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Tidak login 30+ hari → re-engage</p>
    </button>
</div>

{{-- Churn Timeline Chart --}}
<div class="glass-card p-5 mb-5">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Churn Timeline — 90 Hari</h2>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Jumlah membership yang expired per minggu</p>
        </div>
    </div>
    <div class="relative h-40"><canvas id="churnChart"></canvas></div>
</div>

{{-- WA Template Editor --}}
<div class="flat-card px-5 py-4 mb-4">
    <div class="flex items-center justify-between cursor-pointer select-none" onclick="toggleSegWaEditor()">
        <div class="flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
            </span>
            <span class="text-sm font-semibold text-slate-700 dark:text-white">Template Pesan WhatsApp</span>
            <span class="text-xs text-slate-400 hidden md:inline">— 4 template per segmen</span>
        </div>
        <svg id="seg-wa-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>

    <div id="seg-wa-body" class="hidden mt-4 space-y-3">
        {{-- Template sub-tabs --}}
        <div class="flex items-center gap-1 p-1 rounded-xl bg-slate-100 dark:bg-white/[0.04] w-fit">
            <button onclick="switchWaTab('expiring')" id="wa-tab-expiring"
                class="wa-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all bg-white dark:bg-white/10 text-amber-700 dark:text-amber-400 shadow-sm">
                Expiring Soon
            </button>
            <button onclick="switchWaTab('churned')" id="wa-tab-churned"
                class="wa-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all text-slate-500 dark:text-slate-400">
                Churned
            </button>
            <button onclick="switchWaTab('never')" id="wa-tab-never"
                class="wa-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all text-slate-500 dark:text-slate-400">
                Readers Gratis
            </button>
            <button onclick="switchWaTab('dormant')" id="wa-tab-dormant"
                class="wa-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all text-slate-500 dark:text-slate-400">
                Dormant
            </button>
        </div>

        {{-- Placeholders info per tab --}}
        @php
        $waPlaceholders = [
            'expiring' => ['{nama}','{email}','{plan}','{kadaluarsa}','{sisa_hari}'],
            'churned'  => ['{nama}','{email}','{plan_terakhir}','{expired}','{total_trx}','{ltv}'],
            'never'    => ['{nama}','{email}','{total_chapter}','{judul_unik}','{terakhir_baca}'],
            'dormant'  => ['{nama}','{email}','{tipe}','{hari_tidak_aktif}','{terakhir_login}'],
        ];
        @endphp
        @foreach($waPlaceholders as $segKey => $placeholders)
        <div id="wa-placeholders-{{ $segKey }}" class="wa-placeholders {{ $segKey !== 'expiring' ? 'hidden' : '' }}">
            <div class="text-xs text-slate-400 flex flex-wrap gap-x-3 gap-y-1">
                <span class="font-medium text-slate-500 dark:text-slate-300">Placeholder:</span>
                @foreach($placeholders as $ph)
                <code class="bg-slate-100 dark:bg-white/[0.06] px-1.5 py-0.5 rounded text-slate-600 dark:text-slate-300">{{ $ph }}</code>
                @endforeach
            </div>
        </div>
        @endforeach

        {{-- Textareas per tab --}}
        @foreach(['expiring','churned','never','dormant'] as $segKey)
        <div id="wa-textarea-{{ $segKey }}" class="wa-textarea-wrap {{ $segKey !== 'expiring' ? 'hidden' : '' }}">
            <textarea id="wa-tpl-{{ $segKey }}" rows="4"
                class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.04] text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 resize-none font-mono"></textarea>
        </div>
        @endforeach

        <div class="flex items-center justify-between">
            <p class="text-[11px] text-slate-400">Template tersimpan di browser ini per segmen.</p>
            <div class="flex gap-2">
                <button onclick="resetSegWaTemplate()"
                    class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-white/10 text-slate-500 hover:text-slate-700 dark:hover:text-white transition-colors">
                    Reset ke default
                </button>
                <button id="seg-save-btn" onclick="saveSegWaTemplate()"
                    class="px-3 py-1.5 text-xs rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium transition-colors">
                    Simpan Template
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Segment Tables --}}
<div class="flat-card">
    {{-- Tab header --}}
    <div class="px-5 py-3 border-b border-slate-100 dark:border-white/[0.06] flex items-center gap-1 overflow-x-auto">
        @php
        $tabs = [
            ['id' => 'expiring', 'label' => 'Expiring Soon', 'count' => $segmentCounts['expiring'], 'color' => 'amber'],
            ['id' => 'churned',  'label' => 'Churned',       'count' => $segmentCounts['churned'],  'color' => 'red'],
            ['id' => 'never',    'label' => 'Readers Gratis','count' => $segmentCounts['never_subscribed'], 'color' => 'blue'],
            ['id' => 'dormant',  'label' => 'Dormant',       'count' => $segmentCounts['dormant'],  'color' => 'slate'],
        ];
        @endphp
        @foreach($tabs as $tab)
        <button id="tab-btn-{{ $tab['id'] }}" onclick="showTab('{{ $tab['id'] }}')"
            class="tab-btn flex-shrink-0 flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-medium transition-all duration-150">
            {{ $tab['label'] }}
            <span class="tab-badge font-mono text-[10px] px-1.5 py-0.5 rounded-full">{{ $tab['count'] }}</span>
        </button>
        @endforeach
    </div>

    {{-- Segment A: Expiring --}}
    <div id="seg-expiring" class="seg-panel overflow-x-auto">
        <table id="tbl-expiring" class="w-full min-w-max text-sm">
            <thead><tr class="border-b border-slate-100 dark:border-white/[0.05]">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">User</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Plan</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider sortable-th cursor-pointer select-none" onclick="sortTable('tbl-expiring',2)" data-col="2">Expires <svg class="inline w-3 h-3 ml-0.5 -mt-0.5" fill="currentColor" viewBox="0 0 16 16"><path d="M7.247 4.86l-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z" style="opacity:0.3"/><path d="M7.247 11.14l-4.796-5.481c-.566-.647-.106-1.659.753-1.659h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z" style="opacity:0.3"/></svg></th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider sortable-th cursor-pointer select-none" onclick="sortTable('tbl-expiring',3)" data-col="3">Days Left <svg class="inline w-3 h-3 ml-0.5 -mt-0.5" fill="currentColor" viewBox="0 0 16 16"><path d="M7.247 4.86l-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z" style="opacity:0.3"/><path d="M7.247 11.14l-4.796-5.481c-.566-.647-.106-1.659.753-1.659h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z" style="opacity:0.3"/></svg></th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider sortable-th cursor-pointer select-none" onclick="sortTable('tbl-expiring',4)" data-col="4">Last Login <svg class="inline w-3 h-3 ml-0.5 -mt-0.5" fill="currentColor" viewBox="0 0 16 16"><path d="M7.247 4.86l-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z" style="opacity:0.3"/><path d="M7.247 11.14l-4.796-5.481c-.566-.647-.106-1.659.753-1.659h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z" style="opacity:0.3"/></svg></th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">WA</th>
            </tr></thead>
            <tbody>
                @forelse($expiringSoon as $u)
                <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-5 py-3">
                        <div class="text-xs font-medium text-slate-700 dark:text-slate-200">{{ $u->name }}</div>
                        <div class="text-[11px] text-slate-400 font-mono">{{ $u->email }}</div>
                        @if($u->phone_number)<div class="text-[11px] text-slate-400 font-mono">{{ $u->phone_number }}</div>@endif
                    </td>
                    <td class="px-5 py-3 text-xs text-slate-500 dark:text-slate-400">{{ $u->plan_name }}</td>
                    <td class="px-5 py-3 text-center font-mono text-xs text-slate-500" data-val="{{ \Carbon\Carbon::parse($u->expired_at)->format('Y-m-d H:i') }}">{{ \Carbon\Carbon::parse($u->expired_at)->format('d/m/Y H:i') }}</td>
                    <td class="px-5 py-3 text-center" data-val="{{ $u->days_left }}">
                        <span class="font-mono text-xs font-bold {{ $u->days_left <= 1 ? 'text-red-500' : 'text-amber-500 dark:text-amber-400' }}">{{ $u->days_left }}d</span>
                    </td>
                    <td class="px-5 py-3 font-mono text-[11px] text-slate-400" data-val="{{ $u->last_login_at ? \Carbon\Carbon::parse($u->last_login_at)->format('Y-m-d') : '0000-00-00' }}">{{ $u->last_login_at ? \Carbon\Carbon::parse($u->last_login_at)->format('d/m/Y') : '—' }}</td>
                    <td class="px-4 py-3 text-center">@include('admin.partials.wa-btn', ['phone' => $u->phone_number ?? null, 'segment' => 'expiring', 'data' => ['nama' => $u->name, 'email' => $u->email, 'plan' => $u->plan_name, 'kadaluarsa' => \Carbon\Carbon::parse($u->expired_at)->format('d M Y'), 'sisa_hari' => $u->days_left]])</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-400">Tidak ada member yang akan expire dalam 7 hari</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Segment B: Churned --}}
    <div id="seg-churned" class="seg-panel hidden overflow-x-auto">
        <table id="tbl-churned" class="w-full min-w-max text-sm">
            <thead><tr class="border-b border-slate-100 dark:border-white/[0.05]">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">User</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Plan Terakhir</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider sortable-th cursor-pointer select-none" onclick="sortTable('tbl-churned',2)" data-col="2">Expired <svg class="inline w-3 h-3 ml-0.5 -mt-0.5" fill="currentColor" viewBox="0 0 16 16"><path d="M7.247 4.86l-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z" style="opacity:0.3"/><path d="M7.247 11.14l-4.796-5.481c-.566-.647-.106-1.659.753-1.659h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z" style="opacity:0.3"/></svg></th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Trx</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">LTV</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider sortable-th cursor-pointer select-none" onclick="sortTable('tbl-churned',5)" data-col="5">Last Login <svg class="inline w-3 h-3 ml-0.5 -mt-0.5" fill="currentColor" viewBox="0 0 16 16"><path d="M7.247 4.86l-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z" style="opacity:0.3"/><path d="M7.247 11.14l-4.796-5.481c-.566-.647-.106-1.659.753-1.659h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z" style="opacity:0.3"/></svg></th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">WA</th>
            </tr></thead>
            <tbody>
                @forelse($churned as $u)
                <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-5 py-3">
                        <div class="text-xs font-medium text-slate-700 dark:text-slate-200">{{ $u->name }}</div>
                        <div class="text-[11px] text-slate-400 font-mono">{{ $u->email }}</div>
                        @if($u->phone_number)<div class="text-[11px] text-slate-400 font-mono">{{ $u->phone_number }}</div>@endif
                    </td>
                    <td class="px-5 py-3 text-xs text-slate-500 dark:text-slate-400">{{ $u->last_plan }}</td>
                    <td class="px-5 py-3 text-center font-mono text-[11px] text-slate-400" data-val="{{ $u->membership_expired_at ? \Carbon\Carbon::parse($u->membership_expired_at)->format('Y-m-d') : '0000-00-00' }}">{{ $u->membership_expired_at ? \Carbon\Carbon::parse($u->membership_expired_at)->format('d/m/Y') : '—' }}</td>
                    <td class="px-5 py-3 text-right font-mono text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $u->total_trx }}×</td>
                    <td class="px-5 py-3 text-right font-mono text-xs text-emerald-600 dark:text-emerald-400 whitespace-nowrap">Rp {{ number_format($u->lifetime_value, 0, ',', '.') }}</td>
                    <td class="px-5 py-3 font-mono text-[11px] text-slate-400" data-val="{{ $u->last_login_at ? \Carbon\Carbon::parse($u->last_login_at)->format('Y-m-d') : '0000-00-00' }}">{{ $u->last_login_at ? \Carbon\Carbon::parse($u->last_login_at)->format('d/m/Y') : '—' }}</td>
                    <td class="px-4 py-3 text-center">@include('admin.partials.wa-btn', ['phone' => $u->phone_number ?? null, 'segment' => 'churned', 'data' => ['nama' => $u->name, 'email' => $u->email, 'plan_terakhir' => $u->last_plan, 'expired' => $u->membership_expired_at ? \Carbon\Carbon::parse($u->membership_expired_at)->format('d M Y') : '-', 'total_trx' => $u->total_trx, 'ltv' => 'Rp ' . number_format($u->lifetime_value, 0, ',', '.')]])</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">Tidak ada data churn</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Segment C: Never Subscribed --}}
    <div id="seg-never" class="seg-panel hidden overflow-x-auto">
        <table class="w-full min-w-max text-sm">
            <thead><tr class="border-b border-slate-100 dark:border-white/[0.05]">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">User</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Chapters Dibaca</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Judul Unik</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Terakhir Baca</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Hari Sejak</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Daftar</th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">WA</th>
            </tr></thead>
            <tbody>
                @forelse($neverSubscribed as $u)
                <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-5 py-3">
                        <div class="text-xs font-medium text-slate-700 dark:text-slate-200">{{ $u->name }}</div>
                        <div class="text-[11px] text-slate-400 font-mono">{{ $u->email }}</div>
                        @if($u->phone_number)<div class="text-[11px] text-slate-400 font-mono">{{ $u->phone_number }}</div>@endif
                    </td>
                    <td class="px-5 py-3 text-right font-mono text-xs font-semibold text-blue-600 dark:text-blue-400">{{ number_format($u->total_chapters_read) }}</td>
                    <td class="px-5 py-3 text-right font-mono text-xs text-slate-500">{{ $u->unique_contents }}</td>
                    <td class="px-5 py-3 text-center font-mono text-[11px] text-slate-400">{{ $u->last_read_at ? \Carbon\Carbon::parse($u->last_read_at)->format('d/m/Y') : '—' }}</td>
                    <td class="px-5 py-3 text-right font-mono text-xs {{ ($u->days_since_read ?? 999) <= 7 ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-slate-400' }}">{{ $u->days_since_read ?? '—' }}d</td>
                    <td class="px-5 py-3 font-mono text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($u->created_at)->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-center">@include('admin.partials.wa-btn', ['phone' => $u->phone_number ?? null, 'segment' => 'never', 'data' => ['nama' => $u->name, 'email' => $u->email, 'total_chapter' => number_format($u->total_chapters_read), 'judul_unik' => $u->unique_contents, 'terakhir_baca' => $u->last_read_at ? \Carbon\Carbon::parse($u->last_read_at)->format('d M Y') : '-']])</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">Semua reader sudah berlangganan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Segment D: Dormant --}}
    <div id="seg-dormant" class="seg-panel hidden overflow-x-auto">
        <table id="tbl-dormant" class="w-full min-w-max text-sm">
            <thead><tr class="border-b border-slate-100 dark:border-white/[0.05]">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">User</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tipe</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider sortable-th cursor-pointer select-none" onclick="sortTable('tbl-dormant',2)" data-col="2">Days Inactive <svg class="inline w-3 h-3 ml-0.5 -mt-0.5" fill="currentColor" viewBox="0 0 16 16"><path d="M7.247 4.86l-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z" style="opacity:0.3"/><path d="M7.247 11.14l-4.796-5.481c-.566-.647-.106-1.659.753-1.659h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z" style="opacity:0.3"/></svg></th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider sortable-th cursor-pointer select-none" onclick="sortTable('tbl-dormant',3)" data-col="3">Last Login <svg class="inline w-3 h-3 ml-0.5 -mt-0.5" fill="currentColor" viewBox="0 0 16 16"><path d="M7.247 4.86l-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z" style="opacity:0.3"/><path d="M7.247 11.14l-4.796-5.481c-.566-.647-.106-1.659.753-1.659h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z" style="opacity:0.3"/></svg></th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Reminder Terakhir</th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">WA</th>
            </tr></thead>
            <tbody>
                @forelse($dormant as $u)
                <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-5 py-3">
                        <div class="text-xs font-medium text-slate-700 dark:text-slate-200">{{ $u->name }}</div>
                        <div class="text-[11px] text-slate-400 font-mono">{{ $u->email }}</div>
                        @if($u->phone_number)<div class="text-[11px] text-slate-400 font-mono">{{ $u->phone_number }}</div>@endif
                    </td>
                    <td class="px-5 py-3 text-center">
                        @if($u->dormant_type === 'lapsed_member')
                            <span class="badge badge-failed">Lapsed</span>
                        @else
                            <span class="badge badge-expired">Never sub</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right font-mono text-xs font-bold {{ ($u->days_inactive ?? 0) >= 60 ? 'text-red-500' : 'text-slate-500 dark:text-slate-400' }}" data-val="{{ $u->days_inactive ?? 0 }}">{{ $u->days_inactive }}d</td>
                    <td class="px-5 py-3 text-center font-mono text-[11px] text-slate-400" data-val="{{ $u->last_login_at ? \Carbon\Carbon::parse($u->last_login_at)->format('Y-m-d') : '0000-00-00' }}">{{ $u->last_login_at ? \Carbon\Carbon::parse($u->last_login_at)->format('d/m/Y') : '—' }}</td>
                    <td class="px-5 py-3 text-center font-mono text-[11px] text-slate-400">{{ $u->inactive_reminder_sent_at ? \Carbon\Carbon::parse($u->inactive_reminder_sent_at)->format('d/m/Y') : '—' }}</td>
                    <td class="px-4 py-3 text-center">@include('admin.partials.wa-btn', ['phone' => $u->phone_number ?? null, 'segment' => 'dormant', 'data' => ['nama' => $u->name, 'email' => $u->email, 'tipe' => $u->dormant_type === 'lapsed_member' ? 'Member Lapsed' : 'Belum Berlangganan', 'hari_tidak_aktif' => $u->days_inactive, 'terakhir_login' => $u->last_login_at ? \Carbon\Carbon::parse($u->last_login_at)->format('d M Y') : '-']])</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-400">Tidak ada user dormant</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const dark      = document.documentElement.classList.contains('dark');
const gridColor = dark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
const tickColor = dark ? '#475569' : '#94a3b8';
const ttDefaults = { backgroundColor: dark ? '#1e293b':'#fff', titleColor: dark?'#f1f5f9':'#1e293b', bodyColor: dark?'#94a3b8':'#64748b', borderColor: dark?'#334155':'#e2e8f0', borderWidth:1, padding:10, cornerRadius:10 };

// Churn timeline
const churnData = @json($churnTimeline);
new Chart(document.getElementById('churnChart'), {
    type: 'bar',
    data: {
        labels: churnData.map(d => d.week_start),
        datasets: [{ label: 'Expired', data: churnData.map(d => d.churned_count), backgroundColor: dark ? 'rgba(248,113,113,0.4)' : 'rgba(239,68,68,0.35)', borderRadius: 4, borderSkipped: false }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { ...ttDefaults, callbacks: { label: ctx => ` ${ctx.raw} membership expired` } } }, scales: { x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 10, family: 'Fira Code' } }, border: { display: false } }, y: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 10 }, stepSize: 1 }, border: { display: false } } } }
});

// Tab switcher
const tabColors = { expiring: 'amber', churned: 'red', never: 'blue', dormant: 'slate' };
const activeClasses = { amber: 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400', red: 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400', blue: 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400', slate: 'bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300' };
const badgeActive  = { amber: 'bg-amber-100 dark:bg-amber-500/20', red: 'bg-red-100 dark:bg-red-500/20', blue: 'bg-blue-100 dark:bg-blue-500/20', slate: 'bg-slate-200 dark:bg-white/10' };

function showTab(id) {
    // panels
    document.querySelectorAll('.seg-panel').forEach(p => p.classList.add('hidden'));
    document.getElementById('seg-' + id)?.classList.remove('hidden');

    // tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.className = 'tab-btn flex-shrink-0 flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-medium transition-all duration-150 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.04]';
        btn.querySelector('.tab-badge').className = 'tab-badge font-mono text-[10px] px-1.5 py-0.5 rounded-full bg-slate-100 dark:bg-white/[0.06] text-slate-500';
    });
    const activeBtn = document.getElementById('tab-btn-' + id);
    if (activeBtn) {
        const color = tabColors[id];
        activeBtn.className = `tab-btn flex-shrink-0 flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-medium transition-all duration-150 ${activeClasses[color]}`;
        activeBtn.querySelector('.tab-badge').className = `tab-badge font-mono text-[10px] px-1.5 py-0.5 rounded-full ${badgeActive[color]}`;
    }

    // card highlight
    document.querySelectorAll('.tab-card').forEach(c => c.style.outline = '');
    document.querySelector(`[data-tab="${id}"]`)?.style.setProperty('outline', '2px solid rgba(99,130,246,0.4)');
}

// Init first tab
showTab('expiring');

// ── WA Template Editor ──────────────────────────────────────────────────────

const WA_SEG_DEFAULTS = {
    expiring: `Halo {nama}, langganan *{plan}* kamu akan berakhir dalam *{sisa_hari} hari* lagi (tanggal {kadaluarsa}) 😟

Perpanjang sekarang agar bisa terus menikmati koleksi cerita Novelya! 📚

Ketuk link berikut untuk subscribe: [link promo]`,

    churned: `Hai {nama}, kami kangen kamu di Novelya! 😢

Langganan terakhirmu (*{plan_terakhir}*) sudah berakhir sejak {expired}. Kamu sudah pernah berlangganan {total_trx}× dengan total {ltv}.

Yuk balik lagi! Ada promo menarik yang sayang dilewatkan 🎁`,

    never: `Hai {nama}! 👋

Kamu sudah baca *{total_chapter} chapter* dari *{judul_unik} judul* di Novelya — keren banget! 🎉

Kalau mau akses lebih banyak konten premium tanpa batas, coba berlangganan sekarang yuk. Harga mulai terjangkau! 📖✨`,

    dormant: `Halo {nama}, lama tidak jumpa! 😊

Sudah *{hari_tidak_aktif} hari* kamu tidak mampir ke Novelya. Ada banyak cerita seru dan update terbaru yang menunggumu lho!

Yuk balik dan baca lagi. Kami tunggu kamu! 📚❤️`,
};

const WA_SEG_KEYS = {
    expiring: 'novelya_wa_seg_expiring',
    churned:  'novelya_wa_seg_churned',
    never:    'novelya_wa_seg_never',
    dormant:  'novelya_wa_seg_dormant',
};

let activeWaTab = 'expiring';

function loadSegTemplates() {
    Object.keys(WA_SEG_KEYS).forEach(seg => {
        const saved = localStorage.getItem(WA_SEG_KEYS[seg]);
        document.getElementById('wa-tpl-' + seg).value = saved ?? WA_SEG_DEFAULTS[seg];
    });
}

function toggleSegWaEditor() {
    const body    = document.getElementById('seg-wa-body');
    const chevron = document.getElementById('seg-wa-chevron');
    const hidden  = body.classList.toggle('hidden');
    chevron.style.transform = hidden ? '' : 'rotate(180deg)';
    if (!hidden) loadSegTemplates();
}

function switchWaTab(seg) {
    activeWaTab = seg;
    const tabColors = { expiring: 'text-amber-700 dark:text-amber-400', churned: 'text-red-600 dark:text-red-400', never: 'text-blue-700 dark:text-blue-400', dormant: 'text-slate-600 dark:text-slate-300' };

    document.querySelectorAll('.wa-tab-btn').forEach(btn => {
        btn.classList.remove('bg-white','dark:bg-white/10','shadow-sm','text-amber-700','dark:text-amber-400','text-red-600','dark:text-red-400','text-blue-700','dark:text-blue-400','text-slate-600','dark:text-slate-300');
        btn.classList.add('text-slate-500','dark:text-slate-400');
    });
    const active = document.getElementById('wa-tab-' + seg);
    active.classList.remove('text-slate-500','dark:text-slate-400');
    active.classList.add('bg-white','dark:bg-white/10','shadow-sm', ...tabColors[seg].split(' '));

    document.querySelectorAll('.wa-textarea-wrap').forEach(el => el.classList.add('hidden'));
    document.getElementById('wa-textarea-' + seg).classList.remove('hidden');
    document.querySelectorAll('.wa-placeholders').forEach(el => el.classList.add('hidden'));
    document.getElementById('wa-placeholders-' + seg).classList.remove('hidden');
}

// Sync WA template editor tab when switching segment tab
const _origShowTab = showTab;
window.showTab = function(id) {
    _origShowTab(id);
    const map = { expiring: 'expiring', churned: 'churned', never: 'never', dormant: 'dormant' };
    if (map[id]) switchWaTab(map[id]);
};

function saveSegWaTemplate() {
    Object.keys(WA_SEG_KEYS).forEach(seg => {
        const val = document.getElementById('wa-tpl-' + seg).value.trim();
        if (val) localStorage.setItem(WA_SEG_KEYS[seg], val);
    });
    const btn = document.getElementById('seg-save-btn');
    btn.textContent = 'Tersimpan ✓';
    btn.classList.replace('bg-emerald-600', 'bg-slate-500');
    setTimeout(() => { btn.textContent = 'Simpan Template'; btn.classList.replace('bg-slate-500', 'bg-emerald-600'); }, 2000);
}

function resetSegWaTemplate() {
    Object.keys(WA_SEG_KEYS).forEach(seg => {
        localStorage.removeItem(WA_SEG_KEYS[seg]);
        document.getElementById('wa-tpl-' + seg).value = WA_SEG_DEFAULTS[seg];
    });
}

function normalizePhone(raw) {
    let num = (raw ?? '').replace(/\D/g, '');
    if (num.startsWith('0')) num = '62' + num.slice(1);
    return num;
}

function openSegmentWA(segment, data) {
    const key = WA_SEG_KEYS[segment];
    let tpl   = localStorage.getItem(key) ?? WA_SEG_DEFAULTS[segment];
    let msg   = tpl;
    Object.entries(data).forEach(([k, v]) => {
        msg = msg.replace(new RegExp('\\{' + k + '\\}', 'g'), v ?? '');
    });
    const phone = normalizePhone(data.phone ?? '');
    if (!phone) { alert('Nomor HP tidak tersedia.'); return; }
    window.open('https://wa.me/' + phone + '?text=' + encodeURIComponent(msg), '_blank');
}

// Load templates on page load (in background)
document.addEventListener('DOMContentLoaded', loadSegTemplates);

// ── Table Sorting ────────────────────────────────────────────────────────────
const sortState = {};

function sortTable(tableId, colIndex) {
    const table = document.getElementById(tableId);
    const tbody = table.querySelector('tbody');
    const rows  = Array.from(tbody.querySelectorAll('tr'));

    if (!rows.length || rows[0].cells.length <= 1) return; // empty state row

    const key    = tableId + ':' + colIndex;
    const asc    = sortState[key] !== true;
    sortState[key] = asc;

    rows.sort((a, b) => {
        const aCell = a.cells[colIndex];
        const bCell = b.cells[colIndex];
        const aVal  = aCell?.dataset.val ?? aCell?.textContent.trim() ?? '';
        const bVal  = bCell?.dataset.val ?? bCell?.textContent.trim() ?? '';
        const aNum  = parseFloat(aVal);
        const bNum  = parseFloat(bVal);
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return asc ? aNum - bNum : bNum - aNum;
        }
        return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
    });

    rows.forEach(r => tbody.appendChild(r));

    // Update header icons
    table.querySelectorAll('.sortable-th').forEach(th => {
        th.querySelectorAll('svg').forEach(svg => {
            svg.querySelectorAll('path').forEach((p, i) => {
                p.style.opacity = '0.3';
            });
        });
    });
    const activeTh = table.querySelector(`[data-col="${colIndex}"]`);
    if (activeTh) {
        const paths = activeTh.querySelectorAll('svg path');
        if (paths.length >= 2) {
            paths[0].style.opacity = asc  ? '1' : '0.3'; // up
            paths[1].style.opacity = !asc ? '1' : '0.3'; // down
        }
    }
}
</script>
@endpush
