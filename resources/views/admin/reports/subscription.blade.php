@extends('layouts.admin')
@section('title', 'Laporan Subscription')
@section('page-title', 'Laporan Subscription')

@section('content')
@php
    $totalUsers    = $kpi->total_users ?? 0;
    $everPaid      = $kpi->ever_paid ?? 0;
    $totalRevenue  = $kpi->total_revenue ?? 0;
    $arpu          = $kpi->arpu ?? 0;
    $renewalCount  = $renewalCount->cnt ?? 0;
    $convRate      = $totalUsers > 0 ? round($everPaid * 100 / $totalUsers, 1) : 0;
    $renewalRate   = $everPaid > 0 ? round($renewalCount * 100 / $everPaid, 1) : 0;
@endphp

{{-- KPI Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">

    <div class="glass-card p-5 cursor-default">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 px-2 py-1 rounded-full">Konversi</span>
        </div>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white">{{ $convRate }}%</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Conversion Rate</p>
        <div class="flex items-center gap-2 mt-0.5">
            <button onclick="openUserModal('paid_users','User Berbayar','Pernah melakukan transaksi paid')" class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">{{ $everPaid }} paid</button>
            <span class="text-slate-300 dark:text-slate-600">·</span>
            <button onclick="openUserModal('free_users','User Gratis','Belum pernah berlangganan')" class="text-sm font-semibold text-slate-400 hover:underline cursor-pointer">{{ $totalUsers - $everPaid }} gratis</button>
        </div>
    </div>

    <div class="glass-card p-5 cursor-default">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Total Revenue</p>
        <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 mt-0.5">ARPU Rp {{ number_format($arpu, 0, ',', '.') }}</p>
    </div>

    <div class="glass-card p-5 cursor-default">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-violet-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-500/10 px-2 py-1 rounded-full">Renewal</span>
        </div>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white">{{ $renewalRate }}%</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Renewal Rate</p>
        <button onclick="openUserModal('renewers','Top Renewers','User yang berlangganan lebih dari 1x')" class="text-sm font-semibold text-violet-600 dark:text-violet-400 hover:underline cursor-pointer mt-0.5">{{ $renewalCount }} user renew</button>
    </div>

    <div class="glass-card p-5 cursor-default">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white">{{ collect($revByPlan)->sum('total_trx') }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Total Transaksi Paid</p>
        <p class="text-sm font-semibold text-amber-600 dark:text-amber-400 mt-0.5">{{ collect($statusBreakdown)->where('status','pending')->sum('cnt') }} pending</p>
    </div>
</div>

{{-- Charts Row --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-5">

    {{-- Daily subscription trend --}}
    <div class="xl:col-span-2 glass-card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Tren Subscription — 30 Hari</h2>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Member baru & renewal per hari</p>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-400">
                <span class="flex items-center gap-1.5"><span class="w-3 h-1.5 rounded-full bg-blue-500 inline-block"></span>Baru</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-1.5 rounded-full bg-violet-500 inline-block"></span>Renewal</span>
            </div>
        </div>
        <div class="relative h-52"><canvas id="trendChart"></canvas></div>
    </div>

    {{-- Revenue by plan --}}
    <div class="glass-card p-5">
        <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white mb-1">Revenue by Plan</h2>
        <p class="text-[11px] text-slate-400 dark:text-slate-500 mb-4">Distribusi transaksi & pendapatan</p>
        <div class="relative h-52"><canvas id="planChart"></canvas></div>
    </div>
</div>

{{-- Top Renewers (full width) --}}
<div class="flat-card mb-5">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between">
        <div>
            <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Top Renewers</h2>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">User dengan transaksi terbanyak — {{ number_format($renewerTotal) }} user</p>
        </div>
        <span class="text-[11px] font-medium text-violet-500 bg-violet-50 dark:bg-violet-500/10 px-2.5 py-1 rounded-full">
            Hal {{ $renewerPage }}/{{ $renewerPages }}
        </span>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full min-w-max text-sm">
        <thead>
            <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">#</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">User</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Trx</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Plan Terakhir</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Terakhir Langganan</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Terakhir Baca</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topRenewers as $i => $r)
            @php
                $isActive = (bool) $r->is_member_active;
                $expiredAt = $r->membership_expired_at ? \Carbon\Carbon::parse($r->membership_expired_at) : null;
                $isExpired = $expiredAt && $expiredAt->isPast();
                // Determine badge
                if ($isActive && !$isExpired) {
                    $badge = ['label' => 'Aktif', 'class' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'];
                } else {
                    $badge = ['label' => 'Expired', 'class' => 'bg-red-50 dark:bg-red-500/10 text-red-500 dark:text-red-400'];
                }
            @endphp
            <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-3 font-mono text-xs text-slate-400">{{ ($renewerPage - 1) * 10 + $i + 1 }}</td>
                <td class="px-5 py-3">
                    <div class="text-xs font-medium text-slate-700 dark:text-slate-200">{{ $r->name }}</div>
                    <div class="text-[11px] text-slate-400 font-mono">{{ $r->email }}</div>
                    @if($r->phone_number)<div class="text-[11px] text-slate-400 font-mono">{{ $r->phone_number }}</div>@endif
                </td>
                <td class="px-5 py-3 text-right font-mono text-xs font-bold text-violet-600 dark:text-violet-400">{{ $r->trx_count }}×</td>
                <td class="px-5 py-3 text-right font-mono text-xs text-slate-700 dark:text-slate-200 whitespace-nowrap">Rp {{ number_format($r->total_spent, 0, ',', '.') }}</td>
                <td class="px-5 py-3 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ $r->latest_plan }}</td>
                <td class="px-5 py-3 text-center">
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                    @if($expiredAt)
                        <div class="text-[10px] text-slate-400 mt-0.5 whitespace-nowrap">{{ $expiredAt->format('d M Y') }}</div>
                    @endif
                </td>
                <td class="px-5 py-3 text-[11px] text-slate-500 dark:text-slate-400 whitespace-nowrap font-mono">
                    {{ $r->last_paid_at ? \Carbon\Carbon::parse($r->last_paid_at)->format('d M Y') : '—' }}
                </td>
                <td class="px-5 py-3 text-[11px] font-mono whitespace-nowrap
                    {{ $r->last_read_at && \Carbon\Carbon::parse($r->last_read_at)->gt(now()->subDays(7)) ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                    {{ $r->last_read_at ? \Carbon\Carbon::parse($r->last_read_at)->diffForHumans() : '—' }}
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada data</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    {{-- Pagination --}}
    @if($renewerPages > 1)
    <div class="px-5 py-3 border-t border-slate-100 dark:border-white/[0.05] flex items-center justify-between">
        <span class="text-xs text-slate-400">
            {{ ($renewerPage - 1) * 10 + 1 }}–{{ min($renewerPage * 10, $renewerTotal) }} dari {{ number_format($renewerTotal) }}
        </span>
        <div class="flex items-center gap-1">
            @if($renewerPage > 1)
            <a href="{{ request()->fullUrlWithQuery(['renewer_page' => $renewerPage - 1]) }}"
                class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.06] transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            @endif
            @foreach(range(max(1, $renewerPage - 2), min($renewerPages, $renewerPage + 2)) as $p)
            <a href="{{ request()->fullUrlWithQuery(['renewer_page' => $p]) }}"
                class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-mono transition-colors
                    {{ $p === $renewerPage ? 'bg-violet-600 text-white' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06]' }}">
                {{ $p }}
            </a>
            @endforeach
            @if($renewerPage < $renewerPages)
            <a href="{{ request()->fullUrlWithQuery(['renewer_page' => $renewerPage + 1]) }}"
                class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.06] transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const dark      = document.documentElement.classList.contains('dark');
const gridColor = dark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
const tickColor = dark ? '#475569' : '#94a3b8';
const ttBg      = dark ? '#1e293b' : '#fff';
const ttBorder  = dark ? '#334155' : '#e2e8f0';
const ttTitle   = dark ? '#f1f5f9' : '#1e293b';
const ttBody    = dark ? '#94a3b8' : '#64748b';

const ttDefaults = { backgroundColor: ttBg, titleColor: ttTitle, bodyColor: ttBody, borderColor: ttBorder, borderWidth: 1, padding: 10, cornerRadius: 10 };

// Trend chart
const trendData = @json($dailyTrend);
new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels: trendData.map(d => d.date),
        datasets: [
            {
                label: 'Member Baru',
                data: trendData.map(d => d.new_member),
                backgroundColor: dark ? 'rgba(99,130,255,0.5)' : 'rgba(59,130,246,0.6)',
                borderRadius: 4, borderSkipped: false,
            },
            {
                label: 'Renewal',
                data: trendData.map(d => d.renewal_member),
                backgroundColor: dark ? 'rgba(167,139,250,0.5)' : 'rgba(139,92,246,0.6)',
                borderRadius: 4, borderSkipped: false,
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { display: false }, tooltip: ttDefaults },
        scales: {
            x: { stacked: true, grid: { display: false }, ticks: { color: tickColor, font: { size: 10, family: 'Fira Code' } }, border: { display: false } },
            y: { stacked: true, grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 10 }, stepSize: 1 }, border: { display: false } }
        }
    }
});

// Plan donut chart
const planData = @json($revByPlan);
new Chart(document.getElementById('planChart'), {
    type: 'doughnut',
    data: {
        labels: planData.map(d => d.plan_name),
        datasets: [{
            data: planData.map(d => d.total_trx),
            backgroundColor: ['rgba(59,130,246,0.8)', 'rgba(139,92,246,0.8)', 'rgba(16,185,129,0.8)', 'rgba(245,158,11,0.8)'],
            borderWidth: 0, hoverOffset: 6,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false, cutout: '65%',
        plugins: {
            legend: { position: 'bottom', labels: { color: tickColor, font: { size: 11, family: 'Fira Code' }, padding: 12, boxWidth: 10 } },
            tooltip: { ...ttDefaults, callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw} trx` } }
        }
    }
});
</script>
@endpush
