@extends('layouts.admin')
@section('title', 'Chapter Drop-off')
@section('page-title', 'Chapter Drop-off — Funnel Pembaca per Chapter')

@section('content')
<div x-data="chapterHeatmap()">

    {{-- Header + Search --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <p class="text-sm text-slate-500 dark:text-slate-400">Visualisasi di chapter mana pembaca berhenti membaca</p>
        <div class="flex items-center gap-2" style="position:relative">
            <input type="text" x-model="query" @input.debounce.350ms="search()" @keydown.escape="results=[]" placeholder="Cari judul konten..."
                class="h-9 px-3 w-72 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
            <button @click="reset()" x-show="selected"
                class="h-9 px-3 rounded-xl border border-slate-200 dark:border-white/10 text-xs text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition">
                Reset
            </button>

            {{-- Dropdown results --}}
            <div x-show="results.length > 0 && !selected"
                class="absolute top-full right-0 mt-1 w-96 rounded-xl border border-slate-200 dark:border-white/10 overflow-hidden bg-white dark:bg-slate-900 shadow-xl z-20 max-h-56 overflow-y-auto"
                @click.outside="results=[]">
                <template x-for="r in results" :key="r.id">
                    <button @click="selectContent(r)" class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 dark:hover:bg-white/[0.04] border-b border-slate-50 dark:border-white/[0.03] last:border-0 transition-colors">
                        <span class="font-medium text-slate-700 dark:text-slate-200" x-text="r.title"></span>
                        <span class="text-slate-400 text-xs ml-2" x-text="'(' + r.chapter_count + ' ch)'"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- Empty state --}}
    <div x-show="!selected && !loading" class="glass-card flex flex-col items-center justify-center py-24 text-slate-400">
        <svg class="w-14 h-14 mb-4 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="font-medium text-sm">Cari dan pilih konten untuk melihat drop-off per chapter</p>
        <p class="text-xs mt-1 text-slate-400">Ketik minimal 2 karakter di kolom pencarian</p>
    </div>

    {{-- Loading state --}}
    <div x-show="loading" class="glass-card flex items-center justify-center py-24 text-slate-400 gap-3">
        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        <span class="text-sm">Memuat data chapter...</span>
    </div>

    {{-- Main content --}}
    <div x-show="selected && !loading">

        {{-- Content header --}}
        <div class="glass-card p-5 mb-4">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="font-mono text-base font-semibold text-slate-800 dark:text-white" x-text="selected?.title"></h2>
                    <p class="text-xs text-slate-400 mt-1" x-text="chapters.length + ' chapter dipublikasi'"></p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <div class="text-center px-5 py-3 bg-slate-50 dark:bg-white/[0.03] rounded-xl border border-slate-100 dark:border-white/[0.05]">
                        <p class="font-mono text-lg font-bold text-slate-800 dark:text-white" x-text="Number(chapters[0]?.read_count || 0).toLocaleString('id-ID')"></p>
                        <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-wide">Pembaca Ch.1</p>
                    </div>
                    <div class="text-center px-5 py-3 bg-slate-50 dark:bg-white/[0.03] rounded-xl border border-slate-100 dark:border-white/[0.05]">
                        <p class="font-mono text-lg font-bold"
                            :class="(chapters[chapters.length-1]?.retention_pct || 0) >= 50 ? 'text-emerald-500' : (chapters[chapters.length-1]?.retention_pct || 0) >= 25 ? 'text-amber-500' : 'text-red-500'"
                            x-text="(chapters[chapters.length-1]?.retention_pct || 0) + '%'"></p>
                        <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-wide">Retensi Akhir</p>
                    </div>
                    <div class="text-center px-5 py-3 bg-slate-50 dark:bg-white/[0.03] rounded-xl border border-slate-100 dark:border-white/[0.05]">
                        <p class="font-mono text-lg font-bold text-red-500" x-text="cliffChapter()"></p>
                        <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-wide">Cliff Chapter</p>
                    </div>
                    <div class="text-center px-5 py-3 bg-slate-50 dark:bg-white/[0.03] rounded-xl border border-slate-100 dark:border-white/[0.05]">
                        <p class="font-mono text-lg font-bold text-slate-700 dark:text-slate-300" x-text="avgDropoff() + '%'"></p>
                        <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-wide">Avg Drop-off/Ch</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart --}}
        <div x-show="chapters.length > 0" class="glass-card p-5 mb-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Funnel Pembaca per Chapter</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Bar = jumlah pembaca · Garis = % retensi vs Chapter 1</p>
                </div>
                <div class="flex items-center gap-3 text-[11px] text-slate-400">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-emerald-500/75 inline-block"></span> ≥70%</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-amber-500/75 inline-block"></span> 50–69%</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-orange-500/75 inline-block"></span> 30–49%</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-red-500/75 inline-block"></span> &lt;30%</span>
                </div>
            </div>
            <div class="relative h-72">
                <canvas id="chapterFunnelChart"></canvas>
            </div>
        </div>

        {{-- Detail Table --}}
        <div x-show="chapters.length > 0" class="flat-card">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between">
                <h3 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Detail per Chapter</h3>
                <p x-show="chapters.length > 60" class="text-[11px] text-slate-400">Menampilkan semua <span x-text="chapters.length"></span> chapter</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                            <th class="py-2.5 px-5 text-left text-[10px] font-semibold text-slate-400 uppercase tracking-wider w-12">Ch</th>
                            <th class="py-2.5 px-4 text-left text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Judul Chapter</th>
                            <th class="py-2.5 px-4 text-right text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Pembaca</th>
                            <th class="py-2.5 px-4 text-right text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Retensi</th>
                            <th class="py-2.5 px-4 text-right text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Drop-off</th>
                            <th class="py-2.5 px-5 text-left text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Bar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="ch in chapters" :key="ch.sequence">
                            <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02]"
                                :class="ch.dropoff_pct >= 20 ? 'bg-red-50/30 dark:bg-red-500/[0.03]' : ''">
                                <td class="py-2 px-5 font-mono text-slate-500 dark:text-slate-400" x-text="ch.sequence"></td>
                                <td class="py-2 px-4 text-slate-600 dark:text-slate-300 max-w-xs">
                                    <span class="line-clamp-1" x-text="ch.title || '—'"></span>
                                </td>
                                <td class="py-2 px-4 text-right font-mono text-slate-700 dark:text-slate-200" x-text="Number(ch.read_count).toLocaleString('id-ID')"></td>
                                <td class="py-2 px-4 text-right font-mono font-semibold"
                                    :class="ch.retention_pct >= 70 ? 'text-emerald-500' : ch.retention_pct >= 50 ? 'text-amber-500' : ch.retention_pct >= 30 ? 'text-orange-500' : 'text-red-500'"
                                    x-text="ch.retention_pct + '%'"></td>
                                <td class="py-2 px-4 text-right font-mono"
                                    :class="ch.dropoff_pct >= 20 ? 'text-red-500 font-semibold' : ch.dropoff_pct >= 10 ? 'text-amber-500' : 'text-slate-400'"
                                    x-text="ch.dropoff_pct > 0 ? '-' + ch.dropoff_pct + '%' : '—'"></td>
                                <td class="py-2 px-5">
                                    <div class="h-1.5 rounded-full bg-slate-100 dark:bg-white/[0.06] w-24 overflow-hidden">
                                        <div class="h-full rounded-full transition-all"
                                            :style="'width:' + ch.retention_pct + '%'"
                                            :class="ch.retention_pct >= 70 ? 'bg-emerald-400' : ch.retention_pct >= 50 ? 'bg-amber-400' : ch.retention_pct >= 30 ? 'bg-orange-400' : 'bg-red-400'">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Empty chapters --}}
        <div x-show="chapters.length === 0" class="glass-card flex items-center justify-center py-16 text-slate-400 text-sm">
            Konten ini belum memiliki data chapter.
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
window.chapterHeatmap = function () {
    return {
        query: '',
        results: [],
        selected: null,
        loading: false,
        chapters: [],
        _chart: null,

        async init() {
            const params = new URLSearchParams(window.location.search);
            const id = params.get('content_id');
            const title = params.get('content_title') || '';
            if (id) {
                this.query = title;
                await this.selectContent({ id, title, chapter_count: null });
            }
        },

        reset() {
            this.query = '';
            this.results = [];
            this.selected = null;
            this.chapters = [];
            if (this._chart) { this._chart.destroy(); this._chart = null; }
        },

        async search() {
            if (this.query.length < 2) { this.results = []; return; }
            try {
                const res = await fetch('/admin/reports/content/search?' + new URLSearchParams({ q: this.query }));
                if (!res.ok) { return; }
                this.results = await res.json();
            } catch (e) { this.results = []; }
        },

        async selectContent(r) {
            this.selected = r;
            this.results = [];
            this.query = r.title;
            this.loading = true;
            this.chapters = [];
            if (this._chart) { this._chart.destroy(); this._chart = null; }
            try {
                const res = await fetch('/admin/reports/content/' + encodeURIComponent(r.id) + '/chapter-funnel');
                const data = await res.json();
                // Enrich selected with real title & chapter count from response
                if (data.content) {
                    this.selected = { id: data.content.id, title: data.content.title, chapter_count: data.content.total_chapters };
                    this.query = data.content.title;
                }
                this.chapters = data.chapters || [];
                this.$nextTick(() => this.renderChart());
            } catch (e) {
                this.chapters = [];
            } finally {
                this.loading = false;
            }
        },

        cliffChapter() {
            if (!this.chapters.length) { return '—'; }
            let max = 0, cliff = null;
            this.chapters.forEach(ch => {
                if (ch.dropoff_pct > max) { max = ch.dropoff_pct; cliff = ch.sequence; }
            });
            return cliff ? 'Ch.' + cliff + ' (-' + max + '%)' : '—';
        },

        avgDropoff() {
            if (this.chapters.length < 2) { return 0; }
            const drops = this.chapters.slice(1).map(ch => ch.dropoff_pct);
            return (drops.reduce((a, b) => a + b, 0) / drops.length).toFixed(1);
        },

        renderChart() {
            const canvas = document.getElementById('chapterFunnelChart');
            if (!canvas) { return; }
            if (this._chart) { this._chart.destroy(); }
            const chs = this.chapters;
            const labels = chs.map(ch => 'Ch.' + ch.sequence);
            const dark = document.documentElement.classList.contains('dark');
            const gridColor = dark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
            const tickColor = dark ? '#475569' : '#94a3b8';
            const barColors = chs.map(ch => {
                const r = ch.retention_pct;
                if (r >= 70) { return 'rgba(16,185,129,0.75)'; }
                if (r >= 50) { return 'rgba(245,158,11,0.75)'; }
                if (r >= 30) { return 'rgba(249,115,22,0.75)'; }
                return 'rgba(239,68,68,0.75)';
            });
            const tt = { backgroundColor: dark ? '#1e293b' : '#fff', titleColor: dark ? '#f1f5f9' : '#1e293b', bodyColor: dark ? '#94a3b8' : '#64748b', borderColor: dark ? '#334155' : '#e2e8f0', borderWidth: 1, padding: 10, cornerRadius: 10 };
            this._chart = new Chart(canvas, {
                data: {
                    labels,
                    datasets: [
                        { type: 'bar', label: 'Pembaca', data: chs.map(c => c.read_count), backgroundColor: barColors, yAxisID: 'yLeft', borderRadius: 3 },
                        { type: 'line', label: 'Retensi %', data: chs.map(c => c.retention_pct), borderColor: dark ? '#60a5fa' : '#3b82f6', backgroundColor: 'transparent', borderWidth: 2, pointRadius: chs.length > 50 ? 0 : 3, tension: 0.3, yAxisID: 'yRight' },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: true, labels: { color: tickColor, font: { size: 11 }, boxWidth: 12 } },
                        tooltip: {
                            ...tt,
                            callbacks: {
                                afterBody: (items) => {
                                    const idx = items[0]?.dataIndex;
                                    if (idx === undefined) { return ''; }
                                    const ch = chs[idx];
                                    return ch.dropoff_pct > 0 ? ['Drop-off dari sebelumnya: -' + ch.dropoff_pct + '%'] : [];
                                },
                            },
                        },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 10 }, maxTicksLimit: 25 }, border: { display: false } },
                        yLeft: { position: 'left', grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 10 } }, border: { display: false }, beginAtZero: true },
                        yRight: { position: 'right', min: 0, max: 100, grid: { display: false }, ticks: { color: tickColor, font: { size: 10 }, callback: v => v + '%' }, border: { display: false } },
                    },
                },
            });
        },
    };
};
</script>
@endpush
