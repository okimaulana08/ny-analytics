{{--
  Usage: @include('admin.partials.growth-badge', ['growth' => $value, 'hasData' => true/false])
--}}
@if($growth !== null)
    @if($growth > 0)
        <span class="inline-flex items-center gap-0.5 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 rounded-full">
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
            +{{ $growth }}%
        </span>
    @elseif($growth < 0)
        <span class="inline-flex items-center gap-0.5 text-[11px] font-semibold text-red-500 bg-red-50 dark:bg-red-500/10 px-2 py-0.5 rounded-full">
            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
            {{ $growth }}%
        </span>
    @else
        <span class="text-[11px] text-slate-400">0%</span>
    @endif
@elseif($hasData ?? false)
    <span class="text-[11px] text-slate-400 italic">baru</span>
@else
    <span class="text-slate-300 dark:text-slate-700 text-xs">—</span>
@endif
