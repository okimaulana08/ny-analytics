@extends('layouts.admin')
@section('title', 'Revenue Forecast')
@section('page-title', 'Revenue Forecast — Proyeksi 30 Hari ke Depan')

@push('head-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- KPI Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="glass-card p-5">
        <div class="text-xs text-slate-400 font-medium uppercase tracking-widest mb-2">Pesimistis (−20%)</div>
        <div class="text-2xl font-mono font-bold text-red-500 dark:text-red-400">Rp {{ number_format($pessimistic, 0, ',', '.') }}</div>
        <div class="text-xs text-slate-400 mt-1">Proyeksi konservatif 30 hari</div>
    </div>
    <div class="glass-card p-5 ring-2 ring-blue-500/20">
        <div class="text-xs text-blue-500 font-semibold uppercase tracking-widest mb-2">Realistis (Base)</div>
        <div class="text-2xl font-mono font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($baseForecast, 0, ',', '.') }}</div>
        <div class="text-xs text-slate-400 mt-1">
            Renewal: Rp {{ number_format($renewalForecast, 0, ',', '.') }} &nbsp;·&nbsp;
            Baru: Rp {{ number_format($newSubForecast, 0, ',', '.') }}
        </div>
    </div>
    <div class="glass-card p-5">
        <div class="text-xs text-slate-400 font-medium uppercase tracking-widest mb-2">Optimistis (+20%)</div>
        <div class="text-2xl font-mono font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($optimistic, 0, ',', '.') }}</div>
        <div class="text-xs text-slate-400 mt-1">Proyeksi terbaik 30 hari</div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">

    {{-- Chart --}}
    <div class="xl:col-span-2 glass-card p-5">
        <h3 class="font-mono font-semibold text-sm text-slate-700 dark:text-slate-300 mb-4">Revenue 3 Bulan Lalu + Forecast</h3>
        <div style="height: 260px;">
            <canvas id="forecastChart"></canvas>
        </div>
    </div>

    {{-- Breakdown by plan --}}
    <div class="flat-card overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-white/[0.06]">
            <h3 class="font-mono font-semibold text-sm text-slate-700 dark:text-slate-300">Expiring per Plan (30 hari)</h3>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/[0.06]">
                    <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Plan</th>
                    <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Expiring</th>
                    <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Renewal%</th>
                    <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Proj.</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-white/[0.03]">
                @forelse($forecastByPlan as $row)
                <tr class="hover:bg-slate-50/60 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300 font-medium text-xs">{{ $row['plan_name'] }}</td>
                    <td class="px-4 py-3 text-right font-mono text-slate-600 dark:text-slate-400 text-xs">{{ $row['expiring'] }}</td>
                    <td class="px-4 py-3 text-right font-mono text-xs {{ $row['renewal_rate'] >= 50 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500' }}">{{ $row['renewal_rate'] }}%</td>
                    <td class="px-4 py-3 text-right font-mono text-xs text-slate-700 dark:text-slate-300">Rp {{ number_format($row['expected_rev'] / 1000, 0) }}rb</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400 text-xs">Tidak ada expiring dalam 30 hari.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- AI Insight Panel --}}
@if($hasAiKey)
<div class="glass-card p-5" x-data="aiNarrative()">
    <div class="flex items-center justify-between mb-3">
        <h3 class="font-mono font-semibold text-sm text-slate-700 dark:text-slate-300">AI Insight (Claude)</h3>
        <button @click="generate()" :disabled="loading"
            class="h-8 px-4 rounded-xl text-xs font-medium bg-violet-600 hover:bg-violet-700 disabled:opacity-50 text-white transition flex items-center gap-2">
            <svg x-show="loading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span x-text="loading ? 'Generating...' : (fromCache ? 'Refresh' : 'Generate Insight')"></span>
        </button>
    </div>
    <div x-show="!narrative && !loading" class="text-sm text-slate-400 text-center py-4">
        Klik "Generate Insight" untuk mendapatkan narasi AI tentang proyeksi revenue ini.
    </div>
    <div x-show="narrative" class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line" x-text="narrative"></div>
    <div x-show="fromCache" class="mt-2 text-xs text-slate-400 flex items-center gap-1">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Cached · <span x-text="cachedAt"></span>
    </div>
    <div x-show="error" class="text-sm text-red-500 mt-2" x-text="error"></div>
</div>
@endif

@endsection

@push('scripts')
<script>
(function () {
    const historical = @json($historicalRevenue);
    const base = {{ $baseForecast }};
    const pessimistic = {{ $pessimistic }};
    const optimistic = {{ $optimistic }};

    const labels = historical.map(r => r.month);
    const actuals = historical.map(r => r.revenue);

    // Add forecast month
    const lastDate = historical.length ? new Date(historical[historical.length - 1].month + '-01') : new Date();
    lastDate.setMonth(lastDate.getMonth() + 1);
    const forecastMonth = lastDate.toISOString().slice(0, 7);

    const ctx = document.getElementById('forecastChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [...labels, forecastMonth],
            datasets: [
                {
                    label: 'Aktual',
                    data: [...actuals, null],
                    backgroundColor: 'rgba(59,130,246,0.7)',
                    borderRadius: 6,
                    order: 2,
                },
                {
                    label: 'Realistis',
                    data: [...actuals.map(() => null), base],
                    backgroundColor: 'rgba(59,130,246,0.3)',
                    borderRadius: 6,
                    order: 2,
                },
                {
                    label: 'Optimistis',
                    data: [...actuals.map(() => null), optimistic],
                    type: 'line',
                    borderColor: 'rgba(16,185,129,0.7)',
                    backgroundColor: 'transparent',
                    borderDash: [5, 3],
                    pointBackgroundColor: 'rgba(16,185,129,0.9)',
                    pointRadius: 5,
                    order: 1,
                },
                {
                    label: 'Pesimistis',
                    data: [...actuals.map(() => null), pessimistic],
                    type: 'line',
                    borderColor: 'rgba(239,68,68,0.7)',
                    backgroundColor: 'transparent',
                    borderDash: [5, 3],
                    pointBackgroundColor: 'rgba(239,68,68,0.9)',
                    pointRadius: 5,
                    order: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { font: { size: 11 }, boxWidth: 12 } },
                tooltip: {
                    callbacks: {
                        label: ctx => ' Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw ?? 0),
                    },
                },
            },
            scales: {
                y: {
                    ticks: {
                        callback: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v),
                        font: { size: 10 },
                    },
                    grid: { color: 'rgba(0,0,0,0.05)' },
                },
                x: { ticks: { font: { size: 11 } }, grid: { display: false } },
            },
        },
    });
})();

function aiNarrative() {
    return {
        narrative: '',
        loading: false,
        fromCache: false,
        cachedAt: '',
        error: '',
        async generate() {
            this.loading = true;
            this.error = '';
            try {
                const res = await fetch('{{ route('admin.reports.revenue-forecast.ai') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({}),
                });
                const data = await res.json();
                if (data.narrative) {
                    this.narrative = data.narrative;
                    this.fromCache = data.from_cache ?? false;
                    this.cachedAt = data.cached_at ?? '';
                } else {
                    this.error = data.error ?? 'Gagal mengambil insight.';
                }
            } catch (e) {
                this.error = 'Terjadi kesalahan. Coba lagi.';
            }
            this.loading = false;
        }
    };
}
</script>
@endpush
