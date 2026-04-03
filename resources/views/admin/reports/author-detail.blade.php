@extends('layouts.admin')
@section('title', $author->name . ' — Author Detail')
@section('page-title', $author->name . ' — Detail Penulis')

@section('content')

{{-- Back --}}
<div class="mb-5">
    <a href="{{ route('admin.reports.authors') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition">← Kembali ke Author Analytics</a>
</div>

{{-- Author header --}}
<div class="glass-card p-5 mb-6 flex flex-wrap items-center gap-4">
    <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-xl font-mono flex-shrink-0">
        {{ strtoupper(substr($author->name, 0, 1)) }}
    </div>
    <div class="flex-1 min-w-0">
        <h2 class="font-mono font-semibold text-slate-800 dark:text-white text-lg leading-tight">{{ $author->name }}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $author->email }}</p>
    </div>
    <div class="flex flex-wrap gap-6">
        <div class="text-center">
            <div class="text-2xl font-mono font-bold text-slate-800 dark:text-white">{{ number_format($stats->content_count) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Konten</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-mono font-bold text-slate-800 dark:text-white">{{ number_format($stats->total_reads) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Total Baca</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-mono font-bold text-slate-800 dark:text-white">{{ number_format($stats->unique_readers) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Unique Readers</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($stats->paying_readers) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Pembaca Bayar</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-mono font-bold text-amber-600 dark:text-amber-400">{{ $avgCompletion ? number_format($avgCompletion, 0) . '%' : '—' }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Avg Completion</div>
        </div>
    </div>
</div>

{{-- Content list --}}
<div class="flat-card overflow-x-auto">
    <div class="px-5 py-3.5 border-b border-slate-100 dark:border-white/[0.06]">
        <h3 class="font-mono font-semibold text-sm text-slate-700 dark:text-slate-300">Daftar Konten</h3>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-100 dark:border-white/[0.06]">
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Judul</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Chapters</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Baca Ch.1</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Baca Ch.Akhir</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Completion</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Baca</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Rating</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50 dark:divide-white/[0.03]">
            @forelse($contents as $content)
            @php
                $completion = ($content->first_ch_reads > 0 && $content->last_ch_reads !== null)
                    ? round($content->last_ch_reads / $content->first_ch_reads * 100)
                    : null;
                $completionColor = $completion === null ? 'text-slate-300 dark:text-slate-600'
                    : ($completion >= 70 ? 'text-emerald-600 dark:text-emerald-400'
                    : ($completion >= 40 ? 'text-amber-600 dark:text-amber-400'
                    : 'text-red-500 dark:text-red-400'));
            @endphp
            <tr class="hover:bg-slate-50/60 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-3.5">
                    <div class="font-medium text-slate-800 dark:text-slate-200 max-w-xs truncate">{{ $content->title }}</div>
                    @if(!$content->is_published)
                        <span class="badge badge-expired text-[10px]">Draft</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-right font-mono text-slate-600 dark:text-slate-400">{{ $content->chapter_count }}</td>
                <td class="px-5 py-3.5 text-right font-mono text-slate-700 dark:text-slate-300">{{ number_format($content->first_ch_reads ?? 0) }}</td>
                <td class="px-5 py-3.5 text-right font-mono text-slate-700 dark:text-slate-300">{{ $content->last_ch_reads !== null ? number_format($content->last_ch_reads) : '—' }}</td>
                <td class="px-5 py-3.5 text-right font-mono font-medium {{ $completionColor }}">
                    {{ $completion !== null ? $completion . '%' : '—' }}
                </td>
                <td class="px-5 py-3.5 text-right font-mono text-slate-700 dark:text-slate-300">{{ number_format($content->read_count) }}</td>
                <td class="px-5 py-3.5 text-right">
                    @if($content->rating)
                        <span class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400 font-mono text-xs">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            {{ number_format($content->rating, 1) }}
                        </span>
                    @else
                        <span class="text-slate-300 dark:text-slate-600 text-xs">—</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-right">
                    <a href="{{ route('admin.reports.chapter-dropoff') }}?content_id={{ $content->id }}&content_title={{ urlencode($content->title) }}"
                       class="h-7 px-2.5 inline-flex items-center gap-1 text-[11px] font-medium text-violet-600 dark:text-violet-400 border border-violet-200 dark:border-violet-500/30 rounded-lg hover:bg-violet-50 dark:hover:bg-violet-500/10 transition-colors whitespace-nowrap">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Drop-off
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-5 py-10 text-center text-slate-400 text-sm">Tidak ada konten.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
