@extends('layouts.admin')
@section('title', 'Revenue Harian')
@section('page-title', 'Revenue Harian')

@section('content')

{{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.reports.revenue-daily') }}" class="flat-card px-5 py-3 mb-5 flex flex-wrap items-center gap-3">
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

{{-- KPI Summary Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-5">

    <div class="glass-card p-4">
        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="font-mono text-lg font-bold text-slate-900 dark:text-white leading-tight">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Total Revenue</p>
    </div>

    <div class="glass-card p-4">
        <div class="w-9 h-9 rounded-xl bg-blue-500/10 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <p class="font-mono text-lg font-bold text-slate-900 dark:text-white leading-tight">{{ number_format($totalTrx) }}</p>
        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Total Transaksi</p>
    </div>

    <div class="glass-card p-4">
        <div class="w-9 h-9 rounded-xl bg-violet-500/10 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
            </svg>
        </div>
        <p class="font-mono text-lg font-bold text-slate-900 dark:text-white leading-tight">Rp {{ number_format($totalMarketingCost, 0, ',', '.') }}</p>
        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Biaya Marketing</p>
    </div>

    <div class="glass-card p-4">
        <div class="w-9 h-9 rounded-xl {{ $totalProfit >= 0 ? 'bg-emerald-500/10' : 'bg-red-500/10' }} flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 {{ $totalProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
        </div>
        <p class="font-mono text-lg font-bold {{ $totalProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }} leading-tight">
            Rp {{ number_format($totalProfit, 0, ',', '.') }}
        </p>
        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Total Profit</p>
    </div>

    <div class="glass-card p-4">
        <div class="w-9 h-9 rounded-xl bg-amber-500/10 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
        </div>
        <p class="font-mono text-sm font-bold text-amber-600 dark:text-amber-400 leading-tight">
            @if($bestDay && $bestDay['revenue'] > 0)
                Tgl {{ $bestDay['day'] }}
                <span class="text-xs block text-slate-500 dark:text-slate-400 font-normal mt-0.5">Rp {{ number_format($bestDay['revenue'], 0, ',', '.') }}</span>
            @else
                —
            @endif
        </p>
        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Hari Terbaik</p>
    </div>

    <div class="glass-card p-4">
        <div class="w-9 h-9 rounded-xl bg-sky-500/10 flex items-center justify-center mb-3">
            <svg class="w-4.5 h-4.5 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <p class="font-mono text-lg font-bold text-slate-900 dark:text-white leading-tight">Rp {{ number_format($avgRevenue, 0, ',', '.') }}</p>
        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Rata-rata/Hari <span class="text-slate-300 dark:text-slate-600">({{ $activeDays }}h aktif)</span></p>
    </div>

</div>

{{-- Table --}}
<div class="flat-card mb-5">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between">
        <h3 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Detail Harian</h3>
        <p class="text-xs text-slate-400">Klik kolom <span class="font-medium text-violet-500">Biaya Marketing</span> untuk edit inline</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="revenue-table">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider w-20">Tanggal</th>
                    <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Transaksi</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Pendapatan</th>
                    <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Growth</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold text-violet-400 uppercase tracking-wider">Biaya Marketing</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Profit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($days as $day)
                @php
                    $isToday = $day['date'] === now()->toDateString();
                    $rowBg = $isToday ? 'bg-blue-50/50 dark:bg-blue-500/[0.04]' : '';
                @endphp
                <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors {{ $rowBg }}"
                    data-date="{{ $day['date'] }}">

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

                    {{-- Transaksi --}}
                    <td class="px-5 py-2.5 text-center">
                        @if($day['trx_count'] > 0)
                            <span class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $day['trx_count'] }}</span>
                        @else
                            <span class="text-slate-300 dark:text-slate-700 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Total Pendapatan --}}
                    <td class="px-5 py-2.5 text-right">
                        @if($day['revenue'] > 0)
                            <span class="font-mono text-xs font-semibold text-slate-800 dark:text-white">Rp {{ number_format($day['revenue'], 0, ',', '.') }}</span>
                        @else
                            <span class="text-slate-300 dark:text-slate-700 text-xs font-mono">Rp 0</span>
                        @endif
                    </td>

                    {{-- Growth --}}
                    <td class="px-5 py-2.5 text-center">
                        @if($day['growth'] !== null)
                            @if($day['growth'] > 0)
                                <span class="inline-flex items-center gap-0.5 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 rounded-full">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                    +{{ $day['growth'] }}%
                                </span>
                            @elseif($day['growth'] < 0)
                                <span class="inline-flex items-center gap-0.5 text-[11px] font-semibold text-red-500 bg-red-50 dark:bg-red-500/10 px-2 py-0.5 rounded-full">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                    {{ $day['growth'] }}%
                                </span>
                            @else
                                <span class="text-[11px] text-slate-400">0%</span>
                            @endif
                        @elseif($day['revenue'] > 0)
                            <span class="text-[11px] text-slate-400 italic">baru</span>
                        @else
                            <span class="text-slate-300 dark:text-slate-700 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Biaya Marketing (inline edit) --}}
                    <td class="px-5 py-2.5 text-right" data-role="cost-cell">
                        <div class="inline-flex items-center justify-end gap-1 group/cost cursor-pointer" onclick="startEdit(this, '{{ $day['date'] }}')">
                            <span class="cost-display font-mono text-xs text-violet-600 dark:text-violet-400">
                                Rp {{ number_format($day['marketing_cost'], 0, ',', '.') }}
                            </span>
                            <svg class="w-3 h-3 text-slate-300 dark:text-slate-600 group-hover/cost:text-violet-500 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                            <input type="number" min="0"
                                class="cost-input hidden w-32 h-7 px-2 text-xs font-mono text-right rounded-lg border border-violet-400 dark:border-violet-500/60 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500/40"
                                value="{{ $day['marketing_cost'] }}"
                                data-original="{{ $day['marketing_cost'] }}"
                                onblur="saveEdit(this, '{{ $day['date'] }}')"
                                onkeydown="handleKey(event, this, '{{ $day['date'] }}')">
                        </div>
                    </td>

                    {{-- Profit --}}
                    <td class="px-5 py-2.5 text-right" data-role="profit-cell">
                        @php $profitClass = $day['profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500'; @endphp
                        <span class="profit-display font-mono text-xs font-semibold {{ $profitClass }}">
                            Rp {{ number_format($day['profit'], 0, ',', '.') }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            {{-- Totals row --}}
            <tfoot>
                <tr class="border-t-2 border-slate-200 dark:border-white/[0.10] bg-slate-50 dark:bg-white/[0.03]">
                    <td class="px-5 py-3 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wide" colspan="2">Total Bulan Ini</td>
                    <td class="px-5 py-3 text-right font-mono text-sm font-bold text-slate-900 dark:text-white">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                    <td class="px-5 py-3"></td>
                    <td class="px-5 py-3 text-right font-mono text-sm font-bold text-violet-600 dark:text-violet-400">Rp {{ number_format($totalMarketingCost, 0, ',', '.') }}</td>
                    <td class="px-5 py-3 text-right font-mono text-sm font-bold {{ $totalProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">
                        Rp {{ number_format($totalProfit, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Chart --}}
<div class="flat-card p-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Grafik Revenue Harian</h3>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-400">
                <span class="w-3 h-0.5 bg-emerald-500 rounded-full inline-block"></span> Revenue
            </span>
            <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-400">
                <span class="w-3 h-0.5 bg-violet-500 rounded-full inline-block"></span> Biaya Marketing
            </span>
            <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-400">
                <span class="w-3 h-0.5 bg-blue-500 border-dashed border-t border-blue-500 inline-block"></span> Profit
            </span>
        </div>
    </div>
    <div class="relative" style="height:280px">
        <canvas id="revenueChart"></canvas>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Chart ──────────────────────────────────────────────────────────────────
const labels  = @json(array_column($days, 'day'));
const revenues = @json(array_column($days, 'revenue'));
const costs   = @json(array_column($days, 'marketing_cost'));
const profits  = @json(array_column($days, 'profit'));

const isDark = document.documentElement.classList.contains('dark');
const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
const textColor = isDark ? '#94a3b8' : '#94a3b8';

const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Revenue',
                data: revenues,
                backgroundColor: 'rgba(16,185,129,0.15)',
                borderColor: 'rgba(16,185,129,0.8)',
                borderWidth: 1.5,
                borderRadius: 4,
                order: 2,
            },
            {
                label: 'Biaya Marketing',
                data: costs,
                backgroundColor: 'rgba(139,92,246,0.15)',
                borderColor: 'rgba(139,92,246,0.8)',
                borderWidth: 1.5,
                borderRadius: 4,
                order: 3,
            },
            {
                label: 'Profit',
                data: profits,
                type: 'line',
                borderColor: 'rgba(59,130,246,0.9)',
                backgroundColor: 'rgba(59,130,246,0.05)',
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointBackgroundColor: 'rgba(59,130,246,1)',
                tension: 0.35,
                fill: false,
                order: 1,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: isDark ? '#1e293b' : '#fff',
                borderColor: isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)',
                borderWidth: 1,
                titleColor: isDark ? '#e2e8f0' : '#1e293b',
                bodyColor: isDark ? '#94a3b8' : '#64748b',
                padding: 10,
                callbacks: {
                    title: (items) => 'Tanggal ' + items[0].label,
                    label: (item) => ' ' + item.dataset.label + ': Rp ' + item.raw.toLocaleString('id-ID'),
                },
            },
        },
        scales: {
            x: {
                grid: { color: gridColor },
                ticks: { color: textColor, font: { size: 11, family: 'monospace' } },
            },
            y: {
                grid: { color: gridColor },
                ticks: {
                    color: textColor,
                    font: { size: 11 },
                    callback: (v) => 'Rp ' + (v >= 1000000 ? (v/1000000).toFixed(1)+'jt' : (v >= 1000 ? (v/1000).toFixed(0)+'rb' : v)),
                },
            },
        },
    },
});

