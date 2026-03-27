@extends('layouts.admin')
@section('title', 'Detail Campaign')
@section('page-title', 'Detail Campaign')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.crm.campaigns.index') }}"
        class="inline-flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke Campaign History
    </a>
</div>

{{-- Campaign Info --}}
<div class="flat-card p-5 mb-5">
    <div class="flex items-start justify-between">
        <div>
            <h2 class="font-mono text-base font-semibold text-slate-800 dark:text-white">{{ $campaign->name }}</h2>
            <p class="text-xs text-slate-400 mt-1">Subject: {{ $campaign->subject }}</p>
            <p class="text-xs text-slate-400">Grup: {{ $campaign->group->name ?? '—' }} &nbsp;|&nbsp; Template: {{ $campaign->template->name ?? '—' }}</p>
        </div>
        <div class="text-right">
            @php
                $statusClass = match($campaign->status) {
                    'sent' => 'badge-paid',
                    'scheduled' => 'badge-pending',
                    'sending', 'queued' => 'badge-pending',
                    'failed' => 'badge-failed',
                    default => 'badge-expired',
                };
            @endphp
            <span class="badge {{ $statusClass }}">{{ ucfirst($campaign->status) }}</span>
            @if($campaign->scheduled_at)
                <p class="text-xs text-amber-500 mt-1 font-mono">{{ $campaign->scheduled_at->format('d/m/Y H:i') }}</p>
            @endif
            @if($campaign->sent_at)
                <p class="text-xs text-slate-400 mt-1 font-mono">Dikirim: {{ $campaign->sent_at->format('d/m/Y H:i') }}</p>
            @endif
        </div>
    </div>
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-5">
    @php
        $statCards = [
            ['label' => 'Total Penerima', 'value' => $campaign->recipient_count, 'color' => 'blue'],
            ['label' => 'Terkirim', 'value' => $stats['sent'], 'color' => 'blue'],
            ['label' => 'Delivered', 'value' => $stats['delivered'], 'color' => 'emerald'],
            ['label' => 'Dibuka', 'value' => $stats['opened'], 'color' => 'violet'],
            ['label' => 'Diklik', 'value' => $stats['clicked'], 'color' => 'amber'],
            ['label' => 'Gagal/Bounce', 'value' => $stats['bounced'] + $stats['failed'], 'color' => 'red'],
        ];
    @endphp
    @foreach($statCards as $card)
    <div class="glass-card p-4 text-center">
        <p class="font-mono text-xl font-bold text-slate-900 dark:text-white">{{ number_format($card['value']) }}</p>
        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $card['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- Log per recipient --}}
<div class="flat-card">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-white/[0.06]">
        <h3 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Log Pengiriman Per Penerima</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Email</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Nama</th>
                    <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Dikirim</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Dibuka</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Error</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-5 py-2.5 text-xs font-mono text-slate-700 dark:text-slate-200">{{ $log->recipient_email }}</td>
                    <td class="px-5 py-2.5 text-xs text-slate-500 dark:text-slate-400">{{ $log->recipient_name ?: '—' }}</td>
                    <td class="px-5 py-2.5 text-center">
                        @php
                            $lc = match($log->status) {
                                'sent', 'delivered' => 'badge-paid',
                                'opened', 'clicked' => 'badge-pending',
                                'bounced', 'failed' => 'badge-failed',
                                default => 'badge-expired',
                            };
                        @endphp
                        <span class="badge {{ $lc }}">{{ ucfirst($log->status) }}</span>
                    </td>
                    <td class="px-5 py-2.5 text-xs text-slate-400 font-mono">
                        {{ $log->sent_at ? $log->sent_at->format('d/m H:i') : '—' }}
                    </td>
                    <td class="px-5 py-2.5 text-xs text-slate-400 font-mono">
                        {{ $log->opened_at ? $log->opened_at->format('d/m H:i') : '—' }}
                    </td>
                    <td class="px-5 py-2.5 text-xs text-red-500 max-w-xs truncate">
                        {{ $log->error_message ?: '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-6 text-center text-sm text-slate-400">Belum ada log pengiriman.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($logs->hasPages())
<div class="mt-4">{{ $logs->links() }}</div>
@endif
@endsection
