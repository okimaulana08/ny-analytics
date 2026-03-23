@extends('layouts.admin')
@section('title', 'Laporan Engagement')
@section('page-title', 'Laporan Engagement')

@section('content')
@php
    $totalReads    = $engKpi->total_reads ?? 0;
    $totalViews    = $engKpi->total_views ?? 0;
    $activeCount   = $activeReaders->cnt ?? 0;
    $avgDepthVal   = $avgDepth->val ?? 0;
    $repeatCount   = $repeatReaders->repeat_readers ?? 0;
    $totalRCount   = $repeatReaders->total_readers ?? 0;
    $repeatRate    = $totalRCount > 0 ? round($repeatCount * 100 / $totalRCount, 1) : 0;
@endphp

{{-- KPI Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">

    <div class="glass-card p-5 cursor-default">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-teal-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-500/10 px-2 py-1 rounded-full">30 Hari</span>
        </div>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($totalReads) }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Total Reads</p>
        <p class="text-sm font-semibold text-teal-600 dark:text-teal-400 mt-0.5">{{ number_format($totalViews) }} views</p>
    </div>

    <div class="glass-card p-5 cursor-default">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 px-2 py-1 rounded-full">30 Hari</span>
        </div>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($activeCount) }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Active Readers</p>
        <button onclick="openUserModal('active_readers','Active Readers','Membaca setidaknya 1 chapter dalam 30 hari terakhir')" class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer mt-0.5">Lihat daftar user</button>
    </div>

    <div class="glass-card p-5 cursor-default">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-violet-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white">{{ $avgDepthVal }}</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Rata-rata Chapter/User</p>
        <p class="text-sm font-semibold text-violet-600 dark:text-violet-400 mt-0.5">Per hari, 30 hari</p>
    </div>

    <div class="glass-card p-5 cursor-default">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-1 rounded-full">30 Hari</span>
        </div>
        <p class="font-mono text-2xl font-bold text-slate-900 dark:text-white">{{ $repeatRate }}%</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Repeat Reader Rate</p>
        <button onclick="openUserModal('repeat_readers','Repeat Readers','Membaca di ≥2 hari berbeda dalam 30 hari terakhir')" class="text-sm font-semibold text-amber-600 dark:text-amber-400 hover:underline cursor-pointer mt-0.5">{{ $repeatCount }} dari {{ $totalRCount }} reader</button>
    </div>
</div>

