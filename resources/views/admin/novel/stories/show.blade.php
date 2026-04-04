@extends('layouts.novel')

@section('title', ($story->title_draft ?? $story->title ?? 'Novel') . ' — Workspace')

@section('breadcrumb')
    <a href="{{ route('admin.novel.stories.index') }}" class="top-nav-link px-0 py-0">Daftar Novel</a>
    <svg class="w-3 h-3 flex-shrink-0" style="color: #5a5368;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span style="color: #e8e0d0;" class="truncate max-w-xs font-medium text-sm">{{ $story->title_draft ?? $story->title ?? 'Novel Baru' }}</span>
@endsection

@section('header-right')
<div class="flex items-center gap-2 text-xs font-mono" style="color: #8a7f9a;">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
    {{ number_format($story->total_input_tokens + $story->total_output_tokens) }} tokens
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
        <div class="novel-card p-10 text-center border generating" x-show="status === 'overview_pending' || status === 'draft'" id="generating-box">
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
        <div class="space-y-5">
            <div class="novel-card p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="font-mono text-base font-semibold" style="color: #d4a04a;">📖 Gambaran Umum Cerita</h2>
                    @if($story->status === 'overview_approved')
                        <span class="text-xs px-2.5 py-1 rounded-full font-mono badge-approved">✓ Disetujui</span>
                    @endif
                </div>

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

            {{-- Approval bar --}}
            @if($story->status === 'overview_ready')
            <div class="approval-bar">
                <div class="flex items-center gap-3 justify-center flex-wrap">
                    <form method="POST" action="{{ route('admin.novel.stories.approve-overview', $story) }}" class="inline">
                        @csrf
                        <button type="submit" class="btn-gold flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Setujui Ringkasan
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.novel.stories.regenerate-overview', $story) }}" class="inline">
                        @csrf
                        <button type="submit" class="btn-outline flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Generate Ulang
                        </button>
                    </form>

                    <button onclick="document.getElementById('reject-form').classList.toggle('hidden')" class="btn-ghost">
                        Tolak + Catatan
                    </button>
                </div>

                <div id="reject-form" class="hidden mt-4 max-w-lg mx-auto">
                    <form method="POST" action="{{ route('admin.novel.stories.reject-overview', $story) }}" class="space-y-3">
                        @csrf
                        <textarea name="rejection_notes" class="novel-input resize-none" rows="3"
                            placeholder="Catatan untuk AI saat generate ulang...">{{ $story->overview_prompt_notes }}</textarea>
                        <button type="submit" class="btn-danger w-full">Tolak & Simpan Catatan</button>
                    </form>
                </div>
            </div>
            @endif

            @if($story->status === 'overview_approved')
            <div class="approval-bar">
                <div class="text-center">
                    <form method="POST" action="{{ route('admin.novel.stories.generate-outlines', $story) }}" class="inline">
                        @csrf
                        <button type="submit" class="btn-gold flex items-center gap-2 mx-auto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Generate Outline Semua Bab
                        </button>
                    </form>
                    <p class="text-xs mt-2" style="color: #5a5368;">AI akan generate outline untuk semua {{ $story->total_chapters_planned }} bab sekaligus</p>
                </div>
            </div>
            @endif
        </div>
        @endif

    {{-- =================== STAGE 2: OUTLINE =================== --}}
    @elseif(in_array($story->status, ['outline_pending', 'outline_ready', 'outline_approved']))

        @if($story->status === 'outline_pending')
        <div class="novel-card p-10 text-center border generating">
            <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background: rgba(124,92,191,0.15);">
                <svg class="w-7 h-7 animate-spin" style="color: #7c5cbf;" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <p class="font-serif text-lg mb-2" style="color: #e8e0d0;">✦ AI sedang menyusun outline semua bab...</p>
            <p class="text-sm" style="color: #8a7f9a;">Proses ini membutuhkan waktu 1-2 menit</p>
        </div>
        @else
        {{-- Outline ready/approved --}}
        <div class="novel-card p-5 mb-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-mono text-base font-semibold" style="color: #d4a04a;">Outline Bab</h2>
                @if($story->status === 'outline_approved')
                    <span class="text-xs px-2.5 py-1 rounded-full font-mono badge-approved">✓ Semua Disetujui</span>
                @else
                    <span class="text-xs" style="color: #8a7f9a;">Review setiap bab, lalu setujui semua sekaligus</span>
                @endif
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($story->chapters as $chapter)
                <a href="{{ route('admin.novel.chapters.show', [$story, $chapter]) }}"
                    class="block p-3 rounded-xl transition-all cursor-pointer"
                    style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);"
                    onmouseover="this.style.borderColor='rgba(212,160,74,0.3)'"
                    onmouseout="this.style.borderColor='rgba(255,255,255,0.06)'">
                    <p class="text-xs font-mono mb-1" style="color: #8a7f9a;">Bab {{ $chapter->chapter_number }}</p>
                    <p class="text-xs font-medium mb-2" style="color: #e8e0d0; line-height: 1.4;">{{ Str::limit($chapter->title ?? 'Belum ada judul', 40) }}</p>
                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-full badge-{{ $chapter->outline_status }}">{{ $chapter->outline_status }}</span>
                </a>
                @endforeach
            </div>
        </div>

        @if($story->status === 'outline_ready')
        <div class="approval-bar">
            <div class="text-center">
                <form method="POST" action="{{ route('admin.novel.stories.approve-outlines', $story) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn-gold flex items-center gap-2 mx-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Setujui Semua Outline
                    </button>
                </form>
                <p class="text-xs mt-2" style="color: #5a5368;">Atau klik masing-masing bab untuk review dan approve individual</p>
            </div>
        </div>
        @endif

        @if($story->status === 'outline_approved')
        <div class="novel-card p-5 text-center" style="border-color: rgba(149,213,178,0.2);">
            <p class="font-serif text-base mb-2" style="color: #95d5b2;">✓ Outline disetujui! Kini generate konten per bab.</p>
            <p class="text-sm" style="color: #8a7f9a;">Klik bab di atas untuk mulai generate konten satu per satu.</p>
        </div>
        @endif
        @endif

    {{-- =================== STAGE 3: CONTENT =================== --}}
    @else
        <div class="novel-card p-5 mb-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-mono text-base font-semibold" style="color: #d4a04a;">Konten Bab</h2>
                @php
                    $approvedCount = $story->chapters->where('content_status', 'approved')->count();
                    $total = $story->chapters->count();
                @endphp
                <span class="text-xs font-mono" style="color: #8a7f9a;">{{ $approvedCount }}/{{ $total }} bab approved</span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($story->chapters as $chapter)
                <a href="{{ route('admin.novel.chapters.show', [$story, $chapter]) }}"
                    class="block p-3 rounded-xl transition-all cursor-pointer"
                    style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);"
                    onmouseover="this.style.borderColor='rgba(212,160,74,0.3)'"
                    onmouseout="this.style.borderColor='rgba(255,255,255,0.06)'">
                    <p class="text-xs font-mono mb-1" style="color: #8a7f9a;">Bab {{ $chapter->chapter_number }}</p>
                    <p class="text-xs font-medium mb-2" style="color: #e8e0d0; line-height: 1.4;">{{ Str::limit($chapter->title ?? 'Belum ada judul', 40) }}</p>
                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-full badge-{{ $chapter->content_status }}">{{ $chapter->content_status }}</span>
                </a>
                @endforeach
            </div>
        </div>
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
        pollInterval: null,

        init() {
            const pendingStatuses = ['overview_pending', 'outline_pending'];
            if (pendingStatuses.includes(this.status)) {
                this.startPolling();
            }
        },

        startPolling() {
            this.pollInterval = setInterval(async () => {
                try {
                    const res = await fetch(`/admin/novel/stories/${storyId}/status`);
                    const data = await res.json();
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
