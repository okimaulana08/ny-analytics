@extends('layouts.admin')
@section('title', 'Log Admin')
@section('page-title', 'Log Admin — Riwayat Aktivitas')

@section('content')

{{-- Filters --}}
<div class="glass-card p-4 mb-5" x-data="{}">
    <form method="GET" action="{{ route('admin.activity-logs') }}" class="flex flex-wrap gap-3 items-end">

        {{-- Search --}}
        <div class="flex-1 min-w-48">
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Nama admin, email, fitur, URL..."
                class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
        </div>

        {{-- Action filter --}}
        <div class="w-36">
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Aksi</label>
            <select name="action"
                class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
                <option value="">Semua Aksi</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ $action }}</option>
                @endforeach
            </select>
        </div>

        {{-- Date from --}}
        <div class="w-36">
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
            <input type="date" name="from" value="{{ request('from') }}"
                class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
        </div>

        {{-- Date to --}}
        <div class="w-36">
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
            <input type="date" name="to" value="{{ request('to') }}"
                class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
        </div>

        {{-- Per page --}}
        <div class="w-28">
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Per Halaman</label>
            <select name="per_page"
                class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
                @foreach([10, 25, 50] as $size)
                    <option value="{{ $size }}" {{ $perPage === $size ? 'selected' : '' }}>{{ $size }} baris</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                class="h-9 px-4 rounded-xl text-xs font-medium bg-blue-600 hover:bg-blue-700 text-white transition">
                Filter
            </button>
            <a href="{{ route('admin.activity-logs') }}"
               class="h-9 px-4 rounded-xl text-xs font-medium border border-slate-200 dark:border-white/10 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/[0.04] transition flex items-center">
                Reset
            </a>
        </div>
    </form>
</div>

{{-- Stats bar --}}
<div class="flex items-center justify-between mb-3 px-1">
    <p class="text-xs text-slate-400">
        Menampilkan <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }}</span>
        dari <span class="font-semibold text-slate-600 dark:text-slate-300">{{ number_format($logs->total()) }}</span> log
    </p>
</div>

{{-- Table --}}
<div class="flat-card overflow-x-auto" x-data="payloadModal()">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-100 dark:border-white/[0.06]">
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Admin</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Fitur</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">URL</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">IP Address</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Waktu</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Detail</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50 dark:divide-white/[0.03]">
            @forelse($logs as $log)
            <tr class="hover:bg-slate-50/60 dark:hover:bg-white/[0.02] transition-colors">
                {{-- Admin --}}
                <td class="px-5 py-3.5">
                    <div class="font-medium text-slate-800 dark:text-slate-200 text-sm">{{ $log->admin_name }}</div>
                    <div class="text-xs text-slate-400">{{ $log->admin_email }}</div>
                </td>

                {{-- Feature --}}
                <td class="px-5 py-3.5">
                    <span class="text-sm text-slate-700 dark:text-slate-300 font-medium">{{ $log->feature }}</span>
                </td>

                {{-- Action badge --}}
                <td class="px-4 py-3.5 text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold {{ $log->actionBadgeClass() }}">
                        {{ $log->action }}
                    </span>
                </td>

                {{-- URL --}}
                <td class="px-5 py-3.5">
                    <span class="font-mono text-xs text-slate-500 dark:text-slate-400">
                        @if($log->http_method)
                            <span class="text-slate-400 dark:text-slate-500 mr-1">{{ $log->http_method }}</span>
                        @endif
                        {{ $log->url ?? '—' }}
                    </span>
                </td>

                {{-- IP --}}
                <td class="px-5 py-3.5">
                    <span class="font-mono text-xs text-slate-600 dark:text-slate-400">{{ $log->ip_address ?? '—' }}</span>
                </td>

                {{-- Waktu --}}
                <td class="px-5 py-3.5 whitespace-nowrap">
                    <div class="text-xs text-slate-600 dark:text-slate-400">
                        {{ $log->created_at->timezone('Asia/Jakarta')->format('d M Y') }}
                    </div>
                    <div class="text-xs text-slate-400 font-mono">
                        {{ $log->created_at->timezone('Asia/Jakarta')->format('H:i:s') }} WIB
                    </div>
                </td>

                {{-- Detail --}}
                <td class="px-4 py-3.5 text-center">
                    @if($log->payload)
                        <button @click="open({{ $log->id }}, {{ json_encode($log->feature . ' — ' . $log->action) }}, {{ json_encode($log->payload) }})"
                            class="h-7 px-2.5 inline-flex items-center gap-1 text-[11px] font-medium text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-white/10 rounded-lg hover:bg-slate-50 dark:hover:bg-white/[0.04] transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            JSON
                        </button>
                    @else
                        <span class="text-xs text-slate-300 dark:text-slate-600">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-14 text-center text-slate-400 text-sm">
                    Tidak ada log aktivitas yang ditemukan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div class="px-5 py-3.5 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between">
        <p class="text-xs text-slate-400">Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}</p>
        <div class="flex gap-1">
            @if($logs->onFirstPage())
                <span class="h-7 px-3 inline-flex items-center text-xs text-slate-300 dark:text-slate-600 border border-slate-100 dark:border-white/[0.04] rounded-lg">← Prev</span>
            @else
                <a href="{{ $logs->previousPageUrl() }}" class="h-7 px-3 inline-flex items-center text-xs text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-white/10 rounded-lg hover:bg-slate-50 dark:hover:bg-white/[0.04] transition-colors">← Prev</a>
            @endif

            @foreach($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                @if($page === $logs->currentPage())
                    <span class="h-7 w-7 inline-flex items-center justify-center text-xs font-semibold bg-blue-600 text-white rounded-lg">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="h-7 w-7 inline-flex items-center justify-center text-xs text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-white/10 rounded-lg hover:bg-slate-50 dark:hover:bg-white/[0.04] transition-colors">{{ $page }}</a>
                @endif
            @endforeach

            @if($logs->hasMorePages())
                <a href="{{ $logs->nextPageUrl() }}" class="h-7 px-3 inline-flex items-center text-xs text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-white/10 rounded-lg hover:bg-slate-50 dark:hover:bg-white/[0.04] transition-colors">Next →</a>
            @else
                <span class="h-7 px-3 inline-flex items-center text-xs text-slate-300 dark:text-slate-600 border border-slate-100 dark:border-white/[0.04] rounded-lg">Next →</span>
            @endif
        </div>
    </div>
    @endif

    {{-- Payload JSON Modal --}}
    <div x-show="show" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @keydown.escape.window="show = false">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="show = false"></div>
        <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] flex flex-col overflow-hidden">
            {{-- Modal header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-white/[0.06]">
                <div>
                    <h3 class="font-mono font-semibold text-sm text-slate-800 dark:text-white" x-text="title"></h3>
                    <p class="text-xs text-slate-400 mt-0.5">Data yang dicatat pada saat aksi dilakukan</p>
                </div>
                <button @click="show = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-white/[0.06] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            {{-- JSON body --}}
            <div class="flex-1 overflow-y-auto p-5">
                <pre class="text-xs font-mono text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-white/[0.03] rounded-xl p-4 overflow-x-auto whitespace-pre-wrap break-words leading-relaxed" x-text="json"></pre>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function payloadModal() {
    return {
        show: false,
        title: '',
        json: '',
        open(id, title, payload) {
            this.title = title;
            this.json = JSON.stringify(payload, null, 2);
            this.show = true;
        },
    };
}
</script>
@endpush
