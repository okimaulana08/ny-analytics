@extends('layouts.admin')
@section('title', 'Trigger WA Otomatis')
@section('page-title', 'Trigger WA Otomatis')

@section('content')
<div class="mb-4 flex items-center justify-between">
    <p class="text-sm text-slate-500 dark:text-slate-400">Pesan WhatsApp dikirim otomatis berdasarkan event user</p>
    <a href="{{ route('admin.crm.wa-triggers.create') }}"
        class="h-9 px-4 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-green-500/20 transition-all duration-150 inline-flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Trigger
    </a>
</div>

@if(session('success'))
<div class="mb-4 rounded-xl bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 px-4 py-3 text-sm text-green-700 dark:text-green-400">
    {{ session('success') }}
</div>
@endif

<div class="flat-card">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Nama Trigger</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Jenis</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Kondisi</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Delay</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Cooldown</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Template</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Terkirim</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($triggers as $trigger)
            <tr class="group border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-3">
                    <div class="font-medium text-slate-800 dark:text-white text-xs">{{ $trigger->name }}</div>
                </td>
                <td class="px-5 py-3">
                    @php
                        $typeColors = [
                            'pending_payment' => 'background:#fef3c7;color:#92400e',
                            'expiry_reminder' => 'background:#dbeafe;color:#1e40af',
                        ];
                    @endphp
                    <span class="badge" style="{{ $typeColors[$trigger->type] ?? '' }}">
                        {{ $trigger->typeLabel() }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    @php
                        $condColors = [
                            'invoice_active'  => 'background:#d1fae5;color:#065f46',
                            'invoice_expired' => 'background:#fee2e2;color:#991b1b',
                            'before_expiry'   => 'background:#e0f2fe;color:#0369a1',
                            'after_expiry'    => 'background:#fae8ff;color:#7e22ce',
                        ];
                    @endphp
                    @if($trigger->condition)
                        <span class="badge" style="{{ $condColors[$trigger->condition] ?? '' }}">
                            {{ $trigger->conditionLabel() }}
                        </span>
                    @else
                        <span class="text-xs text-slate-400">—</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-center text-xs text-slate-500 dark:text-slate-400 font-mono">
                    {{ $trigger->delayLabel() }}
                </td>
                <td class="px-5 py-3 text-center text-xs text-slate-500 dark:text-slate-400 font-mono">
                    {{ $trigger->cooldown_hours }}j
                </td>
                <td class="px-5 py-3 text-center text-xs text-slate-500 dark:text-slate-400">
                    {{ $trigger->templates_count }} template
                </td>
                <td class="px-5 py-3 text-center font-mono text-xs text-slate-600 dark:text-slate-300">
                    {{ number_format($trigger->logs_count) }}
                </td>
                <td class="px-5 py-3 text-center">
                    <form action="{{ route('admin.crm.wa-triggers.toggle', $trigger) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none cursor-pointer
                            {{ $trigger->is_active ? 'bg-green-600' : 'bg-slate-200 dark:bg-slate-700' }}">
                            <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform
                                {{ $trigger->is_active ? 'translate-x-4.5' : 'translate-x-0.5' }}"></span>
                        </button>
                    </form>
                </td>
                <td class="px-5 py-3 text-right">
                    <div class="flex items-center justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                        <a href="{{ route('admin.crm.wa-triggers.edit', $trigger) }}"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form action="{{ route('admin.crm.wa-triggers.destroy', $trigger) }}" method="POST"
                            onsubmit="return confirm('Hapus trigger ini?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-5 py-8 text-center text-sm text-slate-400">
                    Belum ada trigger WA. <a href="{{ route('admin.crm.wa-triggers.create') }}" class="text-green-500 hover:underline">Buat trigger pertama</a>.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 flat-card p-4">
    <p class="text-xs text-slate-500 dark:text-slate-400">
        <span class="font-semibold text-slate-600 dark:text-slate-300">Catatan:</span>
        Trigger dijalankan otomatis setiap 5 menit via scheduler (<code class="font-mono bg-slate-100 dark:bg-white/5 px-1 rounded">php artisan wa:run-triggers</code>).
        Pastikan <code class="font-mono bg-slate-100 dark:bg-white/5 px-1 rounded">php artisan schedule:run</code> dikonfigurasi di cron server.
    </p>
</div>
@endsection
