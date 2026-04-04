@extends('layouts.admin')
@section('title', 'Edit User Admin')
@section('page-title', 'Edit User Admin')

@section('content')
<div class="max-w-md">

    <a href="{{ route('admin.users.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 mb-5 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali
    </a>

    <div class="flat-card p-6">
        <div class="flex items-center gap-3 mb-5">
            <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Informasi Akun</h2>
            @if($user->isSuperAdmin())
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 dark:bg-amber-500/15 text-amber-700 dark:text-amber-400">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    Super Admin
                </span>
            @endif
        </div>

        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            {{-- Name --}}
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Nama <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name"
                    value="{{ old('name', $user->name) }}" required placeholder="Nama lengkap"
                    class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 dark:focus:border-blue-400 {{ $errors->has('name') ? 'border border-red-400 dark:border-red-500' : 'border border-slate-200 dark:border-white/[0.08]' }}">
                @error('name')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email — locked for super admin --}}
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Email <span class="text-red-500">*</span>
                </label>
                @if($user->isSuperAdmin())
                    <div class="flex items-center gap-2 h-10 px-3.5 text-sm rounded-xl bg-slate-100 dark:bg-white/[0.02] border border-slate-200 dark:border-white/[0.06] text-slate-400 dark:text-slate-500 cursor-not-allowed select-none">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        {{ $user->email }}
                    </div>
                    <input type="hidden" name="email" value="{{ $user->email }}">
                    <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">Email Super Admin tidak dapat diubah.</p>
                @else
                    <input type="email" id="email" name="email"
                        value="{{ old('email', $user->email) }}" required placeholder="email@novelya.id"
                        class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 dark:focus:border-blue-400 {{ $errors->has('email') ? 'border border-red-400 dark:border-red-500' : 'border border-slate-200 dark:border-white/[0.08]' }}">
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            {{-- Password (optional) — hidden for super admin unless editing own account --}}
            @if(! $user->isSuperAdmin() || $user->id === session('admin_user.id'))
            @foreach([
                ['password', 'Password Baru', 'Kosongkan jika tidak ingin diubah'],
                ['password_confirmation', 'Konfirmasi Password Baru', 'Ulangi password baru'],
            ] as [$field, $label, $placeholder])
            <div>
                <label for="{{ $field }}" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    {{ $label }}
                </label>
                <div class="relative">
                    <input type="password" id="{{ $field }}" name="{{ $field }}" placeholder="{{ $placeholder }}"
                        class="block w-full h-10 pl-3.5 pr-10 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 dark:focus:border-blue-400">
                    <button type="button" onclick="togglePass('{{ $field }}')"
                        class="absolute inset-y-0 right-0 px-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                @error($field)
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
            @endforeach
            @else
            <div class="px-4 py-3 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 text-xs">
                Password Super Admin hanya dapat diubah oleh pemilik akun tersebut.
            </div>
            @endif

            <div class="flex items-center gap-3 pt-1">
                <button type="submit"
                    class="h-10 px-5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-blue-500/20 transition-all duration-150 cursor-pointer">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.users.index') }}"
                    class="h-10 px-5 flex items-center text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePass(id) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
}
</script>
@endpush
