@extends('layouts.admin')
@section('title', 'Laporan Transaksi')
@section('page-title', 'Laporan Transaksi')

@section('content')
@php
    $totalTrx    = $kpi->total_trx ?? 0;
    $paidCount   = $kpi->paid_count ?? 0;
    $pendingCount= $kpi->pending_count ?? 0;
    $failedCount = $kpi->failed_count ?? 0;
    $totalRev    = $kpi->total_revenue ?? 0;
    $revToday    = $kpi->revenue_today ?? 0;
    $paidToday   = $kpi->paid_today ?? 0;
@endphp

{{-- KPI Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">

    <div class="glass-card p-5 cursor-default">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($totalTrx) }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Total Transaksi</p>
        <p class="text-sm font-semibold text-blue-600 dark:text-blue-400 mt-0.5">{{ $paidToday }} paid hari ini</p>
    </div>

    <div class="glass-card p-5 cursor-default">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 rounded-full">Paid</span>
        </div>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($paidCount) }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Transaksi Berhasil</p>
        <button onclick="openUserModal('paid_users','User Berbayar','Pernah melakukan transaksi paid')" class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:underline cursor-pointer mt-0.5">Lihat user</button>
    </div>

    <div class="glass-card p-5 cursor-default">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white">Rp {{ number_format($totalRev, 0, ',', '.') }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Total Revenue</p>
        <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 mt-0.5">Rp {{ number_format($revToday, 0, ',', '.') }} hari ini</p>
    </div>

    <div class="glass-card p-5 cursor-default">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($pendingCount) }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pending</p>
        <p class="text-sm font-semibold text-red-500 dark:text-red-400 mt-0.5">{{ $failedCount }} failed</p>
    </div>
</div>

{{-- Filter bar --}}
<div class="flat-card px-5 py-3 mb-4 flex flex-wrap items-center gap-3">
    <form method="GET" action="{{ route('admin.reports.transactions') }}" class="flex flex-wrap items-center gap-3 w-full">
        <select name="status" onchange="this.form.submit()" class="h-9 px-3 text-sm rounded-xl bg-slate-50 dark:bg-white/[0.05] border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/40 cursor-pointer">
            <option value="">Semua Status</option>
            <option value="paid"    {{ request('status')=='paid'    ? 'selected' : '' }}>Paid</option>
            <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
            <option value="failed"  {{ request('status')=='failed'  ? 'selected' : '' }}>Failed</option>
        </select>
        <select name="gateway" onchange="this.form.submit()" class="h-9 px-3 text-sm rounded-xl bg-slate-50 dark:bg-white/[0.05] border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/40 cursor-pointer">
            <option value="">Semua Gateway</option>
            <option value="mayar"   {{ request('gateway')=='mayar'  ? 'selected' : '' }}>Mayar</option>
            <option value="xendit"  {{ request('gateway')=='xendit' ? 'selected' : '' }}>Xendit</option>
        </select>
        @if(request('status') || request('gateway'))
        <a href="{{ route('admin.reports.transactions') }}" class="h-9 px-3 text-sm rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-white flex items-center gap-1.5 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            Reset filter
        </a>
        @endif
        <span class="ml-auto text-xs text-slate-400">{{ number_format($total) }} transaksi total</span>
    </form>
</div>

