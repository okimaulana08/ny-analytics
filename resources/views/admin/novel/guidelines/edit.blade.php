@extends('layouts.novel')

@section('title', 'Edit Panduan')

@section('breadcrumb')
    <a href="{{ route('admin.novel.stories.index') }}" class="top-nav-link px-0 py-0">Novel Generator</a>
    <svg class="w-3 h-3 flex-shrink-0" style="color: #5a5368;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('admin.novel.guidelines.index') }}" class="top-nav-link px-0 py-0">Panduan Penulisan</a>
    <svg class="w-3 h-3 flex-shrink-0" style="color: #5a5368;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span style="color: #d4a04a;" class="font-medium">Edit Panduan</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto px-5 py-8">
    <h1 class="font-mono text-xl font-semibold mb-6" style="color: #d4a04a;">Edit: {{ $guideline->name }}</h1>

    <form method="POST" action="{{ route('admin.novel.guidelines.update', $guideline) }}">
        @csrf @method('PUT')
        @include('admin.novel.guidelines._form')
        <div class="flex items-center gap-3 pt-6 mt-4" style="border-top: 1px solid rgba(255,255,255,0.05);">
            <button type="submit" class="btn-gold">Simpan Perubahan</button>
            <a href="{{ route('admin.novel.guidelines.index') }}" class="btn-ghost">Batal</a>
        </div>
    </form>
</div>
@endsection
