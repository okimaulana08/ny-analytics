@extends('layouts.admin')
@section('title', 'Aktivitas User Harian')
@section('page-title', 'Aktivitas User Harian')

@section('content')

{{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.reports.user-daily') }}" class="flat-card px-5 py-3 mb-5 flex flex-wrap items-center gap-3">
    <div class="flex items-center gap-2">
        <label class="text-xs font-medium text-slate-500 dark:text-slate-400">Bulan</label>
        <select name="month" onchange="this.form.submit()"
            class="h-9 px-3 text-sm rounded-xl bg-slate-50 dark:bg-slate-800 dark:[color-scheme:dark] border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/40 cursor-pointer">
            @foreach(range(1,12) as $m)
            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                {{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="flex items-center gap-2">
        <label class="text-xs font-medium text-slate-500 dark:text-slate-400">Tahun</label>
        <select name="year" onchange="this.form.submit()"
            class="h-9 px-3 text-sm rounded-xl bg-slate-50 dark:bg-slate-800 dark:[color-scheme:dark] border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/40 cursor-pointer">
            @foreach($years as $y)
            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
    </div>
    <span class="ml-auto text-xs text-slate-400 font-mono">
        {{ Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}
    </span>
</form>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-5">

    <div class="glass-card p-4">
        <div class="w-9 h-9 rounded-xl bg-blue-500/10 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
        </div>
        <p class="font-mono text-lg font-bold text-slate-900 dark:text-white leading-tight">{{ number_format($totalAkses) }}</p>
        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Total Akses</p>
    </div>

    <div class="glass-card p-4">
        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
        </div>
        <p class="font-mono text-lg font-bold text-slate-900 dark:text-white leading-tight">{{ number_format($totalNewUser) }}</p>
        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Total New User</p>
    </div>

    <div class="glass-card p-4">
        <div class="w-9 h-9 rounded-xl bg-violet-500/10 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <p class="font-mono text-lg font-bold text-slate-900 dark:text-white leading-tight">{{ number_format($totalRead) }}</p>
        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Total Read</p>
    </div>

    <div class="glass-card p-4">
        <div class="w-9 h-9 rounded-xl bg-sky-500/10 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <p class="font-mono text-lg font-bold text-slate-900 dark:text-white leading-tight">{{ number_format($avgAkses) }}</p>
        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Rata-rata Akses/Hari <span class="text-slate-300 dark:text-slate-600">({{ $activeDays }}h)</span></p>
    </div>

    <div class="glass-card p-4">
        <div class="w-9 h-9 rounded-xl bg-amber-500/10 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
        </div>
        @if($peakAksesDay && $peakAksesDay['akses'] > 0)
        <p class="font-mono text-sm font-bold text-amber-600 dark:text-amber-400 leading-tight">
            Tgl {{ $peakAksesDay['day'] }}
            <span class="text-xs block text-slate-500 dark:text-slate-400 font-normal mt-0.5">{{ number_format($peakAksesDay['akses']) }} akses</span>
        </p>
        @else
        <p class="font-mono text-lg font-bold text-slate-300 dark:text-slate-700 leading-tight">—</p>
        @endif
        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Hari Tersibuk</p>
    </div>

    <div class="glass-card p-4">
        <div class="w-9 h-9 rounded-xl bg-teal-500/10 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
        </div>
        @if($peakNewUserDay && $peakNewUserDay['new_user'] > 0)
        <p class="font-mono text-sm font-bold text-teal-600 dark:text-teal-400 leading-tight">
            Tgl {{ $peakNewUserDay['day'] }}
            <span class="text-xs block text-slate-500 dark:text-slate-400 font-normal mt-0.5">{{ number_format($peakNewUserDay['new_user']) }} new user</span>
        </p>
        @else
        <p class="font-mono text-lg font-bold text-slate-300 dark:text-slate-700 leading-tight">—</p>
        @endif
        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Puncak New User</p>
    </div>

</div>

{{-- Table --}}
<div class="flat-card mb-5">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-white/[0.06]">
        <h3 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Detail Harian</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-20">Tanggal</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold text-blue-400 uppercase tracking-wider">User Akses</th>
                    <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Growth Akses</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold text-emerald-400 uppercase tracking-wider">New User</th>
                    <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Growth New User</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Read</th>
                </tr>
            </thead>
            <tbody>
                @foreach($days as $day)
                @php $isToday = $day['date'] === now()->toDateString(); @endphp
                <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors {{ $isToday ? 'bg-blue-50/50 dark:bg-blue-500/[0.04]' : '' }}">

                    {{-- Tanggal --}}
                    <td class="px-5 py-2.5">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg {{ $isToday ? 'bg-blue-600 text-white' : ($day['has_data'] ? 'bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-slate-200' : 'bg-transparent text-slate-300 dark:text-slate-700') }} flex items-center justify-center font-mono text-xs font-bold flex-shrink-0">
                                {{ str_pad($day['day'], 2, '0', STR_PAD_LEFT) }}
                            </div>
                            <span class="text-[11px] text-slate-400 hidden sm:block">
                                {{ Carbon\Carbon::parse($day['date'])->translatedFormat('D') }}
                            </span>
                        </div>
                    </td>

                    {{-- User Akses --}}
                    <td class="px-5 py-2.5 text-right">
                        @if($day['akses'] > 0)
                            <span class="font-mono text-xs font-semibold text-blue-700 dark:text-blue-300">{{ number_format($day['akses']) }}</span>
                        @else
                            <span class="text-slate-300 dark:text-slate-700 text-xs font-mono">—</span>
                        @endif
                    </td>

                    {{-- Growth Akses --}}
                    <td class="px-5 py-2.5 text-center">
                        @include('admin.partials.growth-badge', ['growth' => $day['akses_growth'], 'hasData' => $day['akses'] > 0])
                    </td>

                    {{-- New User --}}
                    <td class="px-5 py-2.5 text-right">
                        @if($day['new_user'] > 0)
                            <span class="font-mono text-xs font-semibold text-emerald-700 dark:text-emerald-300">{{ number_format($day['new_user']) }}</span>
                        @else
                            <span class="text-slate-300 dark:text-slate-700 text-xs font-mono">—</span>
                        @endif
                    </td>

                    {{-- Growth New User --}}
                    <td class="px-5 py-2.5 text-center">
                        @include('admin.partials.growth-badge', ['growth' => $day['new_user_growth'], 'hasData' => $day['new_user'] > 0])
                    </td>

                    {{-- Read --}}
                    <td class="px-5 py-2.5 text-right">
                        @if($day['read'] > 0)
                            <span class="font-mono text-xs text-slate-600 dark:text-slate-300">{{ number_format($day['read']) }}</span>
                        @else
                            <span class="text-slate-300 dark:text-slate-700 text-xs">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-slate-200 dark:border-white/[0.10] bg-slate-50 dark:bg-white/[0.03]">
                    <td class="px-5 py-3 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wide">Total Bulan Ini</td>
                    <td class="px-5 py-3 text-right font-mono text-sm font-bold text-blue-700 dark:text-blue-300">{{ number_format($totalAkses) }}</td>
                    <td class="px-5 py-3"></td>
                    <td class="px-5 py-3 text-right font-mono text-sm font-bold text-emerald-700 dark:text-emerald-300">{{ number_format($totalNewUser) }}</td>
                    <td class="px-5 py-3"></td>
                    <td class="px-5 py-3 text-right font-mono text-sm font-bold text-slate-700 dark:text-slate-200">{{ number_format($totalRead) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Chart --}}
<div class="flat-card p-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Grafik Aktivitas Harian</h3>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-400">
                <span class="w-3 h-2 bg-blue-500 rounded-sm inline-block opacity-70"></span> User Akses
            </span>
            <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-400">
                <span class="w-3 h-0.5 bg-emerald-500 rounded-full inline-block"></span> New User
            </span>
            <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-400">
                <span class="w-3 h-0.5 bg-violet-400 rounded-full inline-block"></span> Read
            </span>
        </div>
    </div>
    <div class="relative" style="height:280px">
        <canvas id="userDailyChart"></canvas>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const labels   = @json(array_column($days, 'day'));
const aksesArr = @json(array_column($days, 'akses'));
const newUserArr = @json(array_column($days, 'new_user'));
const readArr  = @json(array_column($days, 'read'));

const isDark = document.documentElement.classList.contains('dark');
const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
const textColor = isDark ? '#94a3b8' : '#94a3b8';

const tt = {
    backgroundColor: isDark ? '#1e293b' : '#fff',
    borderColor: isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)',
    borderWidth: 1,
    titleColor: isDark ? '#e2e8f0' : '#1e293b',
    bodyColor: isDark ? '#94a3b8' : '#64748b',
    padding: 10,
    callbacks: {
        title: (items) => 'Tanggal ' + items[0].label,
        label: (item) => ' ' + item.dataset.label + ': ' + Number(item.raw).toLocaleString('id-ID'),
    },
};

new Chart(document.getElementById('userDailyChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            {
                label: 'User Akses',
                data: aksesArr,
                backgroundColor: 'rgba(59,130,246,0.2)',
                borderColor: 'rgba(59,130,246,0.8)',
                borderWidth: 1.5,
                borderRadius: 4,
                order: 3,
                yAxisID: 'yAkses',
            },
            {
                label: 'Read',
                data: readArr,
                backgroundColor: 'rgba(139,92,246,0.12)',
                borderColor: 'rgba(139,92,246,0.5)',
                borderWidth: 1,
                borderRadius: 4,
                order: 4,
                yAxisID: 'yAkses',
            },
            {
                label: 'New User',
                data: newUserArr,
                type: 'line',
                borderColor: 'rgba(16,185,129,0.9)',
                backgroundColor: 'rgba(16,185,129,0.06)',
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointBackgroundColor: 'rgba(16,185,129,1)',
                tension: 0.35,
                fill: false,
                order: 1,
                yAxisID: 'yNewUser',
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: tt,
        },
        scales: {
            x: {
                grid: { color: gridColor },
                ticks: { color: textColor, font: { size: 11, family: 'monospace' } },
            },
            yAkses: {
                position: 'left',
                grid: { color: gridColor },
                ticks: {
                    color: textColor,
                    font: { size: 11 },
                    callback: (v) => v >= 1000 ? (v / 1000).toFixed(0) + 'rb' : v,
                },
                title: { display: true, text: 'Akses / Read', color: textColor, font: { size: 10 } },
            },
            yNewUser: {
                position: 'right',
                grid: { drawOnChartArea: false },
                ticks: {
                    color: '#10b981',
                    font: { size: 11 },
                },
                title: { display: true, text: 'New User', color: '#10b981', font: { size: 10 } },
            },
        },
    },
});
</script>
@endpush
