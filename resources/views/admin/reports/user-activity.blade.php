@extends('layouts.admin')
@section('title', 'Aktivitas User')
@section('page-title', 'Aktivitas User — Register, Login & Akses')

@section('content')

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="glass-card p-5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Total User</p>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($kpi->total_users) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">+{{ number_format($kpi->new_30d) }} bulan ini</p>
    </div>
    <div class="glass-card p-5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Register Hari Ini</p>
        <p class="font-mono text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($kpi->new_today) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">+{{ number_format($kpi->new_7d) }} minggu ini</p>
    </div>
    <div class="glass-card p-5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Login Hari Ini</p>
        <p class="font-mono text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($kpi->logins_today) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">{{ number_format($kpi->logins_7d) }} unik 7 hari</p>
    </div>
    <div class="glass-card p-5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Baca Hari Ini</p>
        <p class="font-mono text-2xl font-bold text-violet-600 dark:text-violet-400">{{ number_format($kpi->active_today) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">{{ number_format($kpi->never_logged_in) }} belum pernah login</p>
    </div>
</div>

{{-- Charts Row --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-5">

    {{-- Trend 30 hari --}}
    <div class="glass-card p-5 xl:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Registrasi & Login — 30 Hari</h2>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">User baru dan aktivitas login harian</p>
            </div>
        </div>
        <div class="relative h-48"><canvas id="trendChart"></canvas></div>
    </div>

    {{-- Aktif per jam hari ini --}}
    <div class="glass-card p-5">
        <div class="mb-4">
            <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Aktif per Jam — Hari Ini</h2>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Berdasarkan aktivitas baca chapter</p>
        </div>
        <div class="relative h-48"><canvas id="hourChart"></canvas></div>
    </div>
</div>

{{-- User Table --}}
<div class="flat-card">
    {{-- Header & Filter --}}
    <div class="px-5 py-4 border-b border-slate-100 dark:border-white/[0.06] flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Daftar User</h2>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">{{ number_format($total) }} user</p>
        </div>
        {{-- Filter tabs --}}
        <div class="flex items-center gap-1 p-1 rounded-xl bg-slate-100 dark:bg-white/[0.04]">
            @foreach(['all' => 'Semua', 'new' => 'Baru (7h)', 'subscribed' => 'Berlangganan', 'never_login' => 'Belum Login'] as $val => $label)
            <a href="{{ request()->fullUrlWithQuery(['filter' => $val, 'page' => 1]) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all {{ $filter === $val ? 'bg-white dark:bg-white/10 text-slate-800 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
    <table class="w-full min-w-max text-sm">
        <thead>
            <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">User</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Register</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Login Terakhir</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Baca Terakhir</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Login</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Plan Aktif</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Expires</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
            @php
                $lastActivity = $u->last_read_at ?? $u->last_login_at;
                $daysSince    = $lastActivity ? \Carbon\Carbon::parse($lastActivity)->diffInDays(now()) : null;
            @endphp
            <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-[11px] font-bold flex-shrink-0">
                            {{ strtoupper(substr($u->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-200">{{ $u->name }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">{{ $u->email }}</div>
                            @if($u->phone_number)<div class="text-[11px] text-slate-400 font-mono">{{ $u->phone_number }}</div>@endif
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3 text-center">
                    @if($u->has_membership)
                        <span class="badge badge-paid">Member</span>
                    @elseif($u->last_login_at)
                        <span class="badge badge-expired">User Gratis</span>
                    @else
                        <span class="text-[11px] text-slate-300 dark:text-slate-600 font-mono">—</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-center font-mono text-[11px] text-slate-500 whitespace-nowrap">
                    {{ \Carbon\Carbon::parse($u->registered_at)->format('d M Y') }}
                    @if(\Carbon\Carbon::parse($u->registered_at)->diffInDays(now()) <= 7)
                        <div class="text-[10px] text-emerald-500 font-semibold">Baru</div>
                    @endif
                </td>
                <td class="px-5 py-3 text-center font-mono text-[11px] whitespace-nowrap {{ !$u->last_login_at ? 'text-red-400' : 'text-slate-400' }}">
                    @if($u->last_login_at)
                        {{ \Carbon\Carbon::parse($u->last_login_at)->format('d M Y') }}
                        <div class="text-[10px] text-slate-300 dark:text-slate-600">{{ \Carbon\Carbon::parse($u->last_login_at)->format('H:i') }}</div>
                    @else
                        <span class="text-red-400">Belum login</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-center font-mono text-[11px] whitespace-nowrap">
                    @if($u->last_read_at)
                        @php
                            $lr      = \Carbon\Carbon::parse($u->last_read_at);
                            $diffMin = (int) $lr->diffInMinutes(now());
                            $diffHr  = (int) $lr->diffInHours(now());
                            $diffDay = (int) $lr->diffInDays(now());
                            if ($diffMin < 1)       { $ago = 'Baru saja';            $cls = 'text-emerald-600 dark:text-emerald-400 font-semibold'; }
                            elseif ($diffMin < 60)  { $ago = $diffMin . ' mnt lalu'; $cls = 'text-emerald-600 dark:text-emerald-400 font-semibold'; }
                            elseif ($diffHr < 24)   { $ago = $diffHr . ' jam lalu';  $cls = 'text-blue-500 dark:text-blue-400 font-semibold'; }
                            elseif ($diffDay == 1)  { $ago = 'Kemarin';              $cls = 'text-blue-400'; }
                            elseif ($diffDay <= 7)  { $ago = $diffDay . ' hari lalu';$cls = 'text-slate-500 dark:text-slate-400'; }
                            else                    { $ago = $diffDay . ' hari lalu';$cls = 'text-slate-400'; }
                        @endphp
                        <span class="{{ $cls }}">{{ $ago }}</span>
                        <div class="text-[10px] text-slate-300 dark:text-slate-600">{{ $lr->format('d M H:i') }}</div>
                    @else
                        <span class="text-slate-300 dark:text-slate-600">—</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-right font-mono text-xs font-semibold {{ $u->login_count > 0 ? 'text-slate-700 dark:text-slate-200' : 'text-slate-300 dark:text-slate-600' }}">
                    {{ $u->login_count > 0 ? $u->login_count . '×' : '—' }}
                </td>
                <td class="px-5 py-3 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap">
                    {{ $u->active_plan ?? '—' }}
                </td>
                <td class="px-5 py-3 text-center font-mono text-[11px] whitespace-nowrap">
                    @if($u->membership_expires_at)
                        @php $daysLeft = \Carbon\Carbon::parse($u->membership_expires_at)->diffInDays(now(), false); @endphp
                        <span class="{{ $daysLeft > 0 ? 'text-red-500' : 'text-emerald-600 dark:text-emerald-400' }}">
                            {{ \Carbon\Carbon::parse($u->membership_expires_at)->format('d M Y') }}
                        </span>
                        @if($daysLeft <= 0)
                            <div class="text-[10px] text-emerald-500">{{ abs((int)$daysLeft) }}h lagi</div>
                        @elseif($daysLeft <= 7)
                            <div class="text-[10px] text-red-400">{{ (int)$daysLeft }}h lalu</div>
                        @endif
                    @else
                        <span class="text-slate-300 dark:text-slate-600">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-5 py-12 text-center text-sm text-slate-400">Tidak ada data user.</td></tr>
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

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const dark      = document.documentElement.classList.contains('dark');
const gridColor = dark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
const tickColor = dark ? '#475569' : '#94a3b8';
const ttDefaults = {
    backgroundColor: dark ? '#1e293b' : '#fff',
    titleColor: dark ? '#f1f5f9' : '#1e293b',
    bodyColor:  dark ? '#94a3b8' : '#64748b',
    borderColor: dark ? '#334155' : '#e2e8f0',
    borderWidth: 1, padding: 10, cornerRadius: 10
};

// ── Trend Chart ──────────────────────────────────────────────────────────────
const trendRaw = @json($trendData);
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: trendRaw.map(d => {
            const dt = new Date(d.date);
            return dt.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        }),
        datasets: [
            {
                label: 'Registrasi Baru',
                data: trendRaw.map(d => d.reg),
                borderColor: dark ? '#34d399' : '#10b981',
                backgroundColor: dark ? 'rgba(52,211,153,0.12)' : 'rgba(16,185,129,0.08)',
                borderWidth: 2, fill: true, tension: 0.4, pointRadius: 2, pointHoverRadius: 5,
            },
            {
                label: 'Login',
                data: trendRaw.map(d => d.logins),
                borderColor: dark ? '#60a5fa' : '#3b82f6',
                backgroundColor: dark ? 'rgba(96,165,250,0.08)' : 'rgba(59,130,246,0.06)',
                borderWidth: 2, fill: true, tension: 0.4, pointRadius: 2, pointHoverRadius: 5,
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: true, labels: { color: tickColor, font: { size: 11 }, boxWidth: 12 } },
            tooltip: { ...ttDefaults }
        },
        scales: {
            x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 10 }, maxTicksLimit: 10 }, border: { display: false } },
            y: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 10 }, stepSize: 1 }, border: { display: false }, beginAtZero: true }
        }
    }
});

// ── Active by Hour Chart ─────────────────────────────────────────────────────
const hourRaw = @json($activeByHour);
const hourLabels = Array.from({length: 24}, (_, i) => i + ':00');
const hourData   = Array(24).fill(0);
hourRaw.forEach(d => { hourData[d.hr] = d.cnt; });

new Chart(document.getElementById('hourChart'), {
    type: 'bar',
    data: {
        labels: hourLabels,
        datasets: [{
            label: 'User Aktif',
            data: hourData,
            backgroundColor: dark ? 'rgba(139,92,246,0.5)' : 'rgba(124,58,237,0.35)',
            borderRadius: 4, borderSkipped: false,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: { ...ttDefaults, callbacks: { label: ctx => ` ${ctx.raw} user aktif` } }
        },
        scales: {
            x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 9 }, maxTicksLimit: 8 }, border: { display: false } },
            y: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 10 }, stepSize: 1 }, border: { display: false }, beginAtZero: true }
        }
    }
});
</script>
@endpush