{{-- WA Template Editor --}}
<div class="flat-card px-5 py-4 mb-4">
    <div class="flex items-center justify-between cursor-pointer select-none" onclick="toggleWaEditor()">
        <div class="flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
            </span>
            <span class="text-sm font-semibold text-slate-700 dark:text-white">Template Pesan WhatsApp</span>
            <span class="text-xs text-slate-400 hidden md:inline">— klik baris untuk buka editor</span>
        </div>
        <svg id="wa-editor-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>

    <div id="wa-editor-body" class="hidden mt-4 space-y-3">
        <div class="text-xs text-slate-400 flex flex-wrap gap-x-4 gap-y-1">
            <span class="font-medium text-slate-500 dark:text-slate-300">Placeholder tersedia:</span>
            <code class="bg-slate-100 dark:bg-white/[0.06] px-1.5 py-0.5 rounded text-slate-600 dark:text-slate-300">{nama}</code>
            <code class="bg-slate-100 dark:bg-white/[0.06] px-1.5 py-0.5 rounded text-slate-600 dark:text-slate-300">{email}</code>
            <code class="bg-slate-100 dark:bg-white/[0.06] px-1.5 py-0.5 rounded text-slate-600 dark:text-slate-300">{plan}</code>
            <code class="bg-slate-100 dark:bg-white/[0.06] px-1.5 py-0.5 rounded text-slate-600 dark:text-slate-300">{nominal}</code>
            <code class="bg-slate-100 dark:bg-white/[0.06] px-1.5 py-0.5 rounded text-slate-600 dark:text-slate-300">{status}</code>
            <code class="bg-slate-100 dark:bg-white/[0.06] px-1.5 py-0.5 rounded text-slate-600 dark:text-slate-300">{tanggal}</code>
            <code class="bg-slate-100 dark:bg-white/[0.06] px-1.5 py-0.5 rounded text-slate-600 dark:text-slate-300">{buku_terakhir}</code>
            <code class="bg-slate-100 dark:bg-white/[0.06] px-1.5 py-0.5 rounded text-slate-600 dark:text-slate-300">{link_invoice}</code>
        </div>
        <textarea id="wa-template"
            rows="4"
            placeholder="Tulis template pesan WA di sini..."
            class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.04] text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 resize-none font-mono"></textarea>
        <div class="flex items-center justify-between">
            <p class="text-[11px] text-slate-400">Template tersimpan otomatis di browser ini.</p>
            <div class="flex gap-2">
                <button onclick="resetWaTemplate()"
                    class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-white/10 text-slate-500 hover:text-slate-700 dark:hover:text-white transition-colors">
                    Reset ke default
                </button>
                <button onclick="saveWaTemplate()"
                    class="px-3 py-1.5 text-xs rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium transition-colors">
                    Simpan Template
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Transaction table --}}
<div class="flat-card">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">Waktu</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">User</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">Plan</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">Nominal</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">Gateway</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Terakhir Dibaca</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Chapter Terakhir</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">Waktu Baca</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                @php
                    $readDiff = null;
                    $readBadge = 'text-slate-400';
                    if ($tx->last_read_at) {
                        $txTs   = \Carbon\Carbon::parse($tx->created_at);
                        $readTs = \Carbon\Carbon::parse($tx->last_read_at);
                        $readDiff = (int) floor(abs($txTs->diffInSeconds($readTs)) / 3600);
                        $readBadge = $readDiff <= 6   ? 'text-emerald-600 dark:text-emerald-400'
                                   : ($readDiff <= 24  ? 'text-amber-600 dark:text-amber-400'
                                                       : 'text-slate-400');
                    }
                @endphp
                <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-4 py-3 font-mono text-[11px] text-slate-400 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($tx->created_at)->format('d/m/Y') }}<br>
                        <span class="text-slate-300 dark:text-slate-600">{{ \Carbon\Carbon::parse($tx->created_at)->format('H:i') }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-xs font-medium text-slate-700 dark:text-slate-200 whitespace-nowrap">{{ $tx->user_name }}</div>
                        <div class="text-[11px] text-slate-400 font-mono">{{ $tx->user_email }}</div>
                        @if($tx->user_phone)<div class="text-[11px] text-slate-400 font-mono">{{ $tx->user_phone }}</div>@endif
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $tx->plan_name }}</td>
                    <td class="px-4 py-3 text-right font-mono text-xs font-semibold text-slate-700 dark:text-slate-200 whitespace-nowrap">
                        Rp {{ number_format($tx->total_amount, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-center font-mono text-[11px] text-slate-400 uppercase whitespace-nowrap">{{ $tx->payment_gateway }}</td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        <span class="badge badge-{{ $tx->status }}">{{ ucfirst($tx->status) }}</span>
                        @if($tx->expired_at)
                        <div class="text-[10px] text-slate-400 mt-0.5 font-mono">exp {{ \Carbon\Carbon::parse($tx->expired_at)->format('d/m/Y') }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 max-w-[180px]">
                        @if($tx->last_content)
                        <div class="text-xs text-slate-600 dark:text-slate-300 truncate" title="{{ $tx->last_content }}">{{ $tx->last_content }}</div>
                        @else
                        <span class="text-[11px] text-slate-300 dark:text-slate-600 italic">Tidak ada data</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 max-w-[160px]">
                        @if($tx->last_chapter)
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate" title="{{ $tx->last_chapter }}">{{ $tx->last_chapter }}</div>
                        @else
                        <span class="text-[11px] text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($tx->last_read_at)
                        <div class="text-[11px] font-mono {{ $readBadge }}">
                            {{ \Carbon\Carbon::parse($tx->last_read_at)->format('d/m H:i') }}
                        </div>
                        @if($readDiff !== null)
                        <div class="text-[10px] text-slate-400">
                            {{ $readDiff <= 0 ? 'bersamaan' : ($readDiff < 24 ? $readDiff.'j sebelum' : round($readDiff/24).'h sebelum') }}
                        </div>
                        @endif
                        @else
                        <span class="text-[11px] text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </td>
                    {{-- Actions --}}
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                        @if($tx->payment_url)
                        <button
                            onclick="openInvoiceModal({{ json_encode($tx->payment_url) }}, {{ json_encode($tx->user_name) }})"
                            title="Lihat Link Invoice"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-500/20 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                        </button>
                        @endif
                        @if($tx->user_phone)
                        <button
                            onclick="openWA({{ json_encode([
                                'nama'         => $tx->user_name,
                                'email'        => $tx->user_email,
                                'plan'         => $tx->plan_name,
                                'nominal'      => 'Rp ' . number_format($tx->total_amount, 0, ',', '.'),
                                'status'       => ucfirst($tx->status),
                                'tanggal'      => \Carbon\Carbon::parse($tx->created_at)->format('d M Y'),
                                'buku_terakhir'=> $tx->last_content ?? '-',
                                'phone'        => $tx->user_phone,
                                'payment_url'  => $tx->payment_url,
                                'expired_at'   => $tx->expired_at,
                            ]) }})"
                            title="Kirim WA ke {{ $tx->user_name }}"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-colors">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </button>
                        @endif
                        @if(!$tx->user_phone && !$tx->payment_url)
                        <span class="text-[11px] text-slate-300 dark:text-slate-600">—</span>
                        @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-5 py-12 text-center text-sm text-slate-400">Belum ada data transaksi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', [
        'page'       => $page,
        'totalPages' => $totalPages,
        'total'      => $total,
        'perPage'    => $perPage,
        'param'      => 'page',
    ])
