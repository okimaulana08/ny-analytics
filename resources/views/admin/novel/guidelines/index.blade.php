@extends('layouts.novel')

@section('title', 'Panduan Penulisan')

@section('breadcrumb')
    <a href="{{ route('admin.novel.stories.index') }}" class="top-nav-link px-0 py-0">Novel Generator</a>
    <svg class="w-3 h-3 flex-shrink-0" style="color: #5a5368;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span style="color: #d4a04a;" class="font-medium">Panduan Penulisan</span>
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-5 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="font-mono text-xl font-semibold" style="color: #d4a04a;">Panduan Penulisan</h1>
            <p class="text-sm mt-1" style="color: #8a7f9a;">Template instruksi yang disuntikkan ke AI saat generate novel</p>
        </div>
        <a href="{{ route('admin.novel.guidelines.create') }}" class="btn-gold text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Panduan Baru
        </a>
    </div>

    @if($guidelines->isEmpty())
    <div class="novel-card p-12 text-center">
        <p class="font-serif text-base mb-3" style="color: #e8e0d0;">Belum ada panduan penulisan</p>
        <a href="{{ route('admin.novel.guidelines.create') }}" class="btn-gold text-sm inline-flex items-center gap-2">
            Buat Panduan
        </a>
    </div>
    @else
    <div class="space-y-3">
        @foreach($guidelines as $g)
        <div class="novel-card p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="font-medium text-sm" style="color: #e8e0d0;">{{ $g->name }}</h3>
                        @if($g->is_active)
                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-full badge-approved">aktif</span>
                        @else
                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-full badge-draft">nonaktif</span>
                        @endif
                    </div>
                    <p class="text-xs" style="color: #8a7f9a;">
                        Genre: {{ $g->genre }} · POV: {{ $g->narrative_pov }} · Target: {{ $g->target_chapter_word_count }} kata/bab
                    </p>
                    @if($g->system_prompt_prefix)
                    <p class="text-xs mt-2 italic" style="color: #5a5368;">{{ Str::limit($g->system_prompt_prefix, 120) }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('admin.novel.guidelines.edit', $g) }}" class="btn-ghost text-xs px-3 py-2">Edit</a>
                    <form method="POST" action="{{ route('admin.novel.guidelines.destroy', $g) }}"
                        onsubmit="return confirm('Hapus panduan ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-ghost text-xs px-3 py-2" style="color: #f4a0a0;">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
