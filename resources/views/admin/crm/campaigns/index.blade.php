@extends('layouts.admin')
@section('title', 'Campaign History')
@section('page-title', 'Campaign History')

@section('content')
<div class="mb-4 flex items-center justify-between">
    <p class="text-sm text-slate-500 dark:text-slate-400">Histori semua campaign email</p>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.crm.broadcast.create') }}"
            class="h-9 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-blue-500/20 transition-all duration-150 inline-flex items-center gap-1.5">
            + Broadcast Baru
        </a>
        <a href="{{ route('admin.crm.individual.create') }}"
            class="h-9 px-4 bg-slate-100 dark:bg-white/[0.06] hover:bg-slate-200 dark:hover:bg-white/[0.10] text-slate-700 dark:text-slate-200 text-sm font-semibold rounded-xl transition-all duration-150 inline-flex items-center gap-1.5">
            + Individual Email
        </a>
    </div>
</div>

<div class="flat-card overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Campaign</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Grup</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Penerima</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Terkirim</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Jadwal/Kirim</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($campaigns as $campaign)
            <tr class="group border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-3">
                    <a href="{{ route('admin.crm.campaigns.show', $campaign) }}"
                        class="font-medium text-slate-800 dark:text-white text-xs hover:text-blue-600 dark:hover:text-blue-400">{{ $campaign->name }}</a>
                    <div class="text-slate-400 text-[11px] mt-0.5 truncate max-w-xs">{{ $campaign->subject }}</div>
                </td>
                <td class="px-5 py-3 text-xs text-slate-500 dark:text-slate-400">
                    {{ $campaign->group->name ?? '—' }}
                </td>
                <td class="px-5 py-3 text-center">
                    @php
                        $statusClass = match($campaign->status) {
                            'sent' => 'badge-paid',
                            'scheduled' => 'badge-pending',
                            'sending', 'queued' => 'badge-pending',
                            'failed' => 'badge-failed',
                            default => 'badge-expired',
                        };
                        $statusLabel = match($campaign->status) {
                            'sent' => 'Terkirim',
                            'scheduled' => 'Terjadwal',
                            'sending' => 'Sedang Kirim',
                            'queued' => 'Antrian',
                            'failed' => 'Gagal',
                            default => ucfirst($campaign->status),
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </td>
                <td class="px-5 py-3 text-center font-mono text-xs text-slate-600 dark:text-slate-300">
                    {{ $campaign->recipient_count ?: '—' }}
                </td>
                <td class="px-5 py-3 text-center">
                    @if($campaign->recipient_count > 0)
                        <span class="font-mono text-xs text-emerald-600 dark:text-emerald-400">{{ $campaign->sent_count }}</span>
                        @if($campaign->failed_count > 0)
                            <span class="font-mono text-xs text-red-500">/{{ $campaign->failed_count }} gagal</span>
                        @endif
                    @else
                        <span class="text-xs text-slate-400">—</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-xs text-slate-400 font-mono">
                    @if($campaign->sent_at)
                        {{ $campaign->sent_at->format('d/m/Y H:i') }}
                    @elseif($campaign->scheduled_at)
                        <span class="text-amber-500">{{ $campaign->scheduled_at->format('d/m/Y H:i') }}</span>
                    @else
                        {{ $campaign->created_at->format('d/m/Y H:i') }}
                    @endif
                </td>
                <td class="px-5 py-3 text-right">
                    <div class="flex items-center justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                        <a href="{{ route('admin.crm.campaigns.show', $campaign) }}"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        @if(in_array($campaign->status, ['failed', 'sent']))
                        <form action="{{ route('admin.crm.campaigns.resend', $campaign) }}" method="POST"
                            onsubmit="return confirm('Kirim ulang campaign ini ke semua penerima?')">
                            @csrf
                            <button type="submit" title="Kirim Ulang"
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </button>
                        </form>
                        @endif
                        @if(in_array($campaign->status, ['draft', 'failed', 'scheduled']))
                        <form action="{{ route('admin.crm.campaigns.destroy', $campaign) }}" method="POST"
                            onsubmit="return confirm('Hapus campaign ini?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada campaign. <a href="{{ route('admin.crm.broadcast.create') }}" class="text-blue-500 hover:underline">Buat sekarang</a>.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($campaigns->hasPages())
<div class="mt-4">{{ $campaigns->links() }}</div>
@endif
@endsection
