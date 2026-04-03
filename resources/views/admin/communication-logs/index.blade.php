@extends('layouts.admin')
@section('title', 'Communication Log')
@section('page-title', 'Communication Log — Riwayat Pengiriman')

@section('content')

{{-- Tab nav --}}
<div class="flex gap-1 mb-5 p-1 bg-slate-100 dark:bg-white/[0.04] rounded-xl w-fit">
    <a href="{{ route('admin.communication-logs') }}"
        class="h-8 px-4 rounded-lg text-xs font-semibold bg-white dark:bg-slate-800 text-slate-800 dark:text-white shadow-sm transition-all">
        Timeline
    </a>
    <a href="{{ route('admin.communication-logs.frequency') }}"
        class="h-8 px-4 rounded-lg text-xs font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">
        Frequency Monitor
    </a>
</div>

{{-- Filters --}}
<div class="glass-card p-4 mb-5">
    <form method="GET" action="{{ route('admin.communication-logs') }}" class="flex flex-wrap gap-3 items-end">

        <div class="flex-1 min-w-48">
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Cari</label>
            <input type="text" name="search" value="{{ $search }}"
                placeholder="Email, nama, nomor, nama trigger..."
                class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
        </div>

        <div class="w-32">
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Channel</label>
            <select name="channel"
                class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
                <option value="">Semua</option>
                <option value="Email" {{ $channel === 'Email' ? 'selected' : '' }}>Email</option>
                <option value="WA" {{ $channel === 'WA' ? 'selected' : '' }}>WhatsApp</option>
            </select>
        </div>

        <div class="w-36">
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Tipe</label>
            <select name="sub_type"
                class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
                <option value="">Semua Tipe</option>
                <option value="Trigger" {{ $subType === 'Trigger' ? 'selected' : '' }}>Trigger Otomatis</option>
                <option value="Broadcast" {{ $subType === 'Broadcast' ? 'selected' : '' }}>Broadcast</option>
                <option value="Notifikasi" {{ $subType === 'Notifikasi' ? 'selected' : '' }}>Notifikasi</option>
            </select>
        </div>

        <div class="w-36">
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
            <input type="date" name="from" value="{{ $from }}"
                class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
        </div>

        <div class="w-36">
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
            <input type="date" name="to" value="{{ $to }}"
                class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
        </div>

        <div class="w-28">
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Per Halaman</label>
            <select name="per_page"
                class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
                @foreach([25, 50, 100] as $size)
                    <option value="{{ $size }}" {{ $perPage === $size ? 'selected' : '' }}>{{ $size }} baris</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                class="h-9 px-4 rounded-xl text-xs font-medium bg-blue-600 hover:bg-blue-700 text-white transition">
                Filter
            </button>
            <a href="{{ route('admin.communication-logs') }}"
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
        dari <span class="font-semibold text-slate-600 dark:text-slate-300">{{ number_format($logs->total()) }}</span> pengiriman
    </p>
</div>

{{-- Table --}}
<div class="flat-card overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-100 dark:border-white/[0.06]">
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Waktu</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">User / Kontak</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Channel</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Sumber</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50 dark:divide-white/[0.03]">
            @forelse($logs as $row)
            @php
                $channelBadge = match([$row->channel, $row->sub_type]) {
                    ['Email', 'Trigger'] => 'background:#dbeafe;color:#1e40af',
                    ['Email', 'Broadcast'] => 'background:#ede9fe;color:#5b21b6',
                    ['WA', 'Trigger'] => 'background:#d1fae5;color:#065f46',
                    ['WA', 'Notifikasi'] => 'background:#ccfbf1;color:#0f766e',
                    default => 'background:#f1f5f9;color:#475569',
                };
                $statusBadge = match($row->status ?? 'sent') {
                    'sent', 'delivered' => 'background:#d1fae5;color:#065f46',
                    'opened' => 'background:#dbeafe;color:#1e40af',
                    'clicked' => 'background:#ede9fe;color:#5b21b6',
                    'failed', 'bounced' => 'background:#fee2e2;color:#991b1b',
                    default => 'background:#f1f5f9;color:#475569',
                };
                $sentAt = \Carbon\Carbon::parse($row->sent_at)->timezone('Asia/Jakarta');
            @endphp
            <tr class="hover:bg-slate-50/60 dark:hover:bg-white/[0.02] transition-colors">

                {{-- Waktu --}}
                <td class="px-5 py-3.5 whitespace-nowrap">
                    <div class="text-xs text-slate-700 dark:text-slate-300">{{ $sentAt->format('d M Y') }}</div>
                    <div class="text-xs text-slate-400 font-mono">{{ $sentAt->format('H:i:s') }} WIB</div>
                </td>

                {{-- User / Kontak --}}
                <td class="px-5 py-3.5">
                    @if($row->user_name ?? null)
                        <div class="text-sm font-medium text-slate-800 dark:text-white">{{ $row->user_name }}</div>
                    @elseif($row->recipient_name ?? null)
                        <div class="text-sm font-medium text-slate-800 dark:text-white">{{ $row->recipient_name }}</div>
                    @endif
                    <div class="text-xs text-slate-400 font-mono">{{ $row->identifier }}</div>
                </td>

                {{-- Channel badge --}}
                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                    <span class="badge text-[10px]" style="{{ $channelBadge }}">
                        {{ $row->channel }} {{ $row->sub_type }}
                    </span>
                </td>

                {{-- Sumber --}}
                <td class="px-5 py-3.5">
                    <span class="text-xs text-slate-600 dark:text-slate-300">{{ $row->source_name }}</span>
                </td>

                {{-- Status --}}
                <td class="px-4 py-3.5 text-center">
                    <span class="badge text-[10px]" style="{{ $statusBadge }}">
                        {{ ucfirst($row->status ?? 'sent') }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-5 py-14 text-center text-slate-400 text-sm">
                    Tidak ada data pengiriman.
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
</div>

@endsection
