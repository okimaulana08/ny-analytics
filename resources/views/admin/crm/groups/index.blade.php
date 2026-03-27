@extends('layouts.admin')
@section('title', 'Email Groups')
@section('page-title', 'Email Groups')

@section('content')
<div class="mb-4 flex items-center justify-between">
    <p class="text-sm text-slate-500 dark:text-slate-400">Kelola grup penerima email</p>
    <a href="{{ route('admin.crm.groups.create') }}"
        class="h-9 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-blue-500/20 transition-all duration-150 inline-flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Grup
    </a>
</div>

<div class="flat-card">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-100 dark:border-white/[0.05]">
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Nama Grup</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tipe</th>
                <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Filter</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Anggota</th>
                <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Campaigns</th>
                <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($groups as $group)
            <tr class="group border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-3">
                    <div class="font-medium text-slate-800 dark:text-white text-xs">{{ $group->name }}</div>
                    @if($group->description)
                        <div class="text-slate-400 text-[11px] mt-0.5">{{ $group->description }}</div>
                    @endif
                </td>
                <td class="px-5 py-3">
                    @if($group->type === 'static')
                        <span class="badge" style="background:#e0f2fe;color:#0369a1">Static</span>
                    @else
                        <span class="badge" style="background:#f0fdf4;color:#15803d">Dinamis</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-xs text-slate-500 dark:text-slate-400 font-mono">
                    @if($group->type === 'dynamic')
                        {{ $group->criteria['filter'] ?? '—' }}
                        @if(!empty($group->criteria['params']))
                            <span class="text-slate-400">({{ implode(', ', array_map(fn($k, $v) => "$k=$v", array_keys($group->criteria['params']), $group->criteria['params'])) }})</span>
                        @endif
                    @else
                        —
                    @endif
                </td>
                <td class="px-5 py-3 text-center font-mono text-xs text-slate-600 dark:text-slate-300">
                    @if($group->type === 'static')
                        {{ $group->members_count }}
                    @else
                        <span class="text-slate-400">dynamic</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-center font-mono text-xs text-slate-600 dark:text-slate-300">{{ $group->campaigns_count }}</td>
                <td class="px-5 py-3 text-right">
                    <div class="flex items-center justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                        <a href="{{ route('admin.crm.groups.edit', $group) }}"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form action="{{ route('admin.crm.groups.destroy', $group) }}" method="POST"
                            onsubmit="return confirm('Hapus grup ini?')">
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
                <td colspan="6" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada grup email.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
