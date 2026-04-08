@extends('layouts.novel')

@section('title', 'Publish ke Novelya — ' . ($story->title_draft ?? $story->title))

@section('breadcrumb')
    <a href="{{ route('admin.novel.stories.index') }}" class="top-nav-link px-0 py-0">Daftar Novel</a>
    <svg class="w-3 h-3 flex-shrink-0" style="color: #5a5368;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('admin.novel.stories.show', $story) }}" class="top-nav-link px-0 py-0">{{ $story->title_draft ?? $story->title ?? 'Novel' }}</a>
    <svg class="w-3 h-3 flex-shrink-0" style="color: #5a5368;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span style="color: #d4a04a;" class="font-medium text-sm">Publish ke Novelya</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto px-5 py-6"
    x-data="publishWorkspace()"
    x-init="init()">

    {{-- Step Indicator --}}
    <div class="flex items-center gap-3 mb-6">
        <template x-for="(s, i) in steps" :key="i">
            <div class="flex items-center gap-3" :class="i > 0 ? 'flex-1' : ''">
                <div x-show="i > 0" class="flex-1 h-px" :style="stepIndex >= i ? 'background: rgba(212,160,74,0.5)' : 'background: rgba(255,255,255,0.06)'"></div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                        :style="stepIndex === i
                            ? 'background: linear-gradient(135deg, #d4a04a, #b8872e); color: #0e0c12;'
                            : (stepIndex > i
                                ? 'background: rgba(45,106,79,0.4); color: #95d5b2;'
                                : 'background: rgba(255,255,255,0.06); color: #5a5368;')">
                        <template x-if="stepIndex > i">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </template>
                        <template x-if="stepIndex <= i">
                            <span x-text="i + 1"></span>
                        </template>
                    </div>
                    <span class="text-xs font-medium" :style="stepIndex >= i ? 'color: #e8e0d0' : 'color: #5a5368'" x-text="s"></span>
                </div>
            </div>
        </template>
    </div>

    {{-- ─── STEP 1: FORM ─── --}}
    <div x-show="step === 'form'" x-transition>
        <div class="novel-card p-6 space-y-5">
            <h2 class="font-serif text-lg font-semibold" style="color: #d4a04a;">Informasi Publikasi</h2>

            {{-- Author Search --}}
            <div x-data="authorSearch()" class="relative">
                <label class="block text-xs font-medium mb-1.5" style="color: #8a7f9a;">Author (User Novelya) <span style="color: #f4a0a0;">*</span></label>

                {{-- Selected state --}}
                <template x-if="selected">
                    <div class="novel-input text-sm flex items-center justify-between gap-2 cursor-default">
                        <div class="min-w-0">
                            <span class="font-medium truncate block" style="color: #e8e0d0;" x-text="selected.name"></span>
                            <span class="text-[11px] font-mono truncate block" style="color: #5a5368;" x-text="selected.email"></span>
                        </div>
                        <button type="button" @click="clear()" class="flex-shrink-0 w-5 h-5 flex items-center justify-center rounded-full transition-colors hover:bg-white/10" style="color: #8a7f9a;">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>

                {{-- Search input --}}
                <template x-if="!selected">
                    <div class="relative">
                        <input type="text" x-model="query" @input.debounce.300ms="search()"
                            @focus="if(query.length >= 2) open = true"
                            @keydown.escape="open = false"
                            placeholder="Ketik nama atau email (min. 2 karakter)..."
                            class="novel-input text-sm pr-8" autocomplete="off">
                        <div class="absolute right-3 top-1/2 -translate-y-1/2">
                            <template x-if="loading">
                                <div class="w-3.5 h-3.5 border border-t-transparent rounded-full animate-spin" style="border-color: #8a7f9a; border-top-color: transparent;"></div>
                            </template>
                            <template x-if="!loading">
                                <svg class="w-3.5 h-3.5" style="color: #5a5368;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </template>
                        </div>

                        {{-- Dropdown results --}}
                        <div x-show="open && results.length > 0" x-cloak
                            @click.outside="open = false"
                            class="absolute z-20 w-full mt-1 rounded-xl overflow-hidden shadow-xl"
                            style="background: #221d30; border: 1px solid rgba(212,160,74,0.2);">
                            <template x-for="user in results" :key="user.id">
                                <button type="button" @click="pick(user)"
                                    class="w-full text-left px-4 py-2.5 transition-colors hover:bg-white/[0.05] flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                                        style="background: rgba(124,92,191,0.2); color: #a688e0;"
                                        x-text="user.name.charAt(0).toUpperCase()"></div>
                                    <div class="min-w-0">
                                        <p class="text-sm truncate" style="color: #e8e0d0;" x-text="user.name"></p>
                                        <p class="text-[11px] font-mono truncate" style="color: #5a5368;" x-text="user.email"></p>
                                    </div>
                                </button>
                            </template>
                        </div>

                        {{-- No results --}}
                        <div x-show="open && results.length === 0 && !loading && query.length >= 2" x-cloak
                            class="absolute z-20 w-full mt-1 rounded-xl px-4 py-3 text-xs"
                            style="background: #221d30; border: 1px solid rgba(255,255,255,0.06); color: #5a5368;">
                            Tidak ada user ditemukan
                        </div>
                    </div>
                </template>
            </div>

            {{-- Category --}}
            <div>
                <label class="block text-xs font-medium mb-1.5" style="color: #8a7f9a;">Kategori <span style="color: #f4a0a0;">*</span></label>
                <select x-model="form.category_id" class="novel-input text-sm">
                    <option value="">— Pilih Kategori —</option>
                    @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Cover Upload --}}
            <div>
                <label class="block text-xs font-medium mb-1.5" style="color: #8a7f9a;">Cover Image <span style="color: #f4a0a0;">*</span></label>
                <div class="flex items-start gap-4">
                    {{-- Upload zone --}}
                    <label class="relative flex flex-col items-center justify-center w-40 h-56 rounded-xl border-2 border-dashed cursor-pointer transition-all"
                        :style="coverPreview
                            ? 'border-color: rgba(212,160,74,0.4); background: transparent;'
                            : 'border-color: rgba(255,255,255,0.1); background: rgba(255,255,255,0.02);'"
                        @dragover.prevent="$el.style.borderColor='rgba(212,160,74,0.6)'"
                        @dragleave.prevent="$el.style.borderColor=coverPreview ? 'rgba(212,160,74,0.4)' : 'rgba(255,255,255,0.1)'"
                        @drop.prevent="handleCoverDrop($event)">
                        <template x-if="coverPreview">
                            <img :src="coverPreview" class="absolute inset-0 w-full h-full object-cover rounded-xl">
                        </template>
                        <template x-if="!coverPreview && !coverUploading">
                            <div class="text-center p-3">
                                <svg class="w-8 h-8 mx-auto mb-2" style="color: #5a5368;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-xs" style="color: #5a5368;">Klik atau drop<br>JPEG/PNG/WebP<br>Max 2MB</span>
                            </div>
                        </template>
                        <template x-if="coverUploading">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-6 h-6 border-2 border-t-transparent rounded-full animate-spin" style="border-color: #d4a04a; border-top-color: transparent;"></div>
                                <span class="text-xs" style="color: #8a7f9a;">Uploading...</span>
                            </div>
                        </template>
                        <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="handleCoverSelect($event)">
                    </label>
                    <div class="flex-1 text-xs space-y-1" style="color: #5a5368;">
                        <p>Format: JPEG, PNG, atau WebP</p>
                        <p>Ukuran max: 2MB</p>
                        <p>Rasio ideal: 2:3 (cover buku)</p>
                        <template x-if="coverError">
                            <p class="mt-2 font-medium" style="color: #f4a0a0;" x-text="coverError"></p>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Synopsis --}}
            <div>
                <label class="block text-xs font-medium mb-1.5" style="color: #8a7f9a;">
                    Sinopsis <span style="color: #f4a0a0;">*</span>
                    <span class="float-right font-mono" :style="form.synopsis.length > 2048 ? 'color: #f4a0a0' : (form.synopsis.length > 1800 ? 'color: #fbbf24' : 'color: #5a5368')"
                        x-text="form.synopsis.length + '/2048'"></span>
                </label>
                <textarea x-model="form.synopsis" rows="5" class="novel-input text-sm font-serif" style="line-height: 1.7;" maxlength="2048"></textarea>
            </div>

            {{-- Tags --}}
            <div>
                <label class="block text-xs font-medium mb-1.5" style="color: #8a7f9a;">Tags <span style="color: #5a5368;">(opsional, pisahkan dengan koma)</span></label>
                <input type="text" x-model="form.tags" class="novel-input text-sm" placeholder="drama, cinta, keluarga" maxlength="512">
            </div>

            {{-- Publish Status --}}
            <div>
                <label class="block text-xs font-medium mb-1.5" style="color: #8a7f9a;">Status di Novelya</label>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" x-model="form.is_published" value="0" class="accent-yellow-500">
                        <span class="text-sm" style="color: #e8e0d0;">Draft</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" x-model="form.is_published" value="1" class="accent-yellow-500">
                        <span class="text-sm" style="color: #e8e0d0;">Publish Langsung</span>
                    </label>
                </div>
            </div>

            {{-- Chapter Info --}}
            <div class="pt-3" style="border-top: 1px solid rgba(255,255,255,0.04);">
                <p class="text-xs" style="color: #5a5368;">
                    <span class="font-mono font-semibold" style="color: #d4a04a;">{{ $approvedChapters->count() }}</span>
                    chapter approved siap dikirim
                </p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between pt-3" style="border-top: 1px solid rgba(255,255,255,0.04);">
                <a href="{{ route('admin.novel.stories.show', $story) }}" class="btn-ghost text-sm">Kembali</a>
                <button @click="goToPreview()" class="btn-gold text-sm flex items-center gap-2"
                    :disabled="!canPreview"
                    :style="!canPreview ? 'opacity: 0.4; cursor: not-allowed;' : ''">
                    <span>Preview</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- ─── STEP 2: PREVIEW ─── --}}
    <div x-show="step === 'preview'" x-transition x-cloak>
        <div class="novel-card p-6 space-y-5">
            <h2 class="font-serif text-lg font-semibold" style="color: #d4a04a;">Preview Publikasi</h2>

            {{-- Cover + Meta --}}
            <div class="flex gap-5">
                <img :src="coverPreview" class="w-32 h-44 object-cover rounded-xl flex-shrink-0 border" style="border-color: rgba(212,160,74,0.2);">
                <div class="space-y-2 flex-1 min-w-0">
                    <h3 class="font-serif text-base font-semibold truncate" style="color: #e8e0d0;" x-text="previewData.title"></h3>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs" style="color: #8a7f9a;">
                        <span>Author: <span style="color: #e8e0d0;" x-text="previewData.author_name"></span></span>
                        <span>Kategori: <span style="color: #e8e0d0;" x-text="previewData.category_name"></span></span>
                    </div>
                    <template x-if="previewData.tags">
                        <div class="flex flex-wrap gap-1.5 mt-1">
                            <template x-for="tag in previewData.tags.split(',')" :key="tag">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium"
                                    style="background: rgba(124,92,191,0.2); color: #a688e0;" x-text="tag.trim()"></span>
                            </template>
                        </div>
                    </template>
                    <div class="mt-1">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium"
                            :style="previewData.is_published
                                ? 'background: rgba(45,106,79,0.4); color: #95d5b2;'
                                : 'background: rgba(100,100,120,0.2); color: #8a7f9a;'"
                            x-text="previewData.is_published ? 'Publish Langsung' : 'Draft'"></span>
                    </div>
                </div>
            </div>

            {{-- Synopsis --}}
            <div>
                <p class="text-xs font-medium mb-1" style="color: #8a7f9a;">Sinopsis</p>
                <p class="text-sm font-serif leading-relaxed" style="color: #e8e0d0;" x-text="previewData.synopsis"></p>
            </div>

            {{-- Chapter List --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-medium" style="color: #8a7f9a;">Daftar Chapter</p>
                    <p class="text-xs font-mono" style="color: #5a5368;">
                        <span x-text="previewData.total_chapters"></span> bab ·
                        <span x-text="previewData.total_words?.toLocaleString()"></span> kata
                    </p>
                </div>
                <div class="max-h-64 overflow-y-auto rounded-xl" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04);">
                    <table class="w-full text-xs">
                        <thead class="sticky top-0" style="background: #221d30;">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium" style="color: #5a5368;">No</th>
                                <th class="px-3 py-2 text-left font-medium" style="color: #5a5368;">Judul</th>
                                <th class="px-3 py-2 text-right font-medium" style="color: #5a5368;">Kata</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="ch in previewData.chapters" :key="ch.number">
                                <tr style="border-top: 1px solid rgba(255,255,255,0.03);">
                                    <td class="px-3 py-1.5 font-mono" style="color: #5a5368;" x-text="ch.number"></td>
                                    <td class="px-3 py-1.5" style="color: #e8e0d0;" x-text="ch.title"></td>
                                    <td class="px-3 py-1.5 text-right font-mono" style="color: #8a7f9a;" x-text="ch.word_count.toLocaleString()"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between pt-3" style="border-top: 1px solid rgba(255,255,255,0.04);">
                <button @click="step = 'form'; stepIndex = 0" class="btn-ghost text-sm">Kembali ke Form</button>
                <button @click="executePublish()" class="btn-gold text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <span>Publish ke Novelya</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ─── STEP 3: PUBLISHING ─── --}}
    <div x-show="step === 'publishing'" x-transition x-cloak>
        <div class="novel-card p-8 text-center space-y-5">
            <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center generating"
                style="background: rgba(124,92,191,0.15); border: 2px solid rgba(124,92,191,0.3);">
                <svg class="w-7 h-7 animate-spin" style="color: #a688e0;" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>
            <p class="font-serif text-base font-medium" style="color: #e8e0d0;" x-text="publishStatus"></p>
            <p class="text-xs" style="color: #5a5368;">Jangan tutup halaman ini...</p>
        </div>
    </div>

    {{-- ─── STEP 4: DONE ─── --}}
    <div x-show="step === 'done'" x-transition x-cloak>
        <div class="novel-card p-8 text-center space-y-5">
            {{-- Success --}}
            <template x-if="publishResult?.success">
                <div class="space-y-4">
                    <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center"
                        style="background: rgba(45,106,79,0.3); border: 2px solid rgba(149,213,178,0.3);">
                        <svg class="w-8 h-8" style="color: #95d5b2;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h3 class="font-serif text-lg font-semibold" style="color: #95d5b2;">Berhasil Publish!</h3>
                    <p class="text-sm" style="color: #8a7f9a;" x-text="publishResult.message"></p>
                    <a href="{{ route('admin.novel.stories.show', $story) }}" class="btn-gold text-sm inline-flex items-center gap-2 mt-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                        Kembali ke Workspace
                    </a>
                </div>
            </template>

            {{-- Partial Failure --}}
            <template x-if="publishResult && !publishResult.success && publishResult.partial">
                <div class="space-y-4">
                    <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center"
                        style="background: rgba(123,79,18,0.3); border: 2px solid rgba(255,209,102,0.3);">
                        <svg class="w-8 h-8" style="color: #ffd166;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                    <h3 class="font-serif text-lg font-semibold" style="color: #ffd166;">Publish Sebagian</h3>
                    <p class="text-sm" style="color: #8a7f9a;">
                        Cerita berhasil dibuat, tapi pengiriman chapter gagal.
                        <span class="font-mono" style="color: #ffd166;" x-text="publishResult.chapters_sent + '/' + publishResult.total_chapters"></span> chapter terkirim.
                    </p>
                    <p class="text-xs font-mono p-3 rounded-lg text-left" style="background: rgba(107,45,45,0.2); color: #f4a0a0;" x-text="publishResult.message"></p>
                    <div class="flex items-center justify-center gap-3 mt-2">
                        <a href="{{ route('admin.novel.stories.show', $story) }}" class="btn-ghost text-sm">Kembali</a>
                        <button @click="retryChapters()" class="btn-gold text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Retry Kirim Chapter
                        </button>
                    </div>
                </div>
            </template>

            {{-- Full Failure --}}
            <template x-if="publishResult && !publishResult.success && !publishResult.partial">
                <div class="space-y-4">
                    <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center"
                        style="background: rgba(107,45,45,0.3); border: 2px solid rgba(244,160,160,0.3);">
                        <svg class="w-8 h-8" style="color: #f4a0a0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <h3 class="font-serif text-lg font-semibold" style="color: #f4a0a0;">Gagal Publish</h3>
                    <p class="text-xs font-mono p-3 rounded-lg text-left" style="background: rgba(107,45,45,0.2); color: #f4a0a0;" x-text="publishResult.message"></p>
                    <div class="flex items-center justify-center gap-3 mt-2">
                        <a href="{{ route('admin.novel.stories.show', $story) }}" class="btn-ghost text-sm">Kembali</a>
                        <button @click="step = 'form'; stepIndex = 0" class="btn-gold text-sm">Coba Lagi</button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function publishWorkspace() {
    return {
        step: 'form',
        stepIndex: 0,
        steps: ['Form', 'Preview', 'Publish'],

        form: {
            author_id: '{{ $story->novelya_author_id ?? '' }}',
            category_id: '{{ $story->novelya_category_id ?? '' }}',
            cover_path: '{{ $story->novelya_cover_path ?? '' }}',
            synopsis: @js($story->synopsis ?? ''),
            tags: '',
            is_published: '0',
        },

        coverPreview: {!! $story->novelya_cover_path ? "'" . app(\App\Services\NovelyaPublishService::class)->coverUrl($story->novelya_cover_path) . "'" : 'null' !!},
        coverUploading: false,
        coverError: null,

        previewData: {},
        previewLoading: false,

        publishStatus: '',
        publishResult: null,

        init() {},

        get canPreview() {
            return this.form.author_id
                && this.form.category_id
                && this.form.cover_path
                && this.form.synopsis.length >= 25
                && this.form.synopsis.length <= 2048;
        },

        async handleCoverSelect(event) {
            const file = event.target.files[0];
            if (file) await this.uploadCover(file);
        },

        async handleCoverDrop(event) {
            const file = event.dataTransfer.files[0];
            if (file) await this.uploadCover(file);
        },

        async uploadCover(file) {
            this.coverError = null;

            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                this.coverError = 'Format harus JPEG, PNG, atau WebP.';
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                this.coverError = 'Ukuran file max 2MB.';
                return;
            }

            this.coverUploading = true;
            const fd = new FormData();
            fd.append('cover', file);

            try {
                const res = await fetch('{{ route("admin.novel.stories.publish.upload-cover", $story) }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: fd,
                });
                const data = await res.json();
                if (res.ok) {
                    this.form.cover_path = data.path;
                    this.coverPreview = data.url;
                } else {
                    this.coverError = data.message || 'Upload gagal.';
                }
            } catch (e) {
                this.coverError = 'Koneksi gagal. Coba lagi.';
            } finally {
                this.coverUploading = false;
            }
        },

        async goToPreview() {
            if (!this.canPreview) return;
            this.previewLoading = true;

            try {
                const res = await fetch('{{ route("admin.novel.stories.publish.preview", $story) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        author_id: this.form.author_id,
                        category_id: this.form.category_id,
                        cover_path: this.form.cover_path,
                        synopsis: this.form.synopsis,
                        tags: this.form.tags || null,
                        is_published: this.form.is_published === '1',
                    }),
                });
                const data = await res.json();
                if (res.ok) {
                    this.previewData = data;
                    this.step = 'preview';
                    this.stepIndex = 1;
                } else {
                    const errors = data.errors ? Object.values(data.errors).flat().join(', ') : (data.message || 'Validasi gagal.');
                    alert(errors);
                }
            } catch (e) {
                alert('Koneksi gagal.');
            } finally {
                this.previewLoading = false;
            }
        },

        async executePublish() {
            this.step = 'publishing';
            this.stepIndex = 2;
            this.publishStatus = 'Membuat cerita di Novelya...';

            try {
                const res = await fetch('{{ route("admin.novel.stories.publish.execute", $story) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        author_id: this.form.author_id,
                        category_id: this.form.category_id,
                        cover_path: this.form.cover_path,
                        synopsis: this.form.synopsis,
                        tags: this.form.tags || null,
                        is_published: this.form.is_published === '1',
                    }),
                });
                this.publishResult = await res.json();
            } catch (e) {
                this.publishResult = { success: false, partial: false, message: 'Koneksi gagal: ' + e.message };
            }

            this.step = 'done';
        },

        async retryChapters() {
            this.step = 'publishing';
            this.publishStatus = 'Mengirim ulang chapter...';

            try {
                const res = await fetch('{{ route("admin.novel.stories.publish.retry-chapters", $story) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ is_published: this.form.is_published === '1' }),
                });
                this.publishResult = await res.json();
            } catch (e) {
                this.publishResult = { success: false, partial: true, chapters_sent: 0, total_chapters: 0, message: 'Koneksi gagal: ' + e.message };
            }

            this.step = 'done';
        },
    };
}

