@extends('layouts.admin')
@section('title', 'Buat Trigger WA')
@section('page-title', 'Buat Trigger WA')

@section('content')
<div class="max-w-xl">

    <div class="mb-5">
        <a href="{{ route('admin.crm.wa-triggers.index') }}"
            class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke daftar trigger
        </a>
    </div>

    @if($errors->any())
    <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 px-4 py-3 text-sm text-red-700 dark:text-red-400">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.crm.wa-triggers.store') }}" method="POST">
        @csrf
        <div class="flat-card p-6 space-y-6">
            <div>
                <h2 class="text-sm font-semibold text-slate-800 dark:text-white mb-4">Konfigurasi Trigger</h2>
                @include('admin.crm.wa-triggers._form')
            </div>

            <p class="text-xs text-slate-400">
                Setelah trigger dibuat, 10 template pesan akan otomatis di-generate. Kamu bisa edit, tambah, atau hapus template di halaman edit.
            </p>
        </div>

        <div class="mt-4 flex items-center gap-3">
            <button type="submit"
                class="h-9 px-5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all">
                Buat Trigger
            </button>
            <a href="{{ route('admin.crm.wa-triggers.index') }}"
                class="h-9 px-4 text-sm font-medium text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors inline-flex items-center">
                Batal
            </a>
        </div>
    </form>

</div>
@endsection