// ── Inline edit helpers ────────────────────────────────────────────────────
function startEdit(wrapper, date) {
    const display = wrapper.querySelector('.cost-display');
    const input   = wrapper.querySelector('.cost-input');
    display.classList.add('hidden');
    input.classList.remove('hidden');
    input.focus();
    input.select();
}

function handleKey(e, input, date) {
    if (e.key === 'Enter') { input.blur(); }
    if (e.key === 'Escape') {
        input.value = input.dataset.original;
        cancelEdit(input);
    }
}

function cancelEdit(input) {
    const wrapper = input.closest('[onclick]') ?? input.parentElement;
    const display = wrapper.querySelector('.cost-display');
    input.classList.add('hidden');
    display.classList.remove('hidden');
}

async function saveEdit(input, date) {
    const cost = parseInt(input.value) || 0;
    const original = parseInt(input.dataset.original) || 0;

    const wrapper = input.parentElement;
    const display = wrapper.querySelector('.cost-display');
    const row     = input.closest('tr');
    const profitEl = row.querySelector('[data-role="profit-cell"] .profit-display');

    if (cost === original) {
        cancelEdit(input);
        return;
    }

    // Optimistic update display
    display.textContent = 'Rp ' + cost.toLocaleString('id-ID');
    input.dataset.original = cost;
    cancelEdit(input);

    try {
        const res = await fetch('{{ route("admin.reports.revenue-daily.cost") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ date, cost }),
        });

        const data = await res.json();

        // Update profit cell
        if (profitEl) {
            const profit = data.profit;
            profitEl.textContent = 'Rp ' + profit.toLocaleString('id-ID');
            profitEl.className = 'profit-display font-mono text-xs font-semibold ' +
                (profit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500');
        }

        // Flash success
        display.classList.add('text-emerald-500');
        setTimeout(() => display.classList.remove('text-emerald-500'), 1500);

    } catch (err) {
        // Revert on error
        display.textContent = 'Rp ' + original.toLocaleString('id-ID');
        input.dataset.original = original;
        display.classList.add('text-red-500');
        setTimeout(() => display.classList.remove('text-red-500'), 1500);
    }
}
</script>
@endpush
