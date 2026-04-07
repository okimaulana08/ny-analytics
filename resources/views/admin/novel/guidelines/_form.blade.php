{{-- Shared form for create/edit guideline --}}

<div class="space-y-5">
    <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
            <label class="block text-sm font-medium mb-2" style="color: #e8e0d0;">Nama Panduan</label>
            <input type="text" name="name" class="novel-input" value="{{ old('name', $guideline->name ?? '') }}" required
                placeholder="Contoh: Panduan Drama Rumah Tangga Indonesia v1">
            @error('name') <p class="text-xs mt-1" style="color: #f4a0a0;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2" style="color: #e8e0d0;">Genre</label>
            <select name="genre" class="novel-input" required>
                @foreach([
                    'drama_rumah_tangga'       => 'Drama Rumah Tangga',
                    'drama_perselingkuhan'     => 'Drama Perselingkuhan',
                    'drama_poligami'           => 'Drama Poligami',
                    'drama_kdrt'               => 'Drama KDRT',
                    'drama_pernikahan_kontrak' => 'Pernikahan Kontrak',
                    'horror'                   => 'Horror',
                    'action_adventure'         => 'Action / Adventure',
                    'thriller'                 => 'Thriller',
                    'fantasy'                  => 'Fantasy',
                    'comedy'                   => 'Comedy',
                ] as $value => $label)
                <option value="{{ $value }}" {{ old('genre', $guideline->genre ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2" style="color: #e8e0d0;">Sudut Pandang Narasi</label>
            <select name="narrative_pov" class="novel-input">
                <option value="first_person" {{ old('narrative_pov', $guideline->narrative_pov ?? 'first_person') === 'first_person' ? 'selected' : '' }}>Orang Pertama (AKU)</option>
                <option value="third_person" {{ old('narrative_pov', $guideline->narrative_pov ?? '') === 'third_person' ? 'selected' : '' }}>Orang Ketiga</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2" style="color: #e8e0d0;">Target Kata per Bab</label>
            <input type="number" name="target_chapter_word_count" class="novel-input"
                value="{{ old('target_chapter_word_count', $guideline->target_chapter_word_count ?? 1500) }}"
                min="500" max="10000" required>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="w-4 h-4 rounded"
                    {{ old('is_active', $guideline->is_active ?? true) ? 'checked' : '' }}>
                <span class="text-sm" style="color: #e8e0d0;">Aktif (digunakan saat generate)</span>
            </label>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium mb-2" style="color: #e8e0d0;">System Prompt Prefix</label>
        <textarea name="system_prompt_prefix" class="novel-input resize-none" rows="4"
            placeholder="Kamu adalah penulis novel Indonesia berpengalaman...">{{ old('system_prompt_prefix', $guideline->system_prompt_prefix ?? '') }}</textarea>
        <p class="text-xs mt-1" style="color: #5a5368;">Instruksi utama persona AI yang dikirim sebagai system prompt</p>
    </div>

    <div>
        <label class="block text-sm font-medium mb-2" style="color: #e8e0d0;">Gaya Bahasa</label>
        <textarea name="language_style" class="novel-input resize-none" rows="3"
            placeholder="Bahasa Indonesia sehari-hari, kalimat pendek maks 20 kata...">{{ old('language_style', $guideline->language_style ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium mb-2" style="color: #e8e0d0;">Struktur Plot</label>
        <textarea name="plot_structure_notes" class="novel-input resize-none" rows="5"
            placeholder="3 babak: Babak 1 (Ch.1-20): ...">{{ old('plot_structure_notes', $guideline->plot_structure_notes ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium mb-2" style="color: #e8e0d0;">
            Arketip Karakter
            <span style="color: #5a5368;" class="font-normal">(JSON array)</span>
        </label>
        <textarea name="character_archetypes" class="novel-input resize-none font-mono text-xs" rows="8"
            placeholder='[{"name": "Protagonis", "description": "..."}, ...]'>{{ old('character_archetypes', isset($guideline) && $guideline->character_archetypes ? json_encode($guideline->character_archetypes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
        @error('character_archetypes') <p class="text-xs mt-1" style="color: #f4a0a0;">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-2" style="color: #e8e0d0;">Konten Terlarang</label>
        <textarea name="forbidden_content" class="novel-input resize-none" rows="3"
            placeholder="Deskripsi fisik berlebihan di awal chapter; ...">{{ old('forbidden_content', $guideline->forbidden_content ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium mb-2" style="color: #e8e0d0;">Panduan Konten Lengkap</label>
        <textarea name="content_guidelines" class="novel-input resize-none font-mono text-xs" rows="12"
            placeholder="## PANDUAN PENULISAN DRAMA RUMAH TANGGA INDONESIA...">{{ old('content_guidelines', $guideline->content_guidelines ?? '') }}</textarea>
        <p class="text-xs mt-1" style="color: #5a5368;">Markdown diperbolehkan. Hook library, template emosi, dll.</p>
    </div>
</div>
