@extends('layouts.novel')

@section('title', ($story->title_draft ?? $story->title ?? 'Novel') . ' — Workspace')

@section('breadcrumb')
    <a href="{{ route('admin.novel.stories.index') }}" class="top-nav-link px-0 py-0">Daftar Novel</a>
    <svg class="w-3 h-3 flex-shrink-0" style="color: #5a5368;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span style="color: #e8e0d0;" class="truncate max-w-xs font-medium text-sm">{{ $story->title_draft ?? $story->title ?? 'Novel Baru' }}</span>
@endsection

@section('header-right')
@php
    $totalTokens = $story->total_input_tokens + $story->total_output_tokens;
    $totalCost = ($story->total_input_tokens * 3 + $story->total_output_tokens * 15) / 1_000_000;
@endphp
<div class="flex items-center gap-3">
    @if($story->creator)
    <div class="flex items-center gap-1.5 text-xs" style="color: #8a7f9a;">
        <div class="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-bold" style="background: rgba(124,92,191,0.25); color: #a688e0;">
            {{ strtoupper(substr($story->creator->name, 0, 1)) }}
        </div>
        <span>{{ $story->creator->name }}</span>
    </div>
    @endif
    @if($totalTokens > 0)
    <div class="flex items-center gap-1.5 text-xs font-mono" style="color: #8a7f9a;">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
        {{ number_format($totalTokens) }} tokens
        <span style="color: rgba(212,160,74,0.7);">·</span>
        <span style="color: #d4a04a;">${{ number_format($totalCost, 4) }}</span>
    </div>
    @endif
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-5 py-6"
    x-data="storyWorkspace({{ $story->id }}, '{{ $story->status }}')"
    x-init="init()">

    {{-- Stage Pipeline --}}
    <div class="novel-card p-5 mb-6">
        <div class="flex items-center">
            {{-- Stage 1: Ringkasan --}}
            @php
                $s1Done = in_array($story->status, ['overview_approved','outline_pending','outline_ready','outline_approved','content_in_progress','content_complete','published']);
                $s1Active = in_array($story->status, ['overview_pending','overview_ready','draft']);
                $s2Done = in_array($story->status, ['outline_approved','content_in_progress','content_complete','published']);
                $s2Active = in_array($story->status, ['outline_pending','outline_ready','overview_approved']);
                $s3Done = in_array($story->status, ['content_complete','published']);
                $s3Active = $story->isContentPhase();
            @endphp
            <div class="flex flex-col items-center gap-1.5">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-mono font-semibold border-2 transition-all
                    {{ $s1Done ? 'border-amber-400 bg-amber-400/10 text-amber-400' : 'border-amber-400 bg-amber-400/10 text-amber-400' }}">
                    @if($s1Done) ✓ @else 1 @endif
                </div>
                <span class="text-[10px] font-mono" style="color: {{ $s1Done || $s1Active ? '#d4a04a' : '#5a5368' }};">Ringkasan</span>
            </div>
            <div class="stage-connector {{ $s1Done ? 'done' : '' }} mb-5"></div>
            <div class="flex flex-col items-center gap-1.5">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-mono font-semibold border-2 transition-all
                    {{ $s2Done ? 'border-amber-400 bg-amber-400/10 text-amber-400' : ($s2Active ? 'border-amber-400/60 bg-amber-400/05 text-amber-400/70' : 'border-white/10 bg-transparent text-white/20') }}">
                    @if($s2Done) ✓ @elseif($s2Active) 2 @else 🔒 @endif
                </div>
                <span class="text-[10px] font-mono" style="color: {{ $s2Done || $s2Active ? '#d4a04a' : '#5a5368' }};">Outline</span>
            </div>
            <div class="stage-connector {{ $s2Done ? 'done' : '' }} mb-5"></div>
            <div class="flex flex-col items-center gap-1.5">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-mono font-semibold border-2 transition-all
                    {{ $s3Done ? 'border-amber-400 bg-amber-400/10 text-amber-400' : ($s3Active ? 'border-purple-400/60 bg-purple-400/05 text-purple-400/70' : 'border-white/10 bg-transparent text-white/20') }}">
                    @if($s3Done) ✓ @elseif($s3Active) 3 @else 🔒 @endif
                </div>
                <span class="text-[10px] font-mono" style="color: {{ $s3Done || $s3Active ? '#a688e0' : '#5a5368' }};">Konten Bab</span>
            </div>
        </div>
    </div>

    {{-- =================== STAGE 1: OVERVIEW =================== --}}
    @if(in_array($story->status, ['draft', 'overview_pending', 'overview_ready', 'overview_approved']))

        @if(in_array($story->status, ['draft', 'overview_pending']))
        {{-- Generating / initial state --}}
        <div class="novel-card p-10 text-center border generating" x-show="status === 'overview_pending' || status === 'draft' || submitting" id="generating-box">
            <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background: rgba(124,92,191,0.15);">
                <svg class="w-7 h-7 animate-spin" style="color: #7c5cbf;" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <p class="font-serif text-lg mb-2" style="color: #e8e0d0;">✦ AI sedang menulis ringkasan...</p>
            <p class="text-sm" style="color: #8a7f9a;">Halaman akan otomatis update saat selesai</p>
        </div>
        @endif

        @if($story->status === 'overview_ready' || $story->status === 'overview_approved')
        {{-- Overview ready for review --}}
        <div class="space-y-5" x-data="{ editMode: false }">
            <div class="novel-card p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="font-mono text-base font-semibold" style="color: #d4a04a;">📖 Gambaran Umum Cerita</h2>
                    <div class="flex items-center gap-2">
                        @if($story->status === 'overview_approved')
                            <span class="text-xs px-2.5 py-1 rounded-full font-mono badge-approved">✓ Disetujui</span>
                        @endif
                        <button @click="editMode = !editMode"
                            class="btn-ghost text-xs flex items-center gap-1"
                            :style="editMode ? 'border-color: rgba(212,160,74,0.4); color: #d4a04a;' : ''">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span x-text="editMode ? 'Batal' : 'Edit Langsung'"></span>
                        </button>
                    </div>
                </div>

                {{-- VIEW MODE --}}
                <div x-show="!editMode">
                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <div>
                            <p class="text-xs font-mono mb-1" style="color: #8a7f9a;">JUDUL DRAFT</p>
                            <p class="font-serif font-medium text-base" style="color: #e8e0d0;">{{ $story->title_draft ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-mono mb-1" style="color: #8a7f9a;">TEMA</p>
                            <p class="text-sm" style="color: #e8e0d0;">{{ $story->theme ?? '—' }}</p>
                        </div>
                    </div>

                    @if($story->synopsis)
                    <div class="mb-5">
                        <p class="text-xs font-mono mb-2" style="color: #8a7f9a;">SINOPSIS</p>
                        <div class="prose-novel text-sm">
                            {!! nl2br(e($story->synopsis)) !!}
                        </div>
                    </div>
                    @endif

                    @if($story->characters)
                    <div class="mb-5">
                        <p class="text-xs font-mono mb-3" style="color: #8a7f9a;">TOKOH-TOKOH</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($story->characters as $char)
                            <div class="px-3 py-2 rounded-xl" style="background: rgba(212,160,74,0.06); border: 1px solid rgba(212,160,74,0.12);">
                                <p class="text-xs font-semibold" style="color: #d4a04a;">{{ $char['name'] ?? '' }}</p>
                                <p class="text-[10px]" style="color: #8a7f9a;">{{ $char['role'] ?? '' }}</p>
                                @if(!empty($char['description']))
                                    <p class="text-xs mt-1" style="color: #e8e0d0; max-width: 20ch;">{{ Str::limit($char['description'], 80) }}</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($story->plot_points)
                    <div>
                        <p class="text-xs font-mono mb-3" style="color: #8a7f9a;">PLOT TWIST KUNCI</p>
                        <div class="space-y-2">
                            @foreach($story->plot_points as $pp)
                            <div class="flex items-start gap-3 text-sm">
                                <span class="font-mono text-xs px-2 py-0.5 rounded flex-shrink-0 mt-0.5" style="background: rgba(212,160,74,0.1); color: #d4a04a;">Ch.{{ $pp['chapter'] ?? '?' }}</span>
                                <p style="color: #e8e0d0;">{{ $pp['event'] ?? '' }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- EDIT MODE --}}
                <div x-show="editMode" x-cloak>
                    <form method="POST" action="{{ route('admin.novel.stories.update-overview', $story) }}" class="space-y-4">
                        @csrf @method('PATCH')
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-mono block mb-1" style="color: #8a7f9a;">JUDUL DRAFT</label>
                                <input type="text" name="title_draft" value="{{ old('title_draft', $story->title_draft) }}"
                                    class="novel-input text-sm font-serif">
                            </div>
                            <div>
                                <label class="text-xs font-mono block mb-1" style="color: #8a7f9a;">TEMA</label>
                                <input type="text" name="theme" value="{{ old('theme', $story->theme) }}"
                                    class="novel-input text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-mono block mb-1" style="color: #8a7f9a;">SINOPSIS</label>
                            <textarea name="synopsis" class="novel-input text-sm resize-y" rows="5">{{ old('synopsis', $story->synopsis) }}</textarea>
                        </div>
                        <div>
                            <label class="text-xs font-mono block mb-1" style="color: #8a7f9a;">OVERVIEW UMUM</label>
                            <textarea name="general_overview" class="novel-input text-sm resize-y" rows="4">{{ old('general_overview', $story->general_overview) }}</textarea>
                        </div>
                        <div>
                            <label class="text-xs font-mono block mb-1" style="color: #8a7f9a;">TOKOH (JSON)</label>
                            <textarea name="characters" class="novel-input text-xs resize-y font-mono" rows="6"
                                style="font-size: 11px;">{{ old('characters', json_encode($story->characters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
                        </div>
                        <div>
                            <label class="text-xs font-mono block mb-1" style="color: #8a7f9a;">PLOT POINTS (JSON)</label>
                            <textarea name="plot_points" class="novel-input text-xs resize-y font-mono" rows="6"
                                style="font-size: 11px;">{{ old('plot_points', json_encode($story->plot_points, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                            <button type="submit" class="btn-gold text-sm flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Simpan Perubahan
                            </button>
                            <button type="button" @click="editMode = false" class="btn-ghost text-sm">Batal</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Approval bar --}}
            @if($story->status === 'overview_ready')
            <div class="approval-bar">
                <div class="flex items-center gap-3 justify-center flex-wrap">
                    <form method="POST" action="{{ route('admin.novel.stories.generate-outlines', $story) }}" class="inline" @submit="submitting = true">
                        @csrf
                        <button type="submit" class="btn-gold flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Generate Outline Semua Bab
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.novel.stories.regenerate-overview', $story) }}" class="inline" @submit="submitting = true">
                        @csrf
                        <button type="submit" class="btn-ghost flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Generate Ulang Ringkasan
                        </button>
                    </form>
                </div>
            </div>
            @endif

            @if($story->status === 'overview_approved')
            <div class="approval-bar">
                <div class="text-center">
                    {{-- Spinner saat submitting --}}
                    <div x-show="submitting" x-cloak class="py-4">
                        <div class="w-10 h-10 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background: rgba(124,92,191,0.15);">
                            <svg class="w-5 h-5 animate-spin" style="color: #7c5cbf;" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-serif" style="color: #e8e0d0;">Memulai generate outline...</p>
                    </div>
                    <div x-show="!submitting">
                        <form method="POST" action="{{ route('admin.novel.stories.generate-outlines', $story) }}" class="inline" @submit="submitting = true">
                            @csrf
                            <button type="submit" class="btn-gold flex items-center gap-2 mx-auto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Generate Outline Semua Bab
                            </button>
                        </form>
                        <p class="text-xs mt-2" style="color: #5a5368;">AI akan generate outline untuk semua {{ $story->total_chapters_planned }} bab sekaligus</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

    {{-- =================== STAGE 2: OUTLINE =================== --}}
    @elseif(in_array($story->status, ['outline_pending', 'outline_ready']))

        @if($story->status === 'outline_pending')
        <div class="novel-card p-10 text-center border generating mb-5">
            <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background: rgba(124,92,191,0.15);">
                <svg class="w-7 h-7 animate-spin" style="color: #7c5cbf;" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <p class="font-serif text-lg mb-3" style="color: #e8e0d0;">✦ AI sedang menyusun outline semua bab...</p>

            {{-- Progress bar --}}
            <div class="max-w-xs mx-auto mb-3">
                <div class="flex items-center justify-between text-xs font-mono mb-1.5">
                    <span style="color: #8a7f9a;">Progress</span>
                    <span>
                        <span x-text="outlineProgress.done" style="color: #95d5b2;">0</span>
                        <span style="color: #8a7f9a;"> selesai</span>
                        <template x-if="outlineProgress.failed > 0">
                            <span> · <span x-text="outlineProgress.failed" style="color: #f4a0a0;"></span><span style="color: #f4a0a0;"> gagal</span></span>
                        </template>
                        <span style="color: #8a7f9a;"> / {{ $story->total_chapters_planned }} bab</span>
                    </span>
                </div>
                <div class="w-full rounded-full h-2" style="background: rgba(255,255,255,0.07);">
                    <div class="h-2 rounded-full transition-all duration-500"
                        :style="'background: linear-gradient(90deg, #7c5cbf, #a688e0); width: ' + Math.round(((outlineProgress.done + (outlineProgress.failed || 0)) / {{ $story->total_chapters_planned }}) * 100) + '%'"></div>
                </div>
                <template x-if="outlineProgress.failed > 0">
                    <p class="text-xs mt-1.5 text-center" style="color: #f4a0a0;">⚠ Ada bab yang gagal — akan muncul setelah selesai untuk di-regenerate</p>
                </template>
            </div>

            <p class="text-sm" style="color: #8a7f9a;">Halaman otomatis update saat semua selesai</p>
        </div>
        @else
        {{-- Outline ready — some may have failed --}}
        @php
            $failedOutlineChapters = $story->chapters->where('outline_status', 'failed');
            $readyOutlineChapters = $story->chapters->where('outline_status', 'ready');
        @endphp
        <div class="novel-card p-5 mb-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-mono text-base font-semibold" style="color: #d4a04a;">Outline Bab</h2>
                @if($failedOutlineChapters->count() > 0)
                    <span class="text-xs px-2.5 py-1 rounded-full font-mono" style="background: rgba(107,45,45,0.3); color: #f4a0a0;">⚠ {{ $failedOutlineChapters->count() }} bab gagal</span>
                @else
                    <span class="text-xs" style="color: #8a7f9a;">Klik bab untuk review dan edit outline</span>
                @endif
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($story->chapters as $chapter)
                <a href="{{ route('admin.novel.chapters.show', [$story, $chapter]) }}"
                    class="block p-3 rounded-xl"
                    style="background: {{ $chapter->outline_status === 'failed' ? 'rgba(107,45,45,0.2)' : 'rgba(255,255,255,0.04)' }}; border: 1px solid {{ $chapter->outline_status === 'failed' ? 'rgba(244,160,160,0.2)' : 'rgba(255,255,255,0.09)' }}; transition: border-color 0.15s;"
                    onmouseover="this.style.borderColor='rgba(212,160,74,0.3)'"
                    onmouseout="this.style.borderColor='{{ $chapter->outline_status === 'failed' ? 'rgba(244,160,160,0.2)' : 'rgba(255,255,255,0.09)' }}'">
                    <p class="text-xs font-mono mb-1" style="color: #8a7f9a;">Bab {{ $chapter->chapter_number }}</p>
                    <p class="text-xs font-medium mb-2.5" style="color: #e8e0d0; line-height: 1.4;">{{ Str::limit($chapter->title ?? 'Belum ada judul', 40) }}</p>
                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-full badge-{{ $chapter->outline_status }}">{{ $chapter->outline_status }}</span>
                </a>
                @endforeach
            </div>
        </div>

        @if($failedOutlineChapters->count() > 0)
        <div class="novel-card p-4 mb-5" style="border-color: rgba(244,160,160,0.2); background: rgba(107,45,45,0.1);">
            <p class="text-sm font-mono font-semibold mb-1" style="color: #f4a0a0;">{{ $failedOutlineChapters->count() }} Bab Gagal Di-generate</p>
            <p class="text-xs mb-3" style="color: #8a7f9a;">Klik masing-masing bab yang gagal untuk regenerate outlinenya secara individual.</p>
            <div class="flex flex-wrap gap-2">
                @foreach($failedOutlineChapters as $failedChapter)
                <a href="{{ route('admin.novel.chapters.show', [$story, $failedChapter]) }}"
                    class="text-xs font-mono px-2.5 py-1 rounded-lg"
                    style="background: rgba(244,160,160,0.15); color: #f4a0a0; border: 1px solid rgba(244,160,160,0.25);">
                    Bab {{ $failedChapter->chapter_number }} →
                </a>
                @endforeach
            </div>
        </div>
        @endif
        @endif

    {{-- =================== STAGE 3: CONTENT =================== --}}
    @else
        @php
            $approvedCount = $story->chapters->where('content_status', 'approved')->count();
            $generatingCount = $story->chapters->where('content_status', 'generating')->count();
            $total = $story->chapters->count();
            $pendingChapters = $story->chapters->filter(fn($c) => in_array($c->content_status, ['pending','failed','revision_requested']) && in_array($c->outline_status, ['ready','approved']));
            $failedOutlineInContent = $story->chapters->where('outline_status', 'failed');
        @endphp
        <div class="novel-card p-5 mb-5" x-data="{ selectedIds: [], selectMode: false }">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-mono text-base font-semibold" style="color: #d4a04a;">Konten Bab</h2>
                <div class="flex items-center gap-2">
                    @if($generatingCount > 0)
                        <span class="text-xs font-mono badge-generating px-2 py-0.5 rounded-full">⟳ {{ $generatingCount }} generating</span>
                    @endif
                    <span class="text-xs font-mono" style="color: #8a7f9a;">{{ $approvedCount }}/{{ $total }} selesai</span>
                </div>
            </div>

            {{-- Bulk action bar --}}
            @if($pendingChapters->count() > 0)
            <div class="flex items-center gap-2 mb-4 flex-wrap">
                {{-- Generate All --}}
                <form method="POST" action="{{ route('admin.novel.stories.generate-bulk-content', $story) }}" class="inline">
                    @csrf
                    <input type="hidden" name="chapter_ids[]" value="all">
                    <button type="submit" class="btn-gold text-xs flex items-center gap-1.5"
                        onclick="return confirm('Generate konten untuk semua {{ $pendingChapters->count() }} bab yang belum selesai?')">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Generate Semua ({{ $pendingChapters->count() }} bab)
                    </button>
                </form>

                {{-- Generate Selected toggle --}}
                <button @click="selectMode = !selectMode; if(!selectMode) selectedIds = []"
                    class="btn-ghost text-xs flex items-center gap-1.5"
                    :style="selectMode ? 'border-color: rgba(212,160,74,0.4); color: #d4a04a;' : ''">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span x-text="selectMode ? 'Batal Pilih' : 'Pilih Bab'"></span>
                </button>

                {{-- Submit selected --}}
                <template x-if="selectMode && selectedIds.length > 0">
                    <form method="POST" action="{{ route('admin.novel.stories.generate-bulk-content', $story) }}" id="bulk-form">
                        @csrf
                        <template x-for="id in selectedIds" :key="id">
                            <input type="hidden" name="chapter_ids[]" :value="id">
                        </template>
                        <button type="submit" class="btn-outline text-xs flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Generate Terpilih (<span x-text="selectedIds.length"></span>)
                        </button>
                    </form>
                </template>
            </div>
            @endif

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($story->chapters as $chapter)
                @php
                    $outlineFailed = $chapter->outline_status === 'failed';
                    $canSelect = in_array($chapter->content_status, ['pending','failed','revision_requested']) && in_array($chapter->outline_status, ['ready','approved']) && !$outlineFailed;
                    $isApproved = $chapter->content_status === 'approved';
                    $chId = $chapter->id;
                @endphp

                <div class="rounded-xl p-3 select-none cursor-pointer"
                    :style="(function() {
                        const selected = {{ $canSelect ? 'true' : 'false' }} && selectedIds.includes('{{ $chId }}');
                        const base = 'transition: background 0.15s, border-color 0.15s, box-shadow 0.15s;';
                        if (selected) return base + 'background: rgba(212,160,74,0.15); border: 1px solid rgba(212,160,74,0.7); box-shadow: 0 0 0 3px rgba(212,160,74,0.1);';
                        {{ $outlineFailed ? 'return base + \'background: rgba(107,45,45,0.2); border: 1px solid rgba(244,160,160,0.2);\';' : ($isApproved ? 'return base + \'background: rgba(45,106,79,0.18); border: 1px solid rgba(149,213,178,0.25);\';' : 'return base + \'background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.09);\';') }}
                    })()"
                    @mouseenter="if({{ $canSelect ? 'true' : 'false' }} && !selectedIds.includes('{{ $chId }}')) $el.style.borderColor = 'rgba(212,160,74,0.3)'"
                    @mouseleave="if({{ $canSelect ? 'true' : 'false' }} && !selectedIds.includes('{{ $chId }}')) $el.style.borderColor = '{{ $isApproved ? 'rgba(149,213,178,0.25)' : 'rgba(255,255,255,0.09)' }}'"
                    @click="{{ $canSelect ? 'if(selectMode) { const idx=selectedIds.indexOf(\'' . $chId . '\'); idx>=0 ? selectedIds.splice(idx,1) : selectedIds.push(\'' . $chId . '\'); } else { window.location=\'' . route('admin.novel.chapters.show', [$story, $chapter]) . '\'; }' : 'window.location=\'' . route('admin.novel.chapters.show', [$story, $chapter]) . '\'' }}">

                    {{-- Top row: chapter label + checkbox (select mode) or status icon (approved) --}}
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-mono" style="color: {{ $isApproved ? '#95d5b2' : '#8a7f9a' }};">
                            Bab {{ $chapter->chapter_number }}
                        </span>

                        @if($isApproved)
                        {{-- Approved indicator — always visible --}}
                        <div class="w-4 h-4 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(149,213,178,0.2);">
                            <svg class="w-2.5 h-2.5" style="color: #95d5b2;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        @elseif($canSelect)
                        {{-- Checkbox — only visible in select mode --}}
                        <div x-show="selectMode"
                            class="w-4 h-4 rounded flex-shrink-0 flex items-center justify-center"
                            :style="selectedIds.includes('{{ $chId }}')
                                ? 'background: #d4a04a; border: 2px solid #d4a04a;'
                                : 'border: 2px solid rgba(255,255,255,0.22); background: rgba(255,255,255,0.03);'">
                            <svg x-show="selectedIds.includes('{{ $chId }}')" class="w-2.5 h-2.5" style="color: #0e0c12;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        @endif
                    </div>

                    {{-- Title --}}
                    <p class="text-xs font-medium mb-2.5" style="color: {{ $isApproved ? 'rgba(232,224,208,0.7)' : '#e8e0d0' }}; line-height: 1.4;">
                        {{ Str::limit($chapter->title ?? 'Belum ada judul', 40) }}
                    </p>

                    {{-- Status badge --}}
                    @if($outlineFailed)
                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-full badge-failed">outline gagal</span>
                    @else
                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-full badge-{{ $chapter->content_status }}">
                        {{ $chapter->content_status === 'approved' ? 'selesai' : $chapter->content_status }}
                    </span>
                    @endif
                </div>

                @endforeach
            </div>
        </div>

        {{-- Failed outline chapters notice in content phase --}}
        @if($failedOutlineInContent->count() > 0)
        <div class="novel-card p-4 mb-5" style="border-color: rgba(244,160,160,0.2); background: rgba(107,45,45,0.1);">
            <p class="text-sm font-mono font-semibold mb-1" style="color: #f4a0a0;">{{ $failedOutlineInContent->count() }} Bab Outline Gagal</p>
            <p class="text-xs mb-3" style="color: #8a7f9a;">Bab berikut tidak bisa generate konten karena outlinenya gagal. Klik untuk regenerate outlinenya.</p>
            <div class="flex flex-wrap gap-2">
                @foreach($failedOutlineInContent as $failedCh)
                <a href="{{ route('admin.novel.chapters.show', [$story, $failedCh]) }}"
                    class="text-xs font-mono px-2.5 py-1 rounded-lg"
                    style="background: rgba(244,160,160,0.15); color: #f4a0a0; border: 1px solid rgba(244,160,160,0.25);">
                    Bab {{ $failedCh->chapter_number }} — Regenerate Outline →
                </a>
                @endforeach
            </div>
        </div>
        @endif
    @endif

    {{-- Export buttons (shown when there are approved chapters) --}}
    @if($story->chapters->where('content_status', 'approved')->count() > 0)
    <div class="novel-card p-4 mb-5">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <p class="font-mono text-sm font-semibold mb-0.5" style="color: #d4a04a;">Export Novel</p>
                <p class="text-xs" style="color: #5a5368;">{{ $story->chapters->where('content_status', 'approved')->count() }} bab approved siap diexport</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.novel.stories.export-pdf', $story) }}" target="_blank"
                    class="btn-gold text-xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export PDF
                </a>
                <a href="{{ route('admin.novel.stories.export-docx', $story) }}"
                    class="btn-ghost text-xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export DOCX
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- Analytics Section --}}
    @if($story->total_input_tokens > 0)
    @include('admin.novel.stories._analytics', ['story' => $story, 'analyticsData' => $analyticsData ?? []])
    @endif

    {{-- Delete button --}}
    <div class="mt-8 pt-6 flex justify-end" style="border-top: 1px solid rgba(255,255,255,0.04);">
        <form method="POST" action="{{ route('admin.novel.stories.destroy', $story) }}"
            onsubmit="return confirm('Yakin hapus novel ini? Semua data termasuk bab dan konten akan hilang permanen.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-ghost text-xs flex items-center gap-1.5" style="color: #f4a0a0; border-color: rgba(244,160,160,0.15);">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus Novel
            </button>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
function storyWorkspace(storyId, initialStatus) {
    return {
        status: initialStatus,
        submitting: false,
        outlineProgress: { done: 0, failed: 0 },
        pollInterval: null,

        init() {
            const pendingStatuses = ['draft', 'overview_pending', 'outline_pending', 'content_in_progress'];
            if (pendingStatuses.includes(this.status)) {
                this.startPolling();
            }
        },

        startPolling() {
            this.pollInterval = setInterval(async () => {
                try {
                    const res = await fetch(`/admin/novel/stories/${storyId}/status`);
                    const data = await res.json();

                    // Update outline progress tanpa reload halaman
                    if (data.outline_progress) {
                        this.outlineProgress = data.outline_progress;
                    }

                    if (data.status !== this.status) {
                        window.location.reload();
                    }
                } catch (e) {}
            }, 5000);
        },

        beforeDestroy() {
            if (this.pollInterval) clearInterval(this.pollInterval);
        }
    }
}
</script>
@endpush
