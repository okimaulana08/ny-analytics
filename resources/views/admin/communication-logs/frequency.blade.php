@extends('layouts.admin')
@section('title', 'Frequency Monitor')
@section('page-title', 'Frequency Monitor — Frekuensi Kontak User')

@section('content')

{{-- Tab nav --}}
<div class="flex gap-1 mb-5 p-1 bg-slate-100 dark:bg-white/[0.04] rounded-xl w-fit">
    <a href="{{ route('admin.communication-logs') }}"
        class="h-8 px-4 rounded-lg text-xs font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">
        Timeline
    </a>
    <a href="{{ route('admin.communication-logs.frequency') }}"
        class="h-8 px-4 rounded-lg text-xs font-semibold bg-white dark:bg-slate-800 text-slate-800 dark:text-white shadow-sm transition-all">
        Frequency Monitor
    </a>
</div>

{{-- Threshold info --}}
<div class="glass-card p-4 mb-5">
    <div class="flex flex-wrap gap-5 items-center justify-between">
        <div class="flex flex-wrap gap-4">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-red-500 flex-shrink-0"></span>
                <span class="text-xs text-slate-600 dark:text-slate-300">
                    Over limit: <span class="font-semibold">&gt; {{ $threshold7d }}x / 7 hari</span> atau <span class="font-semibold">&gt; {{ $threshold30d }}x / 30 hari</span>
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-amber-400 flex-shrink-0"></span>
                <span class="text-xs text-slate-600 dark:text-slate-300">
                    Mendekati limit: &ge; <span class="font-semibold">{{ (int) ceil($threshold7d * 0.7) }}x / 7 hari</span>
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-400 flex-shrink-0"></span>
                <span class="text-xs text-slate-600 dark:text-slate-300">Normal</span>
            </div>
        </div>
        <p class="text-[11px] text-slate-400">
            Threshold diatur via <code class="font-mono bg-slate-100 dark:bg-white/5 px-1 rounded">MAX_COMMS_7D</code> dan <code class="font-mono bg-slate-100 dark:bg-white/5 px-1 rounded">MAX_COMMS_30D</code> di <code class="font-mono bg-slate-100 dark:bg-white/5 px-1 rounded">.env</code>
        </p>
    </div>
</div>

{{-- Filters --}}
<div class="glass-card p-4 mb-5">
    <form method="GET" action="{{ route('admin.communication-logs.frequency') }}" class="flex flex-wrap gap-3 items-end">

        <div class="flex-1 min-w-48">
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Cari User</label>
            <input type="text" name="search" value="{{ $search }}"
                placeholder="Nama, email, nomor telepon..."
                class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition">
        </div>

        <div class="flex items-end gap-2">
            <label class="flex items-center gap-2 h-9 cursor-pointer">
                <input type="checkbox" name="only_over" value="1" {{ $onlyOver ? 'checked' : '' }}
                    class="w-4 h-4 rounded text-red-500 border-slate-300 cursor-pointer focus:ring-red-500">
                <span class="text-xs font-medium text-slate-600 dark:text-slate-300">Hanya tampilkan yang over/mendekati limit</span>
            </label>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                class="h-9 px-4 rounded-xl text-xs font-medium bg-blue-600 hover:bg-blue-700 text-white transition">
                Filter
            </button>
            <a href="{{ route('admin.communication-logs.frequency') }}"
               class="h-9 px-4 rounded-xl text-xs font-medium border border-slate-200 dark:border-white/10 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/[0.04] transition flex items-center">
                Reset
            </a>
        </div>
    </form>
</div>

{{-- Stats bar --}}
<div class="flex items-center gap-4 mb-3 px-1">
    @php
        $overCount = $users->where('alert', 'red')->count();
        $warnCount = $users->where('alert', 'yellow')->count();
    @endphp
    <p class="text-xs text-slate-400">
        <span class="font-semibold text-slate-600 dark:text-slate-300">{{ number_format($users->count()) }}</span> user ditemukan
    </p>
    @if($overCount > 0)
        <span class="text-xs font-medium text-red-600 dark:text-red-400 flex items-center gap-1">
            <span class="w-2 h-2 rounded-full bg-red-500"></span>
            {{ $overCount }} over limit
        </span>
    @endif
    @if($warnCount > 0)
        <span class="text-xs font-medium text-amber-600 dark:text-amber-400 flex items-center gap-1">
            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            {{ $warnCount }} mendekati limit
        </span>
    @endif
</div>

