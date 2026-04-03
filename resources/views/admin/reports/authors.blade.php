@extends('layouts.admin')
@section('title', 'Author Analytics')
@section('page-title', 'Author Analytics — Performa per Penulis')

@section('content')

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <p class="text-sm text-slate-500 dark:text-slate-400">Statistik performa konten per penulis terpublikasi</p>
    {{-- Sort tabs (same pill style as content report) --}}
    <div class="flex items-center gap-1 p-1 rounded-xl bg-slate-100 dark:bg-white/[0.04]">
        @foreach(['reads' => 'Total Baca', 'views' => 'Total View', 'rating' => 'Rating', 'readers' => 'Unique Readers', 'content' => 'Jml Konten'] as $key => $label)
            <a href="{{ request()->fullUrlWithQuery(['sort' => $key]) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all {{ $sort === $key ? 'bg-white dark:bg-white/10 text-slate-800 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>

<div class="flat-card overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-100 dark:border-white/[0.06]">
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">#</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Penulis</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Konten</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Baca</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Total View</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Unique Readers</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Avg Rating</th>
                <th class="text-center px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50 dark:divide-white/[0.03]">
            @forelse($authors as $i => $author)
            <tr class="hover:bg-slate-50/60 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-3.5 text-slate-400 text-xs">{{ $i + 1 }}</td>
                <td class="px-5 py-3.5">
                    <div class="font-medium text-slate-800 dark:text-slate-200">{{ $author->name }}</div>
                    <div class="text-xs text-slate-400">{{ $author->email }}</div>
                </td>
                <td class="px-5 py-3.5 text-right font-mono text-slate-700 dark:text-slate-300">{{ number_format($author->content_count) }}</td>
                <td class="px-5 py-3.5 text-right font-mono text-teal-600 dark:text-teal-400 font-semibold">{{ number_format($author->total_reads) }}</td>
                <td class="px-5 py-3.5 text-right font-mono text-blue-600 dark:text-blue-400">{{ number_format($author->total_views) }}</td>
                <td class="px-5 py-3.5 text-right font-mono text-slate-700 dark:text-slate-300">{{ number_format($author->unique_readers) }}</td>
                <td class="px-5 py-3.5 text-right">
                    @if($author->avg_rating)
                        <span class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400 font-mono font-medium">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            {{ number_format($author->avg_rating, 1) }}
                        </span>
                    @else
                        <span class="text-slate-300 dark:text-slate-600 text-xs">—</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-center">
                    <a href="{{ route('admin.reports.authors.detail', $author->id) }}"
                       class="h-7 px-3 inline-flex items-center gap-1.5 text-[11px] font-medium text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-500/30 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-500/10 transition-colors whitespace-nowrap">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Detail
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-5 py-12 text-center text-slate-400 text-sm">Tidak ada penulis dengan konten terpublikasi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
