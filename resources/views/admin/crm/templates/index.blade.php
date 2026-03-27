@extends('layouts.admin')
@section('title', 'Email Templates')
@section('page-title', 'Email Templates')

@section('content')
<div class="mb-4 flex items-center justify-between">
    <p class="text-sm text-slate-500 dark:text-slate-400">Kelola template email yang bisa digunakan kembali</p>
    <a href="{{ route('admin.crm.templates.create') }}"
        class="h-9 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-blue-500/20 transition-all duration-150 inline-flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Template
    </a>
</div>

<div class="flat-card">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Nama Template</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Subject</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Campaigns</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Dibuat</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($templates as $template)
            <tr class="group border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-3">
                    <div class="font-medium text-slate-800 dark:text-white text-xs">{{ $template->name }}</div>
                    @if($template->preview_text)
                        <div class="text-slate-400 text-[11px] mt-0.5 truncate max-w-xs">{{ $template->preview_text }}</div>
                    @endif
                </td>
                <td class="px-5 py-3 text-xs text-slate-600 dark:text-slate-300 max-w-xs truncate">{{ $template->subject }}</td>
                <td class="px-5 py-3 text-center font-mono text-xs text-slate-600 dark:text-slate-300">{{ $template->campaigns_count }}</td>
                <td class="px-5 py-3 text-xs text-slate-400 font-mono">{{ $template->created_at->format('d/m/Y') }}</td>
                <td class="px-5 py-3 text-right">
                    <div class="flex items-center justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                        <a href="{{ route('admin.crm.templates.preview', $template) }}" target="_blank"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-violet-500 hover:bg-violet-50 dark:hover:bg-violet-500/10 transition-colors" title="Preview">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        <a href="{{ route('admin.crm.templates.edit', $template) }}"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form action="{{ route('admin.crm.templates.destroy', $template) }}" method="POST"
                            onsubmit="return confirm('Hapus template ini?')">
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
                <td colspan="5" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada template email. <a href="{{ route('admin.crm.templates.create') }}" class="text-blue-500 hover:underline">Buat sekarang</a>.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 p-4 bg-blue-50 dark:bg-blue-500/10 rounded-xl border border-blue-200 dark:border-blue-500/20">
    <p class="text-xs text-blue-700 dark:text-blue-400 font-semibold mb-1">Merge Tags yang Didukung</p>
    <p class="text-xs text-blue-600 dark:text-blue-300 font-mono">@{{name}} &nbsp; @{{email}} &nbsp; @{{expiry_date}} &nbsp; @{{plan_name}} &nbsp; @{{app_url}}</p>
</div>
@endsection
