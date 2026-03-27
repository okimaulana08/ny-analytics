@extends('layouts.admin')
@section('title', 'Edit Template Email')
@section('page-title', 'Edit Template Email')

@section('content')
<div class="max-w-4xl">
    <a href="{{ route('admin.crm.templates.index') }}"
        class="inline-flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 mb-5 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali
    </a>

    <div class="flat-card p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="font-mono text-sm font-semibold text-slate-800 dark:text-white">Edit: {{ $template->name }}</h2>
            <a href="{{ route('admin.crm.templates.preview', $template) }}" target="_blank"
                class="h-8 px-3 inline-flex items-center gap-1.5 text-xs text-violet-600 dark:text-violet-400 border border-violet-200 dark:border-violet-500/30 rounded-lg hover:bg-violet-50 dark:hover:bg-violet-500/10 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Preview
            </a>
        </div>

        <form action="{{ route('admin.crm.templates.update', $template) }}" method="POST" class="space-y-4">
            @csrf @method('PUT')
            @include('admin.crm.templates._form', ['template' => $template])

            <div class="flex items-center gap-3 pt-1">
                <button type="submit"
                    class="h-10 px-5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-blue-500/20 transition-all duration-150 cursor-pointer">
                    Perbarui Template
                </button>
                <a href="{{ route('admin.crm.templates.index') }}"
                    class="h-10 px-5 flex items-center text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
