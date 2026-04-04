@extends('layouts.novel')

@section('title', 'Novel Generator')

@section('breadcrumb')
    <span style="color: #d4a04a;" class="font-medium">Daftar Novel</span>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-5 py-8">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="font-mono text-2xl font-semibold" style="color: #d4a04a;">Novel Generator</h1>
            <p class="text-sm mt-1" style="color: #8a7f9a;">Generate novel Indonesia berkualitas tinggi dengan bantuan AI</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.novel.guidelines.index') }}" class="btn-ghost text-sm">
                Panduan Penulisan
            </a>
            <a href="{{ route('admin.novel.stories.create') }}" class="btn-gold text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Novel Baru
            </a>
        </div>
    </div>

    @if($stories->isEmpty())
    <div class="novel-card p-16 text-center">
        <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background: rgba(124,92,191,0.15);">
            <svg class="w-8 h-8" style="color: #7c5cbf;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <p class="font-serif text-lg mb-1" style="color: #e8e0d0;">Belum ada novel</p>
        <p class="text-sm mb-6" style="color: #8a7f9a;">Mulai dengan membuat novel pertamamu</p>
        <a href="{{ route('admin.novel.stories.create') }}" class="btn-gold text-sm inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Novel Pertama
        </a>
    </div>
    @else
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">

        {{-- New story card --}}
        <a href="{{ route('admin.novel.stories.create') }}" class="novel-card flex flex-col items-center justify-center p-6 text-center cursor-pointer min-h-[280px] border-dashed" style="border-color: rgba(212,160,74,0.2);">
            <div class="w-12 h-12 rounded-xl mb-3 flex items-center justify-center" style="background: rgba(212,160,74,0.1); border: 1px dashed rgba(212,160,74,0.3);">
                <svg class="w-6 h-6" style="color: #d4a04a;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <p class="text-sm font-medium" style="color: #d4a04a;">Novel Baru</p>
            <p class="text-xs mt-1" style="color: #5a5368;">Mulai generate</p>
        </a>

        @foreach($stories as $story)
        @php
            $coverClass = match($story->genre) {
                'drama_perselingkuhan' => 'cover-perselingkuhan',
                'drama_poligami' => 'cover-poligami',
                'drama_kdrt' => 'cover-kdrt',
                'drama_pernikahan_kontrak' => 'cover-pernikahan_kontrak',
                default => 'cover-drama_rumah_tangga',
            };

            $stage1Done = in_array($story->status, ['overview_approved','outline_pending','outline_ready','outline_approved','content_in_progress','content_complete','published']);
            $stage1Active = in_array($story->status, ['overview_pending','overview_ready']);
            $stage2Done = in_array($story->status, ['outline_approved','content_in_progress','content_complete','published']);
            $stage2Active = in_array($story->status, ['outline_pending','outline_ready']);
            $stage3Done = in_array($story->status, ['content_complete','published']);
            $stage3Active = in_array($story->status, ['content_in_progress']);
        @endphp
        <a href="{{ route('admin.novel.stories.show', $story) }}" class="novel-card overflow-hidden flex flex-col cursor-pointer min-h-[280px]">
            {{-- Cover --}}
            <div class="{{ $coverClass }} h-36 flex items-center justify-center relative flex-shrink-0">
                <svg class="w-10 h-10 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <div class="absolute top-2 right-2">
                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-full badge-{{ in_array($story->status, ['overview_pending','outline_pending']) ? 'pending' : (in_array($story->status, ['overview_ready','outline_ready']) ? 'ready' : (in_array($story->status, ['overview_approved','outline_approved','content_in_progress']) ? 'approved' : 'draft')) }}">
                        {{ $story->statusLabel() }}
                    </span>
                </div>
            </div>

            {{-- Info --}}
            <div class="p-4 flex-1 flex flex-col">
                <p class="font-serif text-sm font-medium leading-snug mb-1" style="color: #e8e0d0;">
                    {{ $story->title_draft ?? $story->title ?? '(Belum ada judul)' }}
                </p>
                <p class="text-xs mb-3" style="color: #8a7f9a;">{{ $story->genreLabel() }} · {{ $story->total_chapters_planned }} bab</p>

                {{-- Progress dots --}}
                <div class="flex items-center gap-1 mt-auto">
                    <div class="flex items-center gap-1 flex-1">
                        <div class="w-2 h-2 rounded-full {{ $stage1Done ? 'bg-amber-400' : ($stage1Active ? 'bg-amber-400/50' : 'bg-white/10') }}"></div>
                        <div class="flex-1 h-px {{ $stage1Done ? 'bg-amber-400/40' : 'bg-white/5' }}"></div>
                        <div class="w-2 h-2 rounded-full {{ $stage2Done ? 'bg-amber-400' : ($stage2Active ? 'bg-amber-400/50' : 'bg-white/10') }}"></div>
                        <div class="flex-1 h-px {{ $stage2Done ? 'bg-amber-400/40' : 'bg-white/5' }}"></div>
                        <div class="w-2 h-2 rounded-full {{ $stage3Done ? 'bg-amber-400' : ($stage3Active ? 'bg-amber-400/50' : 'bg-white/10') }}"></div>
                    </div>
                </div>
                <div class="flex justify-between text-[9px] font-mono mt-1" style="color: #5a5368;">
                    <span>Ringkasan</span><span>Outline</span><span>Konten</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif

</div>
@endsection