{{-- Table --}}
<div class="flat-card overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-100 dark:border-white/[0.06]">
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">User</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Kontak</th>
                <th class="text-center px-3 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                    <div>Email</div><div class="font-normal normal-case text-[10px]">7 hari</div>
                </th>
                <th class="text-center px-3 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                    <div>WA</div><div class="font-normal normal-case text-[10px]">7 hari</div>
                </th>
                <th class="text-center px-3 py-3 text-xs font-semibold text-blue-500 uppercase tracking-wider whitespace-nowrap">
                    <div>Total</div><div class="font-normal normal-case text-[10px]">7 hari</div>
                </th>
                <th class="text-center px-3 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                    <div>Email</div><div class="font-normal normal-case text-[10px]">30 hari</div>
                </th>
                <th class="text-center px-3 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                    <div>WA</div><div class="font-normal normal-case text-[10px]">30 hari</div>
                </th>
                <th class="text-center px-3 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                    <div>Total</div><div class="font-normal normal-case text-[10px]">30 hari</div>
                </th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50 dark:divide-white/[0.03]">
            @forelse($users as $user)
            @php
                $rowBg = match($user['alert']) {
                    'red'    => 'bg-red-50/50 dark:bg-red-500/5',
                    'yellow' => 'bg-amber-50/50 dark:bg-amber-500/5',
                    default  => '',
                };
                $totalColor7 = match($user['alert']) {
                    'red'    => 'text-red-600 dark:text-red-400 font-bold',
                    'yellow' => 'text-amber-600 dark:text-amber-400 font-semibold',
                    default  => 'text-slate-700 dark:text-slate-300',
                };
            @endphp
            <tr class="hover:bg-slate-50/60 dark:hover:bg-white/[0.02] transition-colors {{ $rowBg }}">

                {{-- User --}}
                <td class="px-5 py-3.5">
                    <div class="text-sm font-medium text-slate-800 dark:text-white">
                        {{ $user['name'] ?: '—' }}
                    </div>
                    @if($user['email'])
                        <div class="text-xs text-slate-400 font-mono">{{ $user['email'] }}</div>
                    @endif
                </td>

                {{-- Kontak --}}
                <td class="px-5 py-3.5">
                    @if($user['phone'])
                        <span class="text-xs font-mono text-slate-600 dark:text-slate-300">{{ $user['phone'] }}</span>
                    @else
                        <span class="text-xs text-slate-300 dark:text-slate-600">—</span>
                    @endif
                </td>

                {{-- Email 7d --}}
                <td class="px-3 py-3.5 text-center">
                    <span class="text-sm font-mono {{ $user['email_7d'] > 0 ? 'text-slate-700 dark:text-slate-300' : 'text-slate-300 dark:text-slate-600' }}">
                        {{ $user['email_7d'] }}
                    </span>
                </td>

                {{-- WA 7d --}}
                <td class="px-3 py-3.5 text-center">
                    <span class="text-sm font-mono {{ $user['wa_7d'] > 0 ? 'text-slate-700 dark:text-slate-300' : 'text-slate-300 dark:text-slate-600' }}">
                        {{ $user['wa_7d'] }}
                    </span>
                </td>

                {{-- Total 7d --}}
                <td class="px-3 py-3.5 text-center">
                    <span class="text-sm font-mono {{ $totalColor7 }}">{{ $user['total_7d'] }}</span>
                </td>

                {{-- Email 30d --}}
                <td class="px-3 py-3.5 text-center">
                    <span class="text-sm font-mono {{ $user['email_30d'] > 0 ? 'text-slate-600 dark:text-slate-400' : 'text-slate-300 dark:text-slate-600' }}">
                        {{ $user['email_30d'] }}
                    </span>
                </td>

                {{-- WA 30d --}}
                <td class="px-3 py-3.5 text-center">
                    <span class="text-sm font-mono {{ $user['wa_30d'] > 0 ? 'text-slate-600 dark:text-slate-400' : 'text-slate-300 dark:text-slate-600' }}">
                        {{ $user['wa_30d'] }}
                    </span>
                </td>

                {{-- Total 30d --}}
                <td class="px-3 py-3.5 text-center">
                    <span class="text-sm font-mono text-slate-600 dark:text-slate-400">{{ $user['total_30d'] }}</span>
                </td>

                {{-- Alert --}}
                <td class="px-4 py-3.5 text-center">
                    @if($user['alert'] === 'red')
                        <span class="badge text-[10px]" style="background:#fee2e2;color:#991b1b">Over Limit</span>
                    @elseif($user['alert'] === 'yellow')
                        <span class="badge text-[10px]" style="background:#fef3c7;color:#92400e">Mendekati</span>
                    @else
                        <span class="badge text-[10px]" style="background:#d1fae5;color:#065f46">Normal</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-5 py-14 text-center text-slate-400 text-sm">
                    Tidak ada data dalam 30 hari terakhir.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 flat-card p-4">
    <p class="text-xs text-slate-500 dark:text-slate-400">
        <span class="font-semibold text-slate-600 dark:text-slate-300">Catatan:</span>
        Data mencakup Email Trigger Otomatis, Email Broadcast, dan WA Trigger Otomatis dalam 30 hari terakhir.
        WA Notifikasi (pending/paid) tidak dihitung karena berbasis transaksi, bukan frekuensi kontak langsung.
        Untuk mengubah threshold, set <code class="font-mono bg-slate-100 dark:bg-white/5 px-1 rounded">MAX_COMMS_7D</code> dan <code class="font-mono bg-slate-100 dark:bg-white/5 px-1 rounded">MAX_COMMS_30D</code> di file <code class="font-mono bg-slate-100 dark:bg-white/5 px-1 rounded">.env</code>.
    </p>
</div>

@endsection
