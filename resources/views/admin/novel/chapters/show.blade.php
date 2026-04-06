@extends('layouts.novel')

@section('title', $chapter->chapterLabel() . ' — ' . ($story->title_draft ?? 'Novel'))

@section('breadcrumb')
    <a href="{{ route('admin.novel.stories.index') }}" class="top-nav-link px-0 py-0">Daftar Novel</a>
    <svg class="w-3 h-3 flex-shrink-0" style="color: #5a5368;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('admin.novel.stories.show', $story) }}" class="top-nav-link px-0 py-0 truncate max-w-[180px]">{{ $story->title_draft ?? 'Novel' }}</a>
    <svg class="w-3 h-3 flex-shrink-0" style="color: #5a5368;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span style="color: #d4a04a;" class="font-medium font-mono text-sm">Bab {{ $chapter->chapter_number }}</span>
@endsection

@section('header-right')
@php
    $chapterTokens = $chapter->totalChapterTokens();
    $costUsd = ($chapter->content_input_tokens * 3 + $chapter->content_output_tokens * 15) / 1_000_000;
@endphp
@if($chapterTokens > 0)
<div class="text-xs font-mono" style="color: #8a7f9a;">
    {{ number_format($chapterTokens) }} tokens · ${{ number_format($costUsd, 4) }}
</div>
@endif
@endsection

