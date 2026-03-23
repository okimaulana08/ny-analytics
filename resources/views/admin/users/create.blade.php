@extends('layouts.admin')
@section('title', 'Tambah User Admin')
@section('page-title', 'Tambah User Admin')

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
        <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white mb-5">Informasi Akun</h2>

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
            @csrf

            @foreach([
                ['name', 'text', 'Nama', 'Nama lengkap'],
                ['email', 'email', 'Email', 'admin@novelya.id'],
            ] as [$field, $type, $label, $placeholder])
            <div>
                <label for="{{ $field }}" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    {{ $label }} <span class="text-red-500">*</span>
                </label>
                <input type="{{ $type }}" id="{{ $field }}" name="{{ $field }}"
                    value="{{ old($field) }}" required placeholder="{{ $placeholder }}"
                    class="block w-full h-10 px-3.5 text-sm rounded-xl outline-none transition-all duration-150 bg-slate-50 dark:bg-white/[0.04] text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 dark:focus:border-blue-400 {{ $errors->has($field) ? 'border border-red-400 dark:border-red-500' : 'border border-slate-200 dark:border-white/[0.08]' }}">
                @error($field)
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
            @endforeach

            @foreach([
                ['password', 'Password', 'Min. 8 karakter'],
                ['password_confirmation', 'Konfirmasi Password', 'Ulangi password'],
            ] as [$field, $label, $placeholder])
            <div>
                <label for="{{ $field }}" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    {{ $label }} <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" id="{{ $field }}" name="{{ $field }}" required placeholder="{{ $placeholder }}"
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

            <div class="flex items-center gap-3 pt-1">
                <button type="submit"
                    class="h-10 px-5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-blue-500/20 transition-all duration-150 cursor-pointer">
                    Simpan
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