</div>

{{-- Invoice Modal --}}
<div id="invoice-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeInvoiceModal()"></div>
    <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-blue-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-white">Link Invoice</h3>
                    <p id="invoice-modal-name" class="text-xs text-slate-400"></p>
                </div>
            </div>
            <button onclick="closeInvoiceModal()" class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.08] transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex gap-2">
            <input id="invoice-url-input" type="text" readonly
                class="flex-1 px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.04] text-slate-600 dark:text-slate-300 font-mono focus:outline-none truncate"
                onclick="this.select()">
            <button id="invoice-copy-btn" onclick="copyInvoiceUrl()"
                class="px-3 py-2 text-xs rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors whitespace-nowrap">
                Salin
            </button>
        </div>
        <div class="mt-3">
            <a id="invoice-open-link" href="#" target="_blank"
                class="inline-flex items-center gap-1.5 text-xs text-blue-600 dark:text-blue-400 hover:underline">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Buka di tab baru
            </a>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const WA_TEMPLATE_KEY = 'novelya_wa_template';
const WA_DEFAULT = `Halo {nama}, terima kasih telah berlangganan paket *{plan}* di Novelya! 📚

Transaksi kamu:
• Status   : {status}
• Nominal  : {nominal}
• Tanggal  : {tanggal}
• Buku terakhir: {buku_terakhir}
{link_invoice}
Selamat membaca dan nikmati koleksi cerita terbaik kami ya! 😊`;