{{-- Charts Row 1 --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-5">

    {{-- Daily reads & views --}}
    <div class="glass-card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Daily Reads & Views — 30 Hari</h2>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Aktivitas baca harian</p>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-400">
                <span class="flex items-center gap-1.5"><span class="w-3 h-1.5 rounded-full bg-teal-500 inline-block"></span>Reads</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-1.5 rounded-full bg-blue-400 inline-block"></span>Views</span>
                <div class="relative group flex-shrink-0 ml-1">
                    <button class="w-5 h-5 rounded-full bg-slate-200 dark:bg-white/10 text-slate-500 dark:text-slate-400 text-[10px] font-bold flex items-center justify-center hover:bg-slate-300 dark:hover:bg-white/20 transition-colors">?</button>
                    <div class="absolute right-0 top-7 z-20 w-72 p-3 rounded-xl bg-slate-800 text-white text-[11px] leading-relaxed shadow-2xl border border-white/10 opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-150">
                        <p class="font-semibold mb-1 text-teal-300">Apa arti grafik ini?</p>
                        <p class="mb-2"><span class="text-teal-400 font-medium">Reads</span> = jumlah chapter yang dibaca user dalam sehari. <span class="text-blue-300 font-medium">Views</span> = jumlah kunjungan ke halaman konten.</p>
                        <p class="mb-2">Idealnya <span class="text-teal-400">Reads &gt; Views</span> — artinya user yang buka konten tidak hanya melihat sekilas tapi benar-benar membaca.</p>
                        <p class="text-slate-400">Jika Views tinggi tapi Reads rendah, berarti banyak user yang masuk lalu langsung keluar (bounce).</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="relative h-48"><canvas id="dailyChart"></canvas></div>
    </div>

    {{-- Avg depth per day --}}
    <div class="glass-card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Rata-rata Chapter per User</h2>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Kedalaman baca harian</p>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-400">
                <span class="flex items-center gap-1.5"><span class="w-3 h-1.5 rounded-full bg-violet-500 inline-block"></span>Avg depth</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-1.5 rounded-full bg-slate-300 inline-block"></span>Readers</span>
                <div class="relative group flex-shrink-0 ml-1">
                    <button class="w-5 h-5 rounded-full bg-slate-200 dark:bg-white/10 text-slate-500 dark:text-slate-400 text-[10px] font-bold flex items-center justify-center hover:bg-slate-300 dark:hover:bg-white/20 transition-colors">?</button>
                    <div class="absolute right-0 top-7 z-20 w-72 p-3 rounded-xl bg-slate-800 text-white text-[11px] leading-relaxed shadow-2xl border border-white/10 opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-150">
                        <p class="font-semibold mb-1 text-violet-300">Apa arti grafik ini?</p>
                        <p class="mb-2"><span class="text-violet-400 font-medium">Avg Depth</span> = rata-rata chapter yang dibaca per user aktif dalam satu hari. Dihitung dari <code class="bg-white/10 px-1 rounded">total reads ÷ jumlah reader unik</code>.</p>
                        <p class="mb-2">Nilai <span class="text-green-400 font-medium">≥ 5</span> menunjukkan user sangat engaged — mereka membaca banyak chapter setiap sesi.</p>
                        <p class="mb-2">Nilai <span class="text-yellow-400 font-medium">2–4</span> = engagement moderat. Nilai <span class="text-red-400 font-medium">&lt; 2</span> = user cenderung membaca sekilas dan berhenti.</p>
                        <p class="text-slate-400">Bar abu-abu (Readers) menunjukkan berapa banyak user unik yang aktif membaca hari itu — berguna untuk membedakan apakah depth naik karena konten bagus atau karena hanya sedikit user hardcore yang membaca.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="relative h-48"><canvas id="depthChart"></canvas></div>
    </div>
</div>

{{-- Charts Row 2 --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-5">

    {{-- Avg chapters per view --}}
    <div class="glass-card p-5">
        <div class="flex items-start justify-between mb-1">
            <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Rata-rata Chapter per View</h2>
            <div class="relative group flex-shrink-0 ml-2">
                <button class="w-5 h-5 rounded-full bg-slate-200 dark:bg-white/10 text-slate-500 dark:text-slate-400 text-[11px] font-bold flex items-center justify-center hover:bg-slate-300 dark:hover:bg-white/20 transition-colors cursor-default">?</button>
                <div class="absolute right-0 top-7 z-20 w-64 p-3 rounded-xl bg-slate-800 dark:bg-slate-900 text-white text-xs leading-relaxed shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-150">
                    <p class="font-semibold mb-1 text-slate-200">Apa arti metrik ini?</p>
                    <p class="text-slate-300">Menghitung berapa chapter rata-rata yang dibaca setiap kali user membuka konten dalam 30 hari terakhir.</p>
                    <p class="text-slate-400 mt-1.5"><span class="text-white font-mono">Nilai tinggi (≥3)</span> = user sangat engaged, lanjut baca banyak chapter.</p>
                    <p class="text-slate-400 mt-1"><span class="text-white font-mono">Nilai rendah (≤1)</span> = user hanya lihat sekilas lalu pergi — cek kualitas chapter pertama.</p>
                </div>
            </div>
        </div>
        <p class="text-[11px] text-slate-400 dark:text-slate-500 mb-4">Chapter dibaca ÷ jumlah kunjungan (30 hari)</p>
        <div class="relative h-56"><canvas id="vtcChart"></canvas></div>
    </div>

    {{-- Chapter completion funnel --}}
    <div class="glass-card p-5">
        <div class="flex items-start justify-between mb-0.5">
            <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Chapter Completion Funnel</h2>
            <div class="relative group flex-shrink-0 ml-2">
                <button class="w-5 h-5 rounded-full bg-slate-200 dark:bg-white/10 text-slate-500 dark:text-slate-400 text-[11px] font-bold flex items-center justify-center hover:bg-slate-300 dark:hover:bg-white/20 transition-colors cursor-default">?</button>
                <div class="absolute right-0 top-7 z-20 w-64 p-3 rounded-xl bg-slate-800 dark:bg-slate-900 text-white text-xs leading-relaxed shadow-xl opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-150">
                    <p class="font-semibold mb-1 text-slate-200">Apa arti grafik ini?</p>
                    <p class="text-slate-300">Menunjukkan berapa persen pembaca yang bertahan di tiap chapter dibanding chapter pertama (100%).</p>
                    <p class="text-slate-400 mt-1.5"><span class="text-white font-mono">Drop tajam di chapter N</span> = ada masalah konten di chapter tersebut (terlalu panjang, plot membosankan, atau paywall).</p>
                    <p class="text-slate-400 mt-1"><span class="text-white font-mono">Kurva landai</span> = pembaca setia, konten berkualitas.</p>
                    <p class="text-slate-400 mt-1.5 italic">Data: konten dengan total reads tertinggi.</p>
                </div>
            </div>
        </div>
        <p class="text-[11px] text-slate-400 dark:text-slate-500 mb-4">Konten: <span class="font-medium text-slate-600 dark:text-slate-300">{{ Str::limit($topContentTitle, 35) }}</span></p>
        <div class="relative h-56"><canvas id="funnelChart"></canvas></div>
    </div>
</div>

{{-- Content Performance Table --}}
<div class="flat-card">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between">
        <div>
            <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Performa Konten</h2>
            <p class="text-[11px] text-slate-400 mt-0.5">Diurutkan berdasarkan total reads</p>
        </div>
        <span class="text-[11px] font-medium text-teal-500 bg-teal-50 dark:bg-teal-500/10 px-2.5 py-1 rounded-full">Top 20</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">#</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Judul</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Views</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Reads</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Read-Through</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Subscribes</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Rating</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Chapters</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topContent as $i => $c)
                <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-5 py-3 w-10">
                        <span class="font-mono text-xs font-bold {{ $i === 0 ? 'text-amber-500' : ($i === 1 ? 'text-slate-400' : ($i === 2 ? 'text-orange-400' : 'text-slate-300 dark:text-slate-600')) }}">{{ $i + 1 }}</span>
                    </td>
                    <td class="px-5 py-3 text-xs font-medium text-slate-700 dark:text-slate-300 max-w-[200px] truncate">{{ $c->title }}</td>
                    <td class="px-5 py-3 text-right font-mono text-xs text-slate-500 dark:text-slate-400">{{ number_format($c->view_count) }}</td>
                    <td class="px-5 py-3 text-right font-mono text-xs font-semibold text-teal-600 dark:text-teal-400">{{ number_format($c->read_count) }}</td>
                    <td class="px-5 py-3 text-right">
                        <span class="font-mono text-xs {{ ($c->read_through_pct ?? 0) >= 50 ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-slate-400' }}">
                            {{ $c->read_through_pct ?? 0 }}%
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right font-mono text-xs text-slate-500">{{ number_format($c->subscribe_count) }}</td>
                    <td class="px-5 py-3 text-right font-mono text-xs text-amber-600 dark:text-amber-400">{{ $c->rating > 0 ? $c->rating : '—' }}</td>
                    <td class="px-5 py-3 text-right font-mono text-xs text-slate-400">{{ $c->chapter_count }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
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
    bodyColor: dark ? '#94a3b8' : '#64748b',
    borderColor: dark ? '#334155' : '#e2e8f0',
    borderWidth: 1, padding: 10, cornerRadius: 10
};
const scaleOpts = (pos = 'left') => ({
    position: pos,
    grid: pos === 'left' ? { color: gridColor } : { drawOnChartArea: false },
    ticks: { color: tickColor, font: { size: 10 } },
    border: { display: false }
});

// Daily reads & views
const dailyData = @json($dailyEngagement);
new Chart(document.getElementById('dailyChart'), {
    type: 'line',
    data: {
        labels: dailyData.map(d => d.date),
        datasets: [
            { label: 'Reads', data: dailyData.map(d => d.read), borderColor: dark ? '#2dd4bf' : '#0d9488', backgroundColor: dark ? 'rgba(45,212,191,0.08)' : 'rgba(13,148,136,0.08)', borderWidth: 2, pointRadius: 2, fill: true, tension: 0.4, yAxisID: 'y' },
            { label: 'Views', data: dailyData.map(d => d.view), borderColor: dark ? '#93c5fd' : '#60a5fa', backgroundColor: 'transparent', borderWidth: 1.5, pointRadius: 2, tension: 0.4, borderDash: [4,3], yAxisID: 'y1' }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, plugins: { legend: { display: false }, tooltip: ttDefaults }, scales: { x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 10, family: 'Fira Code' } }, border: { display: false } }, y: scaleOpts('left'), y1: scaleOpts('right') } }
});

// Depth trend
const depthData = @json($depthTrend);
new Chart(document.getElementById('depthChart'), {
    type: 'bar',
    data: {
        labels: depthData.map(d => d.date),
        datasets: [
            { label: 'Avg Depth', data: depthData.map(d => d.avg_depth), backgroundColor: dark ? 'rgba(167,139,250,0.5)' : 'rgba(139,92,246,0.5)', borderRadius: 4, borderSkipped: false, yAxisID: 'y' },
            { label: 'Readers', data: depthData.map(d => d.active_readers), type: 'line', borderColor: dark ? '#94a3b8' : '#cbd5e1', backgroundColor: 'transparent', borderWidth: 1.5, pointRadius: 2, tension: 0.4, borderDash: [4,3], yAxisID: 'y1' }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, plugins: { legend: { display: false }, tooltip: ttDefaults }, scales: { x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 10, family: 'Fira Code' } }, border: { display: false } }, y: scaleOpts('left'), y1: scaleOpts('right') } }
});