function authorSearch() {
    return {
        query: '',
        results: [],
        selected: @js($selectedAuthor ?? null),
        open: false,
        loading: false,

        init() {
            // Sync selected author to parent form on init and on change
            this.$watch('selected', val => {
                const workspace = Alpine.$data(this.$root.closest('[x-data*="publishWorkspace"]'));
                if (workspace) workspace.form.author_id = val ? val.id : '';
            });

            // Set initial author_id if pre-selected
            if (this.selected) {
                const workspace = Alpine.$data(this.$root.closest('[x-data*="publishWorkspace"]'));
                if (workspace) workspace.form.author_id = this.selected.id;
            }
        },

        async search() {
            if (this.query.length < 2) {
                this.results = [];
                this.open = false;
                return;
            }

            this.loading = true;
            try {
                const url = new URL('{{ route("admin.novel.stories.publish.search-authors", $story) }}');
                url.searchParams.set('q', this.query);
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json' },
                });
                this.results = await res.json();
                this.open = true;
            } catch (e) {
                this.results = [];
            } finally {
                this.loading = false;
            }
        },

        pick(user) {
            this.selected = user;
            this.open = false;
            this.query = '';
            this.results = [];

            // Sync to parent publishWorkspace form
            const workspace = Alpine.$data(this.$root.closest('[x-data*="publishWorkspace"]'));
            if (workspace) workspace.form.author_id = user.id;
        },

        clear() {
            this.selected = null;
            this.query = '';
            this.results = [];

            const workspace = Alpine.$data(this.$root.closest('[x-data*="publishWorkspace"]'));
            if (workspace) workspace.form.author_id = '';
        },
    };
}
</script>
@endpush