@section('content')
<div class="max-w-6xl mx-auto px-5 py-6"
    x-data="chapterWorkspace('{{ $chapter->outline_status }}', '{{ $chapter->content_status }}')"
    x-init="init()">

    {{-- Chapter nav --}}
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-2">
            @if($chapter->chapter_number > 1)
            @php $prev = $story->chapters->firstWhere('chapter_number', $chapter->chapter_number - 1); @endphp
            @if($prev)
            <a href="{{ route('admin.novel.chapters.show', [$story, $prev]) }}" class="btn-ghost text-xs flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Bab {{ $prev->chapter_number }}
            </a>
            @endif
            @endif
        </div>

        <div class="flex items-center gap-2">
            <h1 class="font-mono text-base font-semibold" style="color: #d4a04a;">{{ $chapter->chapterLabel() }}</h1>
        </div>

        <div class="flex items-center gap-2">
            @if($chapter->chapter_number < $story->total_chapters_planned)
            @php $next = $story->chapters->firstWhere('chapter_number', $chapter->chapter_number + 1); @endphp
            @if($next)
            <a href="{{ route('admin.novel.chapters.show', [$story, $next]) }}" class="btn-ghost text-xs flex items-center gap-1">
                Bab {{ $next->chapter_number }}
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif
            @endif
        </div>
    </div>

    {{-- Two-column layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

        {{-- LEFT: Outline Panel (35%) --}}
        <div class="lg:col-span-2 space-y-4" x-data="{ editOutline: false }">
            <div class="novel-card p-5 {{ in_array($chapter->outline_status, ['generating']) ? 'generating border' : '' }}">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-mono text-sm font-semibold" style="color: #d4a04a;">Outline</h2>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-full badge-{{ $chapter->outline_status }}">{{ $chapter->outline_status }}</span>
                        @if(in_array($chapter->outline_status, ['ready', 'approved']) && $chapter->outline_content)
                        <button @click="editOutline = !editOutline"
                            class="text-[10px] font-mono px-1.5 py-0.5 rounded border transition-colors"
                            :style="editOutline ? 'border-color: rgba(212,160,74,0.5); color: #d4a04a; background: rgba(212,160,74,0.05);' : 'border-color: rgba(255,255,255,0.1); color: #5a5368;'"
                            style="border-color: rgba(255,255,255,0.1); color: #5a5368;">
                            <span x-text="editOutline ? '✕ Batal' : '✎ Edit'"></span>
                        </button>
                        @endif
                    </div>
                </div>

                @if($chapter->outline_status === 'generating')
                <div class="text-center py-6">
                    <div class="w-8 h-8 rounded-xl mx-auto mb-3 flex items-center justify-center" style="background: rgba(124,92,191,0.15);">
                        <svg class="w-4 h-4 animate-spin" style="color: #7c5cbf;" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </div>
                    <p class="text-xs" style="color: #8a7f9a;">AI sedang buat outline...</p>
                </div>
                @elseif($chapter->outline_content)

                {{-- View mode --}}
                <div x-show="!editOutline" class="font-serif text-sm leading-relaxed mb-4" style="color: #e8e0d0; line-height: 1.75;">
                    {!! nl2br(e($chapter->outline_content)) !!}
                </div>

                {{-- Edit mode --}}
                <div x-show="editOutline" x-cloak>
                    <form method="POST" action="{{ route('admin.novel.chapters.update-outline', [$story, $chapter]) }}" class="space-y-3">
                        @csrf @method('PATCH')
                        <div>
                            <label class="text-[10px] font-mono block mb-1" style="color: #8a7f9a;">JUDUL BAB</label>
                            <input type="text" name="title" value="{{ old('title', $chapter->title) }}"
                                class="novel-input text-xs" placeholder="Judul bab (opsional)">
                        </div>
                        <div>
                            <label class="text-[10px] font-mono block mb-1" style="color: #8a7f9a;">ISI OUTLINE</label>
                            <textarea name="outline_content" class="novel-input text-sm resize-y font-serif" rows="8"
                                style="line-height: 1.65;">{{ old('outline_content', $chapter->outline_content) }}</textarea>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="btn-gold text-xs flex items-center gap-1 flex-1 justify-center">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Simpan
                            </button>
                            <button type="button" @click="editOutline = false" class="btn-ghost text-xs flex-1 text-center">Batal</button>
                        </div>
                    </form>
                </div>
                @else
                <p class="text-sm text-center py-4" style="color: #5a5368;">Outline belum tersedia</p>
                @endif

                {{-- Outline actions --}}
                @if($chapter->outline_status === 'ready')
                <div class="space-y-2 pt-3" x-show="!editOutline" style="border-top: 1px solid rgba(255,255,255,0.05);">
                    <form method="POST" action="{{ route('admin.novel.chapters.approve-outline', [$story, $chapter]) }}">
                        @csrf
                        <button type="submit" class="btn-gold w-full text-sm text-center">✓ Setujui Outline</button>
                    </form>
                    <button onclick="document.getElementById('regen-outline-form').classList.toggle('hidden')" class="btn-ghost w-full text-sm text-center">
                        Generate Ulang
                    </button>
                    <div id="regen-outline-form" class="hidden mt-2">
                        <form method="POST" action="{{ route('admin.novel.chapters.regenerate-outline', [$story, $chapter]) }}" class="space-y-2">
                            @csrf
                            <textarea name="outline_prompt_notes" class="novel-input text-xs resize-none" rows="2"
                                placeholder="Catatan revisi outline...">{{ $chapter->outline_prompt_notes }}</textarea>
                            <button type="submit" class="btn-outline w-full text-sm">Generate Ulang Outline</button>
                        </form>
                    </div>
                </div>
                @elseif($chapter->outline_status === 'approved')
                <div class="pt-3 text-center" x-show="!editOutline" style="border-top: 1px solid rgba(255,255,255,0.05);">
                    <span class="text-xs font-mono" style="color: #95d5b2;">✓ Outline disetujui</span>
                </div>
                @elseif(in_array($chapter->outline_status, ['pending', 'failed']))
                <div class="pt-3">
                    <form method="POST" action="{{ route('admin.novel.chapters.regenerate-outline', [$story, $chapter]) }}" class="space-y-2">
                        @csrf
                        <textarea name="outline_prompt_notes" class="novel-input text-xs resize-none" rows="2"
                            placeholder="Catatan untuk AI (opsional)...">{{ $chapter->outline_prompt_notes }}</textarea>
                        <button type="submit" class="btn-outline w-full text-sm">Generate Outline</button>
                    </form>
                </div>
                @endif
            </div>

            {{-- Token info --}}
            @if($chapter->outline_input_tokens > 0 || $chapter->content_input_tokens > 0)
            <div class="novel-card p-4">
                <p class="text-xs font-mono mb-2" style="color: #8a7f9a;">TOKEN USAGE</p>
                <div class="space-y-1.5 text-xs font-mono" style="color: #e8e0d0;">
                    @if($chapter->outline_input_tokens > 0)
                    <div class="flex justify-between">
                        <span style="color: #5a5368;">Outline in/out</span>
                        <span>{{ number_format($chapter->outline_input_tokens) }} / {{ number_format($chapter->outline_output_tokens) }}</span>
                    </div>
                    @endif
                    @if($chapter->content_input_tokens > 0)
                    <div class="flex justify-between">
                        <span style="color: #5a5368;">Konten in/out</span>
                        <span>{{ number_format($chapter->content_input_tokens) }} / {{ number_format($chapter->content_output_tokens) }}</span>
                    </div>
                    <div class="flex justify-between pt-1" style="border-top: 1px solid rgba(255,255,255,0.05);">
                        <span style="color: #5a5368;">Est. biaya</span>
                        <span style="color: #d4a04a;">${{ number_format(($chapter->content_input_tokens * 3 + $chapter->content_output_tokens * 15) / 1_000_000, 4) }}</span>
                    </div>
                    @endif
                    @if($chapter->content_generation_count > 0)
                    <div class="flex justify-between">
                        <span style="color: #5a5368;">Generate ke-</span>
                        <span {{ $chapter->content_generation_count >= 3 ? 'style="color: #f4a0a0;"' : '' }}>{{ $chapter->content_generation_count }}x</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- RIGHT: Content Panel (65%) --}}
        <div class="lg:col-span-3 space-y-4" x-data="{ editContent: false, submitting: false }">
            <div class="novel-card p-5 {{ in_array($chapter->content_status, ['generating']) ? 'generating border' : '' }}">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-mono text-sm font-semibold" style="color: #d4a04a;">Konten Bab</h2>
                    <div class="flex items-center gap-2">
                        @if($chapter->content_draft)
                        @php
                            $wordCount = str_word_count(strip_tags($chapter->content_draft));
                            $readingMinutes = ceil($wordCount / 200);
                        @endphp
                        <span class="text-[10px] font-mono" x-show="!editContent" style="color: #8a7f9a;">{{ number_format($wordCount) }} kata · ~{{ $readingMinutes }} min</span>
                        @endif
                        @if(in_array($chapter->content_status, ['ready', 'approved', 'revision_requested']) && $chapter->content_draft)
                        <button @click="editContent = !editContent"
                            class="text-[10px] font-mono px-1.5 py-0.5 rounded border transition-colors"
                            :style="editContent ? 'border-color: rgba(212,160,74,0.5); color: #d4a04a; background: rgba(212,160,74,0.05);' : 'border-color: rgba(255,255,255,0.1); color: #5a5368;'"
                            style="border-color: rgba(255,255,255,0.1); color: #5a5368;">
                            <span x-text="editContent ? '✕ Batal' : '✎ Edit'"></span>
                        </button>
                        @endif
                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-full badge-{{ $chapter->content_status }}">{{ $chapter->content_status }}</span>
                    </div>
                </div>

                {{-- Immediate loading state saat form di-submit (sebelum redirect) --}}
                <div x-show="submitting" x-cloak class="text-center py-12">
                    <div class="w-12 h-12 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background: rgba(124,92,191,0.15);">
                        <svg class="w-6 h-6 animate-spin" style="color: #7c5cbf;" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <p class="font-serif text-base mb-2" style="color: #e8e0d0;">✦ Mengirim permintaan ke AI...</p>
                    <p class="text-sm" style="color: #8a7f9a;">Mohon tunggu, jangan klik ulang</p>
                </div>

                @if($chapter->content_status === 'generating')
                <div x-show="!submitting" class="text-center py-12">
                    <div class="w-12 h-12 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background: rgba(124,92,191,0.15);">
                        <svg class="w-6 h-6 animate-spin" style="color: #7c5cbf;" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <p class="font-serif text-base mb-2" style="color: #e8e0d0;">✦ AI sedang menulis konten bab...</p>
                    <p class="text-sm" style="color: #8a7f9a;">Proses 1-3 menit tergantung panjang konten</p>
                </div>
                @elseif($chapter->content_draft)

                {{-- View mode --}}
                <div x-show="!editContent" class="prose-novel text-[15px]" style="max-height: 520px; overflow-y: auto; padding-right: 8px;">
                    {!! nl2br(e($chapter->content_draft)) !!}
                </div>

                {{-- Edit mode --}}
                <div x-show="editContent" x-cloak>
                    <form method="POST" action="{{ route('admin.novel.chapters.update-content', [$story, $chapter]) }}" class="space-y-3">
                        @csrf @method('PATCH')
                        <div>
                            <label class="text-[10px] font-mono block mb-1" style="color: #8a7f9a;">JUDUL BAB</label>
                            <input type="text" name="title" value="{{ old('title', $chapter->title) }}"
                                class="novel-input text-sm" placeholder="Judul bab (opsional)">
                        </div>
                        <div>
                            <label class="text-[10px] font-mono block mb-1" style="color: #8a7f9a;">ISI KONTEN</label>
                            <textarea name="content_draft" class="novel-input text-sm font-serif resize-y" rows="20"
                                style="line-height: 1.75;">{{ old('content_draft', $chapter->content_draft) }}</textarea>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="btn-gold text-sm flex items-center gap-1.5 flex-1 justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Simpan
                            </button>
                            <button type="button" @click="editContent = false" class="btn-ghost text-sm flex-1 text-center">Batal</button>
                        </div>
                    </form>
                </div>

                @elseif($chapter->outline_status === 'approved')
                <div x-show="!submitting" class="text-center py-12">
                    <p class="font-serif text-base mb-4" style="color: #8a7f9a;">Outline disetujui. Siap generate konten.</p>
                    <form method="POST" action="{{ route('admin.novel.chapters.generate-content', [$story, $chapter]) }}" class="inline" @submit="submitting = true">
                        @csrf
                        <button type="submit" class="btn-gold flex items-center gap-2 mx-auto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Generate Konten Bab
                        </button>
                    </form>
                </div>
                @else
                <p class="text-sm text-center py-12" style="color: #5a5368;">Approve outline dulu sebelum generate konten.</p>
                @endif

                {{-- Content actions --}}
                @if(in_array($chapter->content_status, ['ready', 'revision_requested']))
                <div x-show="!editContent">
                <div class="space-y-2 pt-4 mt-4" style="border-top: 1px solid rgba(255,255,255,0.05);">
                    @if($chapter->content_status === 'ready')
                    <div class="flex items-center gap-2 flex-wrap">
                        <form method="POST" action="{{ route('admin.novel.chapters.approve-content', [$story, $chapter]) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn-gold text-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Setujui Konten
                            </button>
                        </form>
                        <button onclick="document.getElementById('revision-form').classList.toggle('hidden')" class="btn-ghost text-sm">
                            Minta Revisi
                        </button>
                        <form method="POST" action="{{ route('admin.novel.chapters.generate-content', [$story, $chapter]) }}" class="inline" @submit="submitting = true">
                            @csrf
                            <button type="submit" class="btn-ghost text-sm">Generate Ulang</button>
                        </form>
                    </div>
                    @endif

                    @if($chapter->content_revision_note)
                    <div class="p-3 rounded-xl text-xs" style="background: rgba(107,45,45,0.2); border: 1px solid rgba(244,160,160,0.15); color: #f4a0a0;">
                        <strong>Catatan revisi:</strong> {{ $chapter->content_revision_note }}
                    </div>
                    @endif

                    <div id="revision-form" class="{{ $chapter->content_status === 'revision_requested' ? '' : 'hidden' }}">
                        <form method="POST" action="{{ route('admin.novel.chapters.request-revision', [$story, $chapter]) }}" class="space-y-2">
                            @csrf
                            <textarea name="revision_note" class="novel-input text-sm resize-none" rows="3"
                                placeholder="Tulis catatan revisi: apa yang perlu diperbaiki AI...">{{ $chapter->content_revision_note }}</textarea>
                            <div class="flex gap-2">
                                <button type="submit" class="btn-danger text-sm flex-1">Simpan Catatan Revisi</button>
                                <form method="POST" action="{{ route('admin.novel.chapters.generate-content', [$story, $chapter]) }}" class="flex-1" @submit="submitting = true">
                                    @csrf
                                    <button type="submit" class="btn-outline w-full text-sm">Generate Ulang dengan Revisi</button>
                                </form>
                            </div>
                        </form>
                    </div>
                </div>
                </div>{{-- /x-show="!editContent" (ready/revision_requested actions) --}}
                @endif

                @if($chapter->content_status === 'approved')
                <div x-show="!editContent" class="pt-3 flex items-center justify-between" style="border-top: 1px solid rgba(255,255,255,0.05);">
                    <span class="text-xs font-mono" style="color: #95d5b2;">✓ Konten disetujui</span>
                    <a href="{{ route('admin.novel.chapters.export-pdf', [$story, $chapter]) }}" target="_blank"
                        class="text-[10px] font-mono flex items-center gap-1 px-2.5 py-1 rounded-lg transition-colors"
                        style="background: rgba(212,160,74,0.1); color: #d4a04a; border: 1px solid rgba(212,160,74,0.25);">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export PDF
                    </a>
                </div>
                @endif

                @if(in_array($chapter->content_status, ['pending', 'failed']) && $chapter->outline_status === 'approved')
                <div x-show="!submitting" class="pt-4 mt-4" style="border-top: 1px solid rgba(255,255,255,0.05);">
                    <form method="POST" action="{{ route('admin.novel.chapters.generate-content', [$story, $chapter]) }}" class="space-y-2" @submit="submitting = true">
                        @csrf
                        <textarea name="content_prompt_notes" class="novel-input text-xs resize-none" rows="2"
                            placeholder="Catatan tambahan untuk AI (opsional)...">{{ $chapter->content_prompt_notes }}</textarea>
                        <button type="submit" class="btn-gold w-full text-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            {{ $chapter->content_status === 'failed' ? 'Coba Lagi' : 'Generate Konten' }}
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function chapterWorkspace(outlineStatus, contentStatus) {
    return {
        outlineStatus,
        contentStatus,
        pollInterval: null,

        init() {
            if (outlineStatus === 'generating' || contentStatus === 'generating') {
                this.startPolling();
            }
        },

        startPolling() {
            const storyId = {{ $story->id }};
            const chapterId = {{ $chapter->id }};
            this.pollInterval = setInterval(() => {
                window.location.reload();
            }, 6000);
        }
    }
}
</script>
@endpush
