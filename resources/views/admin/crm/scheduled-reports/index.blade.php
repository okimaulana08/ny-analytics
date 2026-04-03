@extends('layouts.admin')
@section('title', 'Scheduled Reports')
@section('page-title', 'Scheduled Reports — Laporan Email Otomatis')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-sm text-emerald-700 dark:text-emerald-300">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-sm text-red-700 dark:text-red-300">
    {{ session('error') }}
</div>
@endif

<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-slate-500 dark:text-slate-400">Laporan dikirim otomatis ke email sesuai jadwal (jam 08:00 WIB)</p>
    <a href="{{ route('admin.crm.scheduled-reports.create') }}"
       class="h-9 px-4 rounded-xl text-xs font-medium bg-blue-600 hover:bg-blue-700 text-white transition flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Buat Scheduled Report
    </a>
</div>

<div class="flat-card overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-100 dark:border-white/[0.06]">
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Nama</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Tipe</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Frekuensi</th>
                <th class="text-center px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Penerima</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Terakhir Dikirim</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Jadwal Berikutnya</th>
                <th class="text-center px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50 dark:divide-white/[0.03]">
            @forelse($reports as $report)
            <tr class="hover:bg-slate-50/60 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-3.5">
                    <div class="font-medium text-slate-800 dark:text-slate-200">{{ $report->name }}</div>
                    @if($report->description)
                        <div class="text-xs text-slate-400 truncate max-w-xs">{{ $report->description }}</div>
                    @endif
                </td>
                <td class="px-5 py-3.5">
                    <span class="badge badge-pending">{{ $report->typeLabel() }}</span>
                </td>
                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-400 text-xs">{{ $report->frequencyLabel() }}</td>
                <td class="px-5 py-3.5 text-center font-mono text-slate-700 dark:text-slate-300">{{ count($report->recipients) }}</td>
                <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400">
                    {{ $report->last_sent_at ? $report->last_sent_at->timezone('Asia/Jakarta')->format('d M Y H:i') : '—' }}
                </td>
                <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400">
                    {{ $report->next_run_at ? $report->next_run_at->timezone('Asia/Jakarta')->format('d M Y') : '—' }}
                </td>
                <td class="px-5 py-3.5 text-center">
                    <span class="badge {{ $report->is_active ? 'badge-paid' : 'badge-expired' }}">
                        {{ $report->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <form action="{{ route('admin.crm.scheduled-reports.send-now', $report) }}" method="POST"
                              onsubmit="return confirm('Kirim report ini sekarang?')">
                            @csrf
                            <button type="submit" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">Kirim Sekarang</button>
                        </form>
                        <span class="text-slate-200 dark:text-white/10">|</span>
                        <a href="{{ route('admin.crm.scheduled-reports.edit', $report) }}"
                           class="text-xs text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">Edit</a>
                        <form action="{{ route('admin.crm.scheduled-reports.destroy', $report) }}" method="POST"
                              onsubmit="return confirm('Hapus scheduled report ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 dark:text-red-400 hover:underline">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-5 py-12 text-center text-slate-400 text-sm">
                    Belum ada scheduled report. <a href="{{ route('admin.crm.scheduled-reports.create') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Buat yang pertama</a>.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 px-4 py-3 rounded-xl bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 text-xs text-blue-600 dark:text-blue-400 flex items-start gap-2">
    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span>Laporan dikirim otomatis setiap hari jam 08:00 WIB via <code class="font-mono">php artisan reports:send-scheduled</code>. Pastikan Laravel Scheduler berjalan di server.</span>
</div>

@endsection
