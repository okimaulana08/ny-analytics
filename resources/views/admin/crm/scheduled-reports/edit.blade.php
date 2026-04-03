@extends('layouts.admin')
@section('title', 'Edit Scheduled Report')
@section('page-title', 'Edit: ' . $scheduledReport->name)

@section('content')

<div class="mb-5">
    <a href="{{ route('admin.crm.scheduled-reports.index') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition">← Kembali</a>
</div>

<div class="max-w-2xl">
    <div class="flat-card p-6">
        <form action="{{ route('admin.crm.scheduled-reports.update', $scheduledReport) }}" method="POST">
            @csrf @method('PUT')
            @include('admin.crm.scheduled-reports._form')
            <div class="flex gap-3 mt-6">
                <button type="submit" class="h-10 px-5 rounded-xl text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition">
                    Perbarui
                </button>
                <a href="{{ route('admin.crm.scheduled-reports.index') }}"
                   class="h-10 px-5 rounded-xl text-sm font-medium border border-slate-200 dark:border-white/10 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/[0.04] transition flex items-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
