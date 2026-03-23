@extends('layouts.admin')
@section('title', 'User Admin')
@section('page-title', 'User Admin')

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500 dark:text-slate-400">Kelola akun admin yang dapat mengakses dashboard ini.</p>
        <a href="{{ route('admin.users.create') }}"
           class="inline-flex items-center gap-2 h-9 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl shadow-sm shadow-blue-500/20 transition-all duration-150 hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah User
        </a>
    </div>

    <div class="flat-card">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/[0.06]">
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Nama</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Email</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Dibuat</th>
                    <th class="px-5 py-3.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr class="border-b border-slate-50 dark:border-white/[0.03] hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors group">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $user->name }}</div>
                                @if($user->id === session('admin_user.id'))
                                    <div class="text-[11px] text-blue-500 font-medium">Anda</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 font-mono text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</td>
                    <td class="px-5 py-3.5">
                        @if($user->is_active)
                            <span class="badge badge-paid">Aktif</span>
                        @else
                            <span class="badge badge-expired">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 font-mono text-xs text-slate-400 dark:text-slate-500">
                        {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td class="px-5 py-3.5">
                        @if($user->id !== session('admin_user.id'))
                        <div class="flex items-center justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                            <form action="{{ route('admin.users.toggle', $user) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                    onclick="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} user ini?')"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-amber-500 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-colors cursor-pointer">
                                    @if($user->is_active)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </button>
                            </form>
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" title="Hapus"
                                    onclick="return confirm('Hapus user {{ addslashes($user->name) }}?')"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                        @else
                        <span class="text-[11px] text-slate-300 dark:text-slate-600 italic px-2">akun Anda</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-14 text-center">
                        <div class="flex flex-col items-center gap-2 text-slate-400 dark:text-slate-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <p class="text-sm">Belum ada user admin.</p>
                            <a href="{{ route('admin.users.create') }}" class="text-sm text-blue-500 hover:underline">Tambah sekarang</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->count() > 0)
        <div class="px-5 py-3 border-t border-slate-100 dark:border-white/[0.06] text-xs text-slate-400 dark:text-slate-500">
            {{ $users->count() }} user terdaftar
        </div>
        @endif
    </div>
</div>
@endsection
