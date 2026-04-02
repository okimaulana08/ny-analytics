@extends('layouts.admin')
@section('title', 'Performa Konten')
@section('page-title', 'Performa Konten — Content Analytics')

@section('content')

{{-- Readers Modal --}}
<div x-data="readersModal()" @open-readers.window="open($event.detail)">
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="isOpen = false" style="display:none">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col">
            {{-- Modal header --}}
            <div class="flex items-start justify-between px-5 py-4 border-b border-slate-100 dark:border-white/[0.08] flex-shrink-0">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-800 dark:text-white">Pembaca Konten</p>
                    <p class="text-[11px] text-slate-400 mt-0.5 line-clamp-1" x-text="title"></p>
                </div>
                <div class="flex items-center gap-2 ml-3 flex-shrink-0">
                    <span x-show="loading" class="text-xs text-slate-400 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Memuat...
                    </span>
                    <span x-show="!loading && readers.length" class="text-[11px] text-slate-400" x-text="readers.length + ' pembaca'"></span>
                    <button @click="isOpen = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/[0.08] transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            {{-- Modal body --}}
            <div class="flex-1 overflow-y-auto">
                <div x-show="loading" class="flex items-center justify-center py-16 text-slate-400 text-sm">Memuat data pembaca...</div>
                <div x-show="!loading && readers.length === 0" class="flex items-center justify-center py-16 text-slate-400 text-sm">Belum ada pembaca yang tercatat.</div>
                <table x-show="!loading && readers.length > 0" class="w-full text-sm">
                    <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800/80 backdrop-blur-sm">
                        <tr class="border-b border-slate-100 dark:border-white/[0.06]">
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Nama</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Email</th>
                            <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Sesi</th>
                            <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Terakhir Baca</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(r, i) in readers" :key="i">
                            <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-200" x-text="r.name"></td>
                                <td class="px-5 py-2.5 text-xs text-slate-500 dark:text-slate-400" x-text="r.email"></td>
                                <td class="px-4 py-2.5 text-right font-mono text-xs text-slate-500" x-text="r.read_count"></td>
                                <td class="px-4 py-2.5 text-right font-mono text-[11px] text-slate-400 whitespace-nowrap" x-text="formatDate(r.last_read_at)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <p x-show="!loading && readers.length >= 200" class="text-center text-[11px] text-slate-400 py-3">Menampilkan 200 pembaca terakhir.</p>
            </div>
        </div>
    </div>
</div>

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
    <div class="glass-card p-5 xl:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Reads & Views — 30 Hari</h2>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Aktivitas harian dari semua konten</p>
            </div>
        </div>
        <div class="relative h-48"><canvas id="trendChart"></canvas></div>
    </div>
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
    <div class="px-5 py-4 border-b border-slate-100 dark:border-white/[0.06]">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
            <div>
                <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Semua Konten Publish</h2>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                    {{ number_format($total) }} konten
                    @if($title) <span class="text-blue-500">· judul: "{{ e($title) }}"</span> @endif
                    @if($author) <span class="text-blue-500">· penulis: "{{ e($author) }}"</span> @endif
                </p>
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
        {{-- Filter form: Judul + Penulis --}}
        <form method="GET" action="{{ route('admin.reports.content') }}" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="sort" value="{{ $sort }}">
            {{-- Title search --}}
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="title" value="{{ $title }}" placeholder="Cari judul..."
                    class="h-9 pl-9 pr-3.5 text-xs rounded-xl outline-none bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 w-48">
            </div>
            {{-- Author search --}}
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <input type="text" name="author" value="{{ $author }}" placeholder="Filter penulis..."
                    class="h-9 pl-9 pr-3.5 text-xs rounded-xl outline-none bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 w-44">
            </div>
            <button type="submit" class="h-9 px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl transition-colors">Cari</button>
            @if($title || $author)
            <a href="{{ route('admin.reports.content', ['sort' => $sort]) }}" class="h-9 px-3 flex items-center text-xs text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Reset
            </a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
    <table class="w-full min-w-max text-sm">
        <thead>
            <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Judul</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Kategori</th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Ch</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Reads</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Views</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Sub</th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Publish</th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
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
                <td class="px-4 py-3 text-right font-mono text-xs text-blue-600 dark:text-blue-400">{{ number_format($c->view_count) }}</td>
                <td class="px-4 py-3 text-right font-mono text-xs font-semibold {{ $c->subscribe_count > 0 ? 'text-violet-600 dark:text-violet-400' : 'text-slate-300 dark:text-slate-600' }}">
                    {{ $c->subscribe_count > 0 ? $c->subscribe_count : '—' }}
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
                <td class="px-4 py-3 text-center">
                    <div class="inline-flex items-center gap-1.5">
                        <button type="button"
                            onclick="window.dispatchEvent(new CustomEvent('open-readers', { detail: { id: '{{ $c->id }}', title: {{ json_encode($c->title) }} } }))"
                            class="h-7 px-2.5 inline-flex items-center gap-1 text-[11px] font-medium text-teal-600 dark:text-teal-400 border border-teal-200 dark:border-teal-500/30 rounded-lg hover:bg-teal-50 dark:hover:bg-teal-500/10 transition-colors whitespace-nowrap">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Pembaca
                        </button>
                        <a href="{{ route('admin.reports.content.pdf', $c->id) }}" target="_blank"
                            class="h-7 px-2.5 inline-flex items-center gap-1 text-[11px] font-medium text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-500/30 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-colors whitespace-nowrap">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            PDF
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="px-5 py-12 text-center text-sm text-slate-400">Tidak ada konten.</td></tr>
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
window.readersModal = function () {
    return {
        isOpen: false,
        loading: false,
        title: '',
        readers: [],
        open: async function (detail) {
            this.isOpen = true;
            this.loading = true;
            this.title = detail.title;
            this.readers = [];
            try {
                var res = await fetch('/admin/reports/content/readers/' + detail.id, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                var data = await res.json();
                this.readers = data.readers || [];
                this.title = data.title || detail.title;
            } catch (e) {
                this.readers = [];
            } finally {
                this.loading = false;
            }
        },
        formatDate: function (val) {
            if (!val) { return '—'; }
            var d = new Date(val);
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        },
    };
};

const dark = document.documentElement.classList.contains('dark');
const gridColor = dark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
const tickColor = dark ? '#475569' : '#94a3b8';
const tt = { backgroundColor: dark?'#1e293b':'#fff', titleColor: dark?'#f1f5f9':'#1e293b', bodyColor: dark?'#94a3b8':'#64748b', borderColor: dark?'#334155':'#e2e8f0', borderWidth:1, padding:10, cornerRadius:10 };

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
