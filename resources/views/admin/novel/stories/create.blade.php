@extends('layouts.novel')

@section('title', 'Novel Baru')

@section('breadcrumb')
    <a href="{{ route('admin.novel.stories.index') }}" class="top-nav-link px-0 py-0">Daftar Novel</a>
    <svg class="w-3 h-3 flex-shrink-0" style="color: #5a5368;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span style="color: #d4a04a;" class="font-medium">Novel Baru</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto px-5 py-10">

    <div class="mb-8">
        <h1 class="font-mono text-xl font-semibold mb-1" style="color: #d4a04a;">Buat Novel Baru</h1>
        <p class="text-sm" style="color: #8a7f9a;">AI akan generate gambaran umum cerita secara otomatis setelah kamu mengisi form ini.</p>
    </div>

    <form method="POST" action="{{ route('admin.novel.stories.store') }}" class="space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-2" style="color: #e8e0d0;">Genre Novel</label>
            <select name="genre" class="novel-input" required>
                <option value="">-- Pilih genre --</option>
                <option value="drama_rumah_tangga" {{ old('genre') === 'drama_rumah_tangga' ? 'selected' : '' }}>Drama Rumah Tangga</option>
                <option value="drama_perselingkuhan" {{ old('genre') === 'drama_perselingkuhan' ? 'selected' : '' }}>Drama Perselingkuhan</option>
                <option value="drama_poligami" {{ old('genre') === 'drama_poligami' ? 'selected' : '' }}>Drama Poligami</option>
                <option value="drama_kdrt" {{ old('genre') === 'drama_kdrt' ? 'selected' : '' }}>Drama KDRT</option>
                <option value="drama_pernikahan_kontrak" {{ old('genre') === 'drama_pernikahan_kontrak' ? 'selected' : '' }}>Pernikahan Kontrak</option>
            </select>
            @error('genre') <p class="text-xs mt-1.5" style="color: #f4a0a0;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2" style="color: #e8e0d0;">Jumlah Bab yang Direncanakan</label>
            <input type="number" name="total_chapters_planned" class="novel-input" value="{{ old('total_chapters_planned', 30) }}" min="5" max="200" required>
            <p class="text-xs mt-1.5" style="color: #5a5368;">Rekomendasi: 30-80 bab untuk drama rumah tangga KBM/Fizzo</p>
            @error('total_chapters_planned') <p class="text-xs mt-1.5" style="color: #f4a0a0;">{{ $message }}</p> @enderror
        </div>

        @if($guidelines->isNotEmpty())
        <div>
            <label class="block text-sm font-medium mb-2" style="color: #e8e0d0;">Panduan Penulisan</label>
            <select name="novel_writing_guideline_id" class="novel-input">
                <option value="">-- Tanpa panduan khusus --</option>
                @foreach($guidelines as $g)
                    <option value="{{ $g->id }}" {{ old('novel_writing_guideline_id') == $g->id ? 'selected' : '' }}>
                        {{ $g->name }} ({{ $g->genre }})
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium mb-2" style="color: #e8e0d0;">
                Catatan untuk AI
                <span style="color: #5a5368;" class="font-normal">(opsional)</span>
            </label>
            <textarea name="overview_prompt_notes" class="novel-input resize-none" rows="4"
                placeholder="Contoh: Protagonis bernama Rara, 32 tahun, seorang ibu 2 anak. Setting di Jakarta. Ada unsur bisnis keluarga besar. Twist utama di bab 10: suami ternyata sudah punya anak di luar..."
            >{{ old('overview_prompt_notes') }}</textarea>
            <p class="text-xs mt-1.5" style="color: #5a5368;">Semakin detail, semakin akurat AI memahami visi ceritamu</p>
            @error('overview_prompt_notes') <p class="text-xs mt-1.5" style="color: #f4a0a0;">{{ $message }}</p> @enderror
        </div>

        <div class="pt-4 flex items-center gap-3">
            <button type="submit" class="btn-gold flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Buat & Generate Ringkasan
            </button>
            <a href="{{ route('admin.novel.stories.index') }}" class="btn-ghost">Batal</a>
        </div>
    </form>

</div>
@endsection