// VTC horizontal bar
const vtcData = @json($vtcByContent);
new Chart(document.getElementById('vtcChart'), {
    type: 'bar',
    data: {
        labels: vtcData.map(d => d.title.length > 25 ? d.title.substring(0,25)+'…' : d.title),
        datasets: [{ label: 'Avg Chapter/View', data: vtcData.map(d => parseFloat(d.avg_chapters_per_view)), backgroundColor: dark ? 'rgba(45,212,191,0.5)' : 'rgba(13,148,136,0.5)', borderRadius: 4 }]
    },
    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { ...ttDefaults, callbacks: { label: ctx => ` ${ctx.raw}× per view` } } }, scales: { x: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 10 }, callback: v => v + '×' }, border: { display: false } }, y: { grid: { display: false }, ticks: { color: tickColor, font: { size: 10 } }, border: { display: false } } } }
});

// Chapter funnel
const funnelData = @json($chapterFunnel);
new Chart(document.getElementById('funnelChart'), {
    type: 'line',
    data: {
        labels: funnelData.map(d => 'Ch.' + d.sequence),
        datasets: [{ label: '% of Ch.1', data: funnelData.map(d => d.pct), borderColor: dark ? '#818cf8' : '#6366f1', backgroundColor: dark ? 'rgba(129,140,248,0.1)' : 'rgba(99,102,241,0.08)', borderWidth: 2, pointRadius: 3, fill: true, tension: 0.3 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { ...ttDefaults, callbacks: { label: ctx => ` ${ctx.raw}% retention` } } }, scales: { x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 9, family: 'Fira Code' } }, border: { display: false } }, y: { min: 0, max: 100, grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 10 }, callback: v => v + '%' }, border: { display: false } } } }
});
</script>
@endpush
