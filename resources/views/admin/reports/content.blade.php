@extends('layouts.admin')
@section('title', 'Performa Konten')
@section('page-title', 'Performa Konten — Content Analytics')

@section('content')

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="glass-card p-5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Konten Publish</p>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($kpi->total_content) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">{{ number_format($kpi->completed_content) }} sudah tamat</p>
    </div>
    <div class="glass-card p-5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Total Reads</p>
        <p class="font-mono text-2xl font-bold text-teal-600 dark:text-teal-400">{{ number_format($kpi->total_reads) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">{{ number_format($kpi->unique_readers) }} pembaca unik</p>
    </div>
    <div class="glass-card p-5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Total Views</p>
        <p class="font-mono text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($kpi->total_views) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">{{ $kpi->active_today }} konten aktif hari ini</p>
    </div>
    <div class="glass-card p-5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Total Subscribe</p>
        <p class="font-mono text-2xl font-bold text-violet-600 dark:text-violet-400">{{ number_format($kpi->total_subscribes) }}</p>
        @php $convOverall = $kpi->total_views > 0 ? round($kpi->total_subscribes / $kpi->total_views * 100, 2) : 0; @endphp
        <p class="text-[11px] text-slate-400 mt-1">{{ $convOverall }}% conversion dari view</p>
    </div>
</div>

{{-- Charts Row --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-5">

    {{-- Daily Trend 30 days --}}
    <div class="glass-card p-5 xl:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Reads & Views — 30 Hari</h2>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Aktivitas harian dari semua konten</p>
            </div>
        </div>
        <div class="relative h-48"><canvas id="trendChart"></canvas></div>
    </div>

    {{-- Category breakdown --}}
    <div class="glass-card p-5">
        <div class="mb-4">
            <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Reads per Kategori</h2>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Distribusi pembacaan</p>
        </div>
        <div class="relative h-48"><canvas id="catChart"></canvas></div>
    </div>
</div>

{{-- Top Subscribe Converters --}}
@if(count($topBySubscribe) > 0)
<div class="glass-card p-5 mb-5">
    <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white mb-4">🏆 Top Konten — Subscribe Tertinggi</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-{{ min(count($topBySubscribe), 5) }} gap-3">
        @foreach($topBySubscribe as $i => $c)
        <div class="bg-slate-50 dark:bg-white/[0.03] rounded-xl p-4 border border-slate-100 dark:border-white/[0.05]">
            <div class="flex items-start justify-between gap-2 mb-2">
                <span class="font-mono text-xs font-bold {{ $i === 0 ? 'text-amber-500' : 'text-slate-400' }}">#{{ $i+1 }}</span>
                <span class="text-[10px] font-semibold text-violet-500 bg-violet-50 dark:bg-violet-500/10 px-2 py-0.5 rounded-full whitespace-nowrap">{{ $c->subscribe_count }}× sub</span>
            </div>
            <p class="text-xs font-medium text-slate-700 dark:text-slate-200 leading-snug line-clamp-2">{{ $c->title }}</p>
            <div class="mt-2 flex items-center gap-3 text-[11px] text-slate-400 font-mono">
                <span>{{ number_format($c->read_count) }} reads</span>
                @if($c->convert_rate)<span class="text-emerald-500">{{ $c->convert_rate }}% conv</span>@endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Content Table --}}
<div class="flat-card">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-white/[0.06] flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Semua Konten Publish</h2>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">{{ number_format($total) }} konten</p>
        </div>
        {{-- Sort tabs --}}
        <div class="flex items-center gap-1 p-1 rounded-xl bg-slate-100 dark:bg-white/[0.04]">
            @foreach(['reads' => 'Reads', 'views' => 'Views', 'subscribes' => 'Subscribe', 'recent' => 'Terbaru'] as $val => $label)
            <a href="{{ request()->fullUrlWithQuery(['sort' => $val, 'page' => 1]) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all {{ $sort === $val ? 'bg-white dark:bg-white/10 text-slate-800 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
    </div>

    <div class="overflow-x-auto">
    <table class="w-full min-w-max text-sm">
        <thead>
            <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Judul</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Kategori</th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Ch</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Reads</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">30h</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Views</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Sub</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Conv%</th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Publish</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contents as $c)
            <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-3 max-w-[240px]">
                    <div class="text-xs font-medium text-slate-700 dark:text-slate-200 leading-snug line-clamp-2">{{ $c->title }}</div>
                    @if($c->author_name)<div class="text-[11px] text-slate-400 mt-0.5">{{ $c->author_name }}</div>@endif
                </td>
                <td class="px-5 py-3 text-[11px] text-slate-500 whitespace-nowrap">{{ $c->category ?? '—' }}</td>
                <td class="px-4 py-3 text-center font-mono text-xs text-slate-500">{{ $c->chapter_count }}</td>
                <td class="px-4 py-3 text-right font-mono text-xs font-semibold text-teal-600 dark:text-teal-400">{{ number_format($c->read_count) }}</td>
                <td class="px-4 py-3 text-right font-mono text-[11px] {{ ($c->reads_30d ?? 0) > 0 ? 'text-teal-500' : 'text-slate-300 dark:text-slate-600' }}">
                    {{ $c->reads_30d > 0 ? number_format($c->reads_30d) : '—' }}
                </td>
                <td class="px-4 py-3 text-right font-mono text-xs text-blue-600 dark:text-blue-400">{{ number_format($c->view_count) }}</td>
                <td class="px-4 py-3 text-right font-mono text-xs font-semibold {{ $c->subscribe_count > 0 ? 'text-violet-600 dark:text-violet-400' : 'text-slate-300 dark:text-slate-600' }}">
                    {{ $c->subscribe_count > 0 ? $c->subscribe_count : '—' }}
                </td>
                <td class="px-4 py-3 text-right font-mono text-[11px] {{ ($c->convert_rate ?? 0) > 0 ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-slate-300 dark:text-slate-600' }}">
                    {{ ($c->convert_rate ?? 0) > 0 ? $c->convert_rate . '%' : '—' }}
                </td>
                <td class="px-4 py-3 text-center">
                    @if($c->is_completed)
                        <span class="badge badge-paid">Tamat</span>
                    @else
                        <span class="badge badge-expired">Ongoing</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-center font-mono text-[11px] text-slate-400 whitespace-nowrap">
                    {{ $c->published_at ? \Carbon\Carbon::parse($c->published_at)->format('d M Y') : '—' }}
                </td>
            </tr>
            @empty
            <tr><td colspan="10" class="px-5 py-12 text-center text-sm text-slate-400">Tidak ada konten.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    @include('admin.partials.pagination', [
        'page' => $page, 'totalPages' => $totalPages,
        'total' => $total, 'perPage' => $perPage, 'param' => 'page',
    ])
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const dark = document.documentElement.classList.contains('dark');
const gridColor = dark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
const tickColor = dark ? '#475569' : '#94a3b8';
const tt = { backgroundColor: dark?'#1e293b':'#fff', titleColor: dark?'#f1f5f9':'#1e293b', bodyColor: dark?'#94a3b8':'#64748b', borderColor: dark?'#334155':'#e2e8f0', borderWidth:1, padding:10, cornerRadius:10 };

// Trend Chart
const trend = @json($trend30d);
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: trend.map(d => { const dt = new Date(d.date); return dt.toLocaleDateString('id-ID',{day:'numeric',month:'short'}); }),
        datasets: [
            { label: 'Reads', data: trend.map(d => d.reads), borderColor: dark?'#2dd4bf':'#0d9488', backgroundColor: dark?'rgba(45,212,191,0.1)':'rgba(13,148,136,0.07)', borderWidth:2, fill:true, tension:0.4, pointRadius:2 },
            { label: 'Views', data: trend.map(d => d.views), borderColor: dark?'#60a5fa':'#3b82f6', backgroundColor: 'transparent', borderWidth:1.5, tension:0.4, pointRadius:2, borderDash:[4,3] },
            { label: 'Subscribe', data: trend.map(d => d.subscribes), borderColor: dark?'#a78bfa':'#7c3aed', backgroundColor: 'transparent', borderWidth:1.5, tension:0.4, pointRadius:2, borderDash:[2,2] },
        ]
    },
    options: { responsive:true, maintainAspectRatio:false, interaction:{mode:'index',intersect:false}, plugins:{ legend:{display:true,labels:{color:tickColor,font:{size:10},boxWidth:10}}, tooltip:{...tt} }, scales:{ x:{grid:{display:false},ticks:{color:tickColor,font:{size:10},maxTicksLimit:10},border:{display:false}}, y:{grid:{color:gridColor},ticks:{color:tickColor,font:{size:10}},border:{display:false},beginAtZero:true} } }
});

// Category Chart
const cats = @json($byCategory);
const catColors = ['#0d9488','#3b82f6','#8b5cf6','#f59e0b','#ef4444','#ec4899','#14b8a6'];
new Chart(document.getElementById('catChart'), {
    type: 'doughnut',
    data: {
        labels: cats.map(c => c.category),
        datasets: [{ data: cats.map(c => c.total_reads), backgroundColor: catColors.slice(0, cats.length), borderWidth: 0, hoverOffset: 6 }]
    },
    options: { responsive:true, maintainAspectRatio:false, cutout:'65%', plugins:{ legend:{display:true,position:'bottom',labels:{color:tickColor,font:{size:10},boxWidth:10,padding:8}}, tooltip:{...tt,callbacks:{label:ctx=>' '+Number(ctx.raw).toLocaleString('id-ID')+' reads'}} } }
});
</script>
@endpush