// Load template from localStorage on page load
document.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem(WA_TEMPLATE_KEY);
    document.getElementById('wa-template').value = saved ?? WA_DEFAULT;
});

function toggleWaEditor() {
    const body    = document.getElementById('wa-editor-body');
    const chevron = document.getElementById('wa-editor-chevron');
    const hidden  = body.classList.toggle('hidden');
    chevron.style.transform = hidden ? '' : 'rotate(180deg)';
}

function saveWaTemplate() {
    const tpl = document.getElementById('wa-template').value.trim();
    if (!tpl) return;
    localStorage.setItem(WA_TEMPLATE_KEY, tpl);
    const btn = event.currentTarget;
    btn.textContent = 'Tersimpan ✓';
    btn.classList.replace('bg-emerald-600', 'bg-slate-500');
    setTimeout(() => { btn.textContent = 'Simpan Template'; btn.classList.replace('bg-slate-500', 'bg-emerald-600'); }, 2000);
}

function resetWaTemplate() {
    localStorage.removeItem(WA_TEMPLATE_KEY);
    document.getElementById('wa-template').value = WA_DEFAULT;
}

function normalizePhone(raw) {
    // Remove all non-digit characters
    let num = raw.replace(/\D/g, '');
    if (num.startsWith('0'))       num = '62' + num.slice(1);
    else if (num.startsWith('+62')) num = num.slice(1);
    return num;
}

function openWA(data) {
    const tpl  = localStorage.getItem(WA_TEMPLATE_KEY) ?? WA_DEFAULT;

    // Only show invoice link if payment_url exists and transaction is not yet expired/paid
    const now = new Date();
    const isExpired = data.expired_at ? new Date(data.expired_at) < now : false;
    const isPaid = (data.status ?? '').toLowerCase() === 'paid';
    const invoiceLine = (data.payment_url && !isPaid && !isExpired)
        ? '• Link Invoice : ' + data.payment_url
        : '';

    const msg  = tpl
        .replace(/{nama}/g,          data.nama          ?? '')
        .replace(/{email}/g,         data.email         ?? '')
        .replace(/{plan}/g,          data.plan          ?? '')
        .replace(/{nominal}/g,       data.nominal       ?? '')
        .replace(/{status}/g,        data.status        ?? '')
        .replace(/{tanggal}/g,       data.tanggal       ?? '')
        .replace(/{buku_terakhir}/g, data.buku_terakhir ?? '')
        .replace(/{link_invoice}/g,  invoiceLine);

    const phone = normalizePhone(data.phone ?? '');
    if (!phone) { alert('Nomor HP tidak tersedia.'); return; }

    window.open('https://wa.me/' + phone + '?text=' + encodeURIComponent(msg), '_blank');
}

function openInvoiceModal(url, name) {
    document.getElementById('invoice-url-input').value = url;
    document.getElementById('invoice-modal-name').textContent = name;
    document.getElementById('invoice-open-link').href = url;
    document.getElementById('invoice-copy-btn').textContent = 'Salin';
    document.getElementById('invoice-copy-btn').classList.replace('bg-slate-500', 'bg-blue-600');
    document.getElementById('invoice-modal').classList.remove('hidden');
    document.getElementById('invoice-modal').classList.add('flex');
}

function closeInvoiceModal() {
    document.getElementById('invoice-modal').classList.add('hidden');
    document.getElementById('invoice-modal').classList.remove('flex');
}

function copyInvoiceUrl() {
    const input = document.getElementById('invoice-url-input');
    navigator.clipboard.writeText(input.value).then(() => {
        const btn = document.getElementById('invoice-copy-btn');
        btn.textContent = 'Tersalin ✓';
        btn.classList.replace('bg-blue-600', 'bg-slate-500');
        setTimeout(() => {
            btn.textContent = 'Salin';
            btn.classList.replace('bg-slate-500', 'bg-blue-600');
        }, 2000);
    });
}
</script>
@endpush
