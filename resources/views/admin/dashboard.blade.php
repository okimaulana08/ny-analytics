@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">

    {{-- Paid Today --}}
    <div class="glass-card p-5 cursor-default">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 rounded-full uppercase tracking-wider">Hari Ini</span>
        </div>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white leading-none">{{ $paidToday->count ?? 0 }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-1">Transaksi Paid</p>
        <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format((float)($paidToday->total ?? 0), 0, ',', '.') }}</p>
    </div>

    {{-- Pending Today --}}
    <div class="glass-card p-5 cursor-default">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-1 rounded-full uppercase tracking-wider">Hari Ini</span>
        </div>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white leading-none">{{ $pendingToday }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-1">Transaksi Pending</p>
        <p class="text-sm font-semibold text-amber-600 dark:text-amber-400">Menunggu pembayaran</p>
    </div>

    {{-- User Access --}}
    <div class="glass-card p-5 cursor-default">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 px-2 py-1 rounded-full uppercase tracking-wider">Hari Ini</span>
        </div>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white leading-none">{{ number_format($userAccessToday) }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-1">Akses User</p>
        <p class="text-sm font-semibold text-blue-600 dark:text-blue-400">Total views</p>
    </div>

    {{-- Date --}}
    <div class="glass-card p-5 cursor-default">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-violet-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        <p class="font-mono text-xl font-bold text-slate-900 dark:text-white leading-none">{{ now()->locale('id')->isoFormat('D MMM Y') }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-1">Tanggal</p>
        <p class="text-sm font-semibold text-violet-600 dark:text-violet-400">{{ now()->locale('id')->isoFormat('dddd') }}</p>
    </div>
</div>

{{-- Chart Row --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-5">

    {{-- 7-day chart --}}
    <div class="xl:col-span-2 glass-card p-5">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Transaksi Paid — 7 Hari</h2>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Jumlah & nominal transaksi berhasil</p>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-400 dark:text-slate-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-1.5 rounded-full inline-block bg-blue-500"></span>Count
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-1.5 rounded-full inline-block bg-teal-500"></span>Nominal
                </span>
            </div>
        </div>
        <div class="relative h-52">
            <canvas id="txChart"></canvas>
        </div>
    </div>

    {{-- 7-day summary --}}
    <div class="glass-card p-5 flex flex-col gap-3">
        <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Ringkasan 7 Hari</h2>
        @php
            $totalPaid7 = collect($chartData)->sum('count');
            $totalNom7  = collect($chartData)->sum('total');
            $maxDay     = collect($chartData)->sortByDesc('count')->first();
        @endphp
        <div class="flex-1 flex flex-col gap-2.5">
            <div class="flex items-center justify-between px-3.5 py-3 rounded-xl bg-slate-50 dark:bg-white/[0.04] border border-slate-100 dark:border-white/[0.06]">
                <span class="text-xs text-slate-500 dark:text-slate-400">Total Paid</span>
                <span class="font-mono text-sm font-bold text-slate-800 dark:text-white">{{ $totalPaid7 }} trx</span>
            </div>
            <div class="flex items-center justify-between px-3.5 py-3 rounded-xl bg-slate-50 dark:bg-white/[0.04] border border-slate-100 dark:border-white/[0.06]">
                <span class="text-xs text-slate-500 dark:text-slate-400">Total Nominal</span>
                <span class="font-mono text-sm font-bold text-slate-800 dark:text-white">Rp {{ number_format($totalNom7, 0, ',', '.') }}</span>
            </div>
            @if($maxDay)
            <div class="flex items-center justify-between px-3.5 py-3 rounded-xl bg-slate-50 dark:bg-white/[0.04] border border-slate-100 dark:border-white/[0.06]">
                <span class="text-xs text-slate-500 dark:text-slate-400">Hari Terbaik</span>
                <span class="font-mono text-sm font-bold text-slate-800 dark:text-white">{{ $maxDay['label'] }} <span class="text-slate-400 dark:text-slate-500">({{ $maxDay['count'] }})</span></span>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Top Books + Recent Transactions --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-5">

    {{-- Top Reads --}}
    <div class="flat-card">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between">
            <div>
                <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Top Buku — Reads</h2>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Paling banyak dibaca hari ini</p>
            </div>
            <span class="text-[11px] font-medium text-teal-500 dark:text-teal-400 bg-teal-50 dark:bg-teal-500/10 px-2.5 py-1 rounded-full">Top 10</span>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">#</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Judul</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Reads</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topReads as $i => $book)
                <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-5 py-3 w-10">
                        <span class="font-mono text-xs font-bold {{ $i === 0 ? 'text-amber-500' : ($i === 1 ? 'text-slate-400' : ($i === 2 ? 'text-orange-400' : 'text-slate-300 dark:text-slate-600')) }}">{{ $i + 1 }}</span>
                    </td>
                    <td class="px-5 py-3 text-xs font-medium text-slate-700 dark:text-slate-300 max-w-[1px] w-full truncate">{{ $book->title }}</td>
                    <td class="px-5 py-3 text-right font-mono text-xs font-semibold text-teal-600 dark:text-teal-400 whitespace-nowrap">{{ number_format($book->reads_today) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-5 py-10 text-center text-sm text-slate-400 dark:text-slate-500">Belum ada reads hari ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Recent Transactions --}}
    <div class="flat-card">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between">
            <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Transaksi Terbaru</h2>
            <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-white/[0.06] px-2.5 py-1 rounded-full">{{ number_format($txTotal) }} hari ini</span>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full min-w-max text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">User</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Nominal</th>
                    <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions as $tx)
                <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0">
                                {{ strtoupper(substr($tx->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-200 truncate">{{ $tx->name ?? 'Unknown' }}</div>
                                <div class="text-[11px] text-slate-400 dark:text-slate-500 font-mono truncate">{{ $tx->email ?? '-' }}</div>
                                @if($tx->phone_number ?? null)<div class="text-[11px] text-slate-400 dark:text-slate-500 font-mono truncate">{{ $tx->phone_number }}</div>@endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-right font-mono text-xs font-semibold text-slate-700 dark:text-slate-200 whitespace-nowrap">
                        Rp {{ number_format((float)$tx->total_amount, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="badge badge-{{ $tx->status }}">{{ ucfirst($tx->status) }}</span>
                    </td>
                    <td class="px-5 py-3 text-right font-mono text-[11px] text-slate-400 dark:text-slate-500 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($tx->display_time)->format('d/m H:i') }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-slate-400 dark:text-slate-500">Belum ada transaksi</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>{{-- /overflow-x-auto --}}
        @include('admin.partials.pagination', [
            'page'       => $txPage,
            'totalPages' => $txTotalPages,
            'total'      => $txTotal,
            'perPage'    => $txPerPage,
            'param'      => 'tx_page',
        ])
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const chartData = @json($chartData);
const dark = document.documentElement.classList.contains('dark');
const gridColor  = dark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
const tickColor  = dark ? '#475569' : '#94a3b8';
const ttBg       = dark ? '#1e293b' : '#ffffff';
const ttBorder   = dark ? '#334155' : '#e2e8f0';
const ttTitle    = dark ? '#f1f5f9' : '#1e293b';
const ttBody     = dark ? '#94a3b8' : '#64748b';

new Chart(document.getElementById('txChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: chartData.map(d => d.label),
        datasets: [
            {
                label: 'Transaksi',
                data: chartData.map(d => d.count),
                backgroundColor: dark ? 'rgba(99,130,255,0.25)' : 'rgba(59,130,246,0.15)',
                borderColor: dark ? '#818cf8' : '#3b82f6',
                borderWidth: 1.5,
                borderRadius: 6,
                borderSkipped: false,
                yAxisID: 'y',
            },
            {
                label: 'Nominal',
                data: chartData.map(d => d.total / 1000),
                type: 'line',
                borderColor: dark ? '#2dd4bf' : '#0d9488',
                backgroundColor: dark ? 'rgba(45,212,191,0.06)' : 'rgba(13,148,136,0.06)',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: dark ? '#2dd4bf' : '#0d9488',
                fill: true,
                tension: 0.4,
                yAxisID: 'y1',
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: ttBg,
                titleColor: ttTitle,
                bodyColor: ttBody,
                borderColor: ttBorder,
                borderWidth: 1,
                padding: 10,
                cornerRadius: 10,
                callbacks: {
                    label: ctx => ctx.datasetIndex === 0
                        ? `  ${ctx.raw} transaksi`
                        : `  Rp ${(ctx.raw * 1000).toLocaleString('id-ID')}`
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: tickColor, font: { size: 11, family: 'Fira Code' } },
                border: { display: false }
            },
            y: {
                position: 'left',
                grid: { color: gridColor },
                ticks: { color: tickColor, font: { size: 10 }, stepSize: 1 },
                border: { display: false }
            },
            y1: {
                position: 'right',
                grid: { drawOnChartArea: false },
                ticks: { color: tickColor, font: { size: 10 } },
                border: { display: false }
            }
        }
    }
});
</script>
@endpush
