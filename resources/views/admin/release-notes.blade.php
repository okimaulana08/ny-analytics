@extends('layouts.admin')
@section('title', 'Release Notes')
@section('page-title', 'Release Notes — Riwayat Pembaruan')

@section('content')

<div class="mb-6">
    <p class="text-sm text-slate-500 dark:text-slate-400">Daftar fitur dan pembaruan yang ditambahkan ke Novelya Analytics.</p>
</div>

<div class="space-y-8" x-data="suggestionModal()">

    @foreach($releases as $release)
    <div>
        {{-- Release header --}}
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <span class="font-mono font-bold text-xl text-slate-800 dark:text-white">{{ $release['version'] }}</span>
            @if($release['tag'] === 'latest')
                <span class="badge badge-paid text-[11px]">Latest</span>
            @endif
            <span class="text-xs text-slate-400 font-mono">{{ \Carbon\Carbon::parse($release['date'])->format('d M Y') }}</span>
            <div class="flex-1 h-px bg-slate-200 dark:bg-white/[0.06]"></div>
        </div>

        <h2 class="font-mono font-semibold text-slate-700 dark:text-slate-300 text-base mb-4">{{ $release['title'] }}</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach($release['features'] as $feature)
            <div class="flat-card p-4 flex flex-col gap-3">
                {{-- Feature name + dot --}}
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 mt-0.5"></span>
                        <h3 class="font-mono font-semibold text-sm text-slate-800 dark:text-white leading-snug">{{ $feature['name'] }}</h3>
                    </div>
                </div>

                {{-- Description --}}
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed flex-1 ml-4">{{ $feature['description'] }}</p>

                {{-- Actions row --}}
                <div class="flex items-center gap-2 ml-4">
                    @if(!empty($feature['suggestions']))
                    <button @click="open({{ json_encode($feature['name']) }}, {{ json_encode($feature['suggestions']) }})"
                        class="h-7 px-2.5 inline-flex items-center gap-1.5 text-[11px] font-medium text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        {{ count($feature['suggestions']) }} Saran
                    </button>
                    @endif

                    @if($feature['route'])
                    <a href="{{ route($feature['route']) }}"
                       class="h-7 px-2.5 inline-flex items-center gap-1.5 text-[11px] font-medium text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-500/30 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-500/10 transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Buka
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    {{-- Suggestion Modal --}}
    <div x-show="show" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @keydown.escape.window="show = false">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="show = false"></div>
        <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-white/[0.06]">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    <div>
                        <h3 class="font-mono font-semibold text-sm text-slate-800 dark:text-white" x-text="title"></h3>
                        <p class="text-xs text-slate-400">Saran pengembangan ke depan</p>
                    </div>
                </div>
                <button @click="show = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-white/[0.06] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            {{-- Body --}}
            <ul class="px-5 py-4 space-y-3">
                <template x-for="(s, i) in suggestions" :key="i">
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 w-5 h-5 rounded-full bg-amber-100 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center text-[10px] font-bold flex-shrink-0" x-text="i + 1"></span>
                        <span class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed" x-text="s"></span>
                    </li>
                </template>
            </ul>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
function suggestionModal() {
    return {
        show: false,
        title: '',
        suggestions: [],
        open(title, suggestions) {
            this.title = title;
            this.suggestions = suggestions;
            this.show = true;
        },
    };
}
</script>
@endpush
