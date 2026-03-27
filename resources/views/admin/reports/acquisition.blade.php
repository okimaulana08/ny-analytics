@extends('layouts.admin')
@section('title', 'Akuisisi & Referral')
@section('page-title', 'Akuisisi & Referral — Traffic Source')

@section('content')

{{-- Conversion Funnel --}}
<div class="glass-card p-5 mb-5">
    <div class="mb-5">
        <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Conversion Funnel</h2>
        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Dari registrasi hingga berlangganan</p>
    </div>
    @php
        $steps = [
            ['label' => 'Register', 'value' => $funnel->registered, 'color' => 'blue', 'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
            ['label' => 'Pernah Login', 'value' => $funnel->activated, 'color' => 'teal', 'icon' => 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1'],
            ['label' => 'Pernah Baca', 'value' => $funnel->readers, 'color' => 'violet', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
            ['label' => 'Berlangganan', 'value' => $funnel->paying, 'color' => 'emerald', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
        ];
        $max = $funnel->registered ?: 1;
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($steps as $i => $step)
        @php
            $pct = round($step['value'] / $max * 100);
            $dropPct = $i > 0 ? round((1 - $step['value'] / max($steps[$i-1]['value'], 1)) * 100) : 0;
            $colorMap = [
                'blue'    => ['bar' => 'bg-blue-500',    'text' => 'text-blue-600 dark:text-blue-400',    'bg' => 'bg-blue-50 dark:bg-blue-500/10'],
                'teal'    => ['bar' => 'bg-teal-500',    'text' => 'text-teal-600 dark:text-teal-400',    'bg' => 'bg-teal-50 dark:bg-teal-500/10'],
                'violet'  => ['bar' => 'bg-violet-500',  'text' => 'text-violet-600 dark:text-violet-400','bg' => 'bg-violet-50 dark:bg-violet-500/10'],
                'emerald' => ['bar' => 'bg-emerald-500', 'text' => 'text-emerald-600 dark:text-emerald-400','bg' => 'bg-emerald-50 dark:bg-emerald-500/10'],
            ];
            $c = $colorMap[$step['color']];
        @endphp
        <div class="flat-card p-4">
            <div class="flex items-start justify-between mb-3">
                <div class="w-9 h-9 rounded-xl {{ $c['bg'] }} flex items-center justify-center flex-shrink-0">
                <svg class="w-4.5 h-4.5 {{ $c['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $step['icon'] }}"/>
                </svg>
                </div>
                @if($i > 0 && $dropPct > 0)
                <span class="text-[10px] font-semibold text-red-400 bg-red-50 dark:bg-red-500/10 px-2 py-1 rounded-full whitespace-nowrap">−{{ $dropPct }}% drop</span>
                @endif
            </div>
            <p class="font-mono text-2xl font-bold {{ $c['text'] }}">{{ number_format($step['value']) }}</p>
            <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mt-1">{{ $step['label'] }}</p>
            <div class="mt-3 h-1.5 bg-slate-100 dark:bg-white/[0.06] rounded-full overflow-hidden">
                <div class="{{ $c['bar'] }} h-full rounded-full transition-all" style="width: {{ $pct }}%"></div>
            </div>
            <p class="text-[11px] text-slate-400 mt-1 font-mono">{{ $pct }}% dari total register</p>
        </div>
        @endforeach
    </div>
</div>

{{-- Charts Row --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-5">

    {{-- Weekly registration trend --}}
    <div class="glass-card p-5">
        <div class="mb-4">
            <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Registrasi per Minggu — 12 Minggu</h2>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Tren user baru mingguan</p>
        </div>
        <div class="relative h-44"><canvas id="regWeekChart"></canvas></div>
    </div>

    {{-- Conversion funnel chart --}}
    <div class="glass-card p-5">
        <div class="mb-4">
            <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Funnel — Register → Bayar</h2>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Visualisasi tingkat konversi tiap tahap</p>
        </div>
        <div class="relative h-44"><canvas id="funnelChart"></canvas></div>
    </div>
</div>

{{-- UTM Attribution --}}
<div class="flat-card mb-5">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-white/[0.06]">
        <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">UTM Attribution</h2>
        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Sumber traffic berdasarkan attribution events</p>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full min-w-max text-sm">
        <thead>
            <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Source</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Medium</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Campaign</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Users</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Events</th>
            </tr>
        </thead>
        <tbody>
            @forelse($utmBreakdown as $utm)
            <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-3">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200">
                        @if($utm->source === 'organic')
                            <span class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span> organic
                        @elseif(str_contains($utm->source, 'ig') || str_contains($utm->source, 'instagram'))
                            <span class="w-2 h-2 rounded-full bg-pink-400 flex-shrink-0"></span> {{ $utm->source }}
                        @else
                            <span class="w-2 h-2 rounded-full bg-blue-400 flex-shrink-0"></span> {{ $utm->source }}
                        @endif
                    </span>
                </td>
                <td class="px-5 py-3 font-mono text-[11px] text-slate-500">{{ $utm->medium }}</td>
                <td class="px-5 py-3 font-mono text-[11px] text-slate-400 max-w-[200px] truncate" title="{{ $utm->campaign }}">{{ $utm->campaign }}</td>
                <td class="px-5 py-3 text-right font-mono text-xs font-semibold text-slate-600 dark:text-slate-300">{{ number_format($utm->users) }}</td>
                <td class="px-5 py-3 text-right font-mono text-xs text-slate-400">{{ number_format($utm->events) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">Belum ada data UTM.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
    @include('admin.partials.pagination', [
        'page'       => $utmPage,
        'totalPages' => $utmTotalPages,
        'total'      => $utmTotal,
        'perPage'    => $perPage,
        'param'      => 'utm_page',
    ])
</div>

{{-- Share Activity + Short Links --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-5">

    {{-- Most shared content --}}
    <div class="flat-card">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-white/[0.06]">
            <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Konten Paling Banyak Dibagikan</h2>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Cerita yang paling sering di-share oleh user</p>
        </div>
        @if(count($mostShared) > 0)
        @php $maxShares = $mostShared[0]->shares ?: 1; @endphp
        <div class="p-4 space-y-2.5">
            @foreach($mostShared as $i => $ms)
            @php $pct = round($ms->shares / $maxShares * 100); @endphp
            <div class="flex items-center gap-3">
                <span class="font-mono text-[11px] font-bold text-slate-400 w-5 flex-shrink-0 text-right">{{ $sharedOffset + $i + 1 }}</span>
                <div class="flex-1 min-w-0">
                    <p class="text-[12px] font-medium text-slate-700 dark:text-slate-200 truncate mb-1">{{ $ms->title }}</p>
                    <div class="h-1.5 bg-slate-100 dark:bg-white/[0.06] rounded-full overflow-hidden">
                        <div class="bg-violet-500 h-full rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $ms->shares }}</span>
                    <span class="text-[10px] text-slate-400 ml-1">share</span>
                    <div class="text-[10px] text-slate-400">{{ $ms->unique_users }} user</div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="px-5 py-10 text-center text-sm text-slate-400">Belum ada aktivitas share.</div>
        @endif
        @include('admin.partials.pagination', [
            'page'       => $sharedPage,
            'totalPages' => $sharedTotalPages,
            'total'      => $sharedTotal,
            'perPage'    => $perPage,
            'param'      => 'shared_page',
        ])
    </div>

    {{-- Short Links --}}
    <div class="flat-card">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-white/[0.06]">
            <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Short Links</h2>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Link pendek yang dibuat untuk kampanye</p>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full min-w-max text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Code</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Medium</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Campaign</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Klik</th>
                    <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Dibuat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shortLinks as $sl)
                <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-5 py-3 font-mono text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $sl->code }}</td>
                    <td class="px-5 py-3 font-mono text-[11px] text-slate-500">{{ $sl->utm_medium ?? '—' }}</td>
                    <td class="px-5 py-3 font-mono text-[11px] text-slate-400">{{ $sl->utm_campaign ?? '—' }}</td>
                    <td class="px-5 py-3 text-right font-mono text-xs {{ $sl->clicks > 0 ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-slate-300 dark:text-slate-600' }}">
                        {{ $sl->clicks > 0 ? number_format($sl->clicks) : '—' }}
                    </td>
                    <td class="px-5 py-3 text-center font-mono text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($sl->created_at)->format('d M Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">Belum ada short link.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @include('admin.partials.pagination', [
            'page'       => $slPage,
            'totalPages' => $slTotalPages,
            'total'      => $slTotal,
            'perPage'    => $perPage,
            'param'      => 'sl_page',
        ])
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const dark = document.documentElement.classList.contains('dark');
const gridColor = dark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
const tickColor = dark ? '#475569' : '#94a3b8';
const tt = { backgroundColor: dark?'#1e293b':'#fff', titleColor: dark?'#f1f5f9':'#1e293b', bodyColor: dark?'#94a3b8':'#64748b', borderColor: dark?'#334155':'#e2e8f0', borderWidth:1, padding:10, cornerRadius:10 };

// Weekly registration chart
const weekData = @json($regWeekly);
new Chart(document.getElementById('regWeekChart'), {
    type: 'bar',
    data: {
        labels: weekData.map(d => {
            const dt = new Date(d.week_start);
            return dt.toLocaleDateString('id-ID', {day:'numeric', month:'short'});
        }),
        datasets: [{
            label: 'User Baru',
            data: weekData.map(d => d.new_users),
            backgroundColor: dark ? 'rgba(59,130,246,0.5)' : 'rgba(37,99,235,0.4)',
            borderRadius: 5, borderSkipped: false,
        }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false}, tooltip:{...tt, callbacks:{label: ctx => ` ${ctx.raw} user baru`}} }, scales:{ x:{grid:{display:false},ticks:{color:tickColor,font:{size:10}},border:{display:false}}, y:{grid:{color:gridColor},ticks:{color:tickColor,font:{size:10},stepSize:1},border:{display:false},beginAtZero:true} } }
});

// Funnel bar chart
const funnelLabels = ['Register', 'Pernah Login', 'Pernah Baca', 'Berlangganan'];
const funnelValues = [{{ $funnel->registered }}, {{ $funnel->activated }}, {{ $funnel->readers }}, {{ $funnel->paying }}];
const funnelColors = [
    dark ? 'rgba(59,130,246,0.6)' : 'rgba(37,99,235,0.5)',
    dark ? 'rgba(20,184,166,0.6)' : 'rgba(13,148,136,0.5)',
    dark ? 'rgba(139,92,246,0.6)' : 'rgba(109,40,217,0.5)',
    dark ? 'rgba(16,185,129,0.6)' : 'rgba(5,150,105,0.5)',
];
new Chart(document.getElementById('funnelChart'), {
    type: 'bar',
    data: {
        labels: funnelLabels,
        datasets: [{
            data: funnelValues,
            backgroundColor: funnelColors,
            borderRadius: 6, borderSkipped: false,
        }]
    },
    options: {
        indexAxis: 'y', responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{display:false}, tooltip:{...tt, callbacks:{label: ctx => ` ${Number(ctx.raw).toLocaleString('id-ID')} user`}} },
        scales:{ x:{grid:{color:gridColor},ticks:{color:tickColor,font:{size:10}},border:{display:false}}, y:{grid:{display:false},ticks:{color:tickColor,font:{size:11}},border:{display:false}} }
    }
});
</script>
@endpush
