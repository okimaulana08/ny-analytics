{{--
  Usage: @include('admin.partials.pagination', [
      'page'       => $page,
      'totalPages' => $totalPages,
      'total'      => $total,
      'perPage'    => $perPage,
      'param'      => 'page',   // query param name
  ])
--}}
@if($total > 0)
<div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 border-t border-slate-100 dark:border-white/[0.05]">

    {{-- Count info --}}
    <p class="text-xs text-slate-400">
        {{ number_format(($page - 1) * $perPage + 1) }}–{{ number_format(min($page * $perPage, $total)) }}
        <span class="text-slate-300 dark:text-slate-600 mx-1">dari</span>
        <span class="font-medium text-slate-500 dark:text-slate-400">{{ number_format($total) }}</span>
    </p>

    @if($totalPages > 1)
    <div class="flex items-center gap-1">

        {{-- Prev --}}
        @if($page > 1)
        <a href="{{ request()->fullUrlWithQuery([$param => $page - 1]) }}"
           class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06] transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        @else
        <span class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 dark:text-slate-700 cursor-not-allowed">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </span>
        @endif

        {{-- First page --}}
        @php $rangeStart = max(1, $page - 2); $rangeEnd = min($totalPages, $page + 2); @endphp
        @if($rangeStart > 1)
            <a href="{{ request()->fullUrlWithQuery([$param => 1]) }}"
               class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-mono font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06] transition-colors">1</a>
            @if($rangeStart > 2)
            <span class="text-slate-300 dark:text-slate-600 text-xs font-mono px-0.5">…</span>
            @endif
        @endif

        {{-- Page numbers --}}
        @for($i = $rangeStart; $i <= $rangeEnd; $i++)
        <a href="{{ request()->fullUrlWithQuery([$param => $i]) }}"
           class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-mono font-medium transition-colors
                  {{ $i === $page
                      ? 'bg-blue-600 text-white shadow-sm'
                      : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06]' }}">
            {{ $i }}
        </a>
        @endfor

        {{-- Last page --}}
        @if($rangeEnd < $totalPages)
            @if($rangeEnd < $totalPages - 1)
            <span class="text-slate-300 dark:text-slate-600 text-xs font-mono px-0.5">…</span>
            @endif
            <a href="{{ request()->fullUrlWithQuery([$param => $totalPages]) }}"
               class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-mono font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06] transition-colors">{{ $totalPages }}</a>
        @endif

        {{-- Next --}}
        @if($page < $totalPages)
        <a href="{{ request()->fullUrlWithQuery([$param => $page + 1]) }}"
           class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-white/[0.06] transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        @else
        <span class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 dark:text-slate-700 cursor-not-allowed">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </span>
        @endif

    </div>
    @endif
</div>
@endif
