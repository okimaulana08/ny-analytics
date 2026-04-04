<?php

namespace App\Services;

use App\Models\NovelChapter;
use App\Models\NovelStory;
use App\Models\NovelWritingGuideline;

class NovelPromptBuilder
{
    public function buildSystemPrompt(?NovelWritingGuideline $guideline): string
    {
        if ($guideline?->system_prompt_prefix) {
            $base = $guideline->system_prompt_prefix;
        } else {
            $base = 'Kamu adalah penulis novel Indonesia berpengalaman khusus genre Drama Rumah Tangga yang laris di platform KBM App dan Fizzo. Target pembacamu adalah wanita dan ibu rumah tangga usia 30-55 tahun. Tulisan kamu menggunakan sudut pandang orang pertama (AKU), Bahasa Indonesia sehari-hari yang mengalir natural, emosional namun tidak berlebihan, dan selalu menjaga konsistensi karakter.';
        }

        if ($guideline?->content_guidelines) {
            $base .= "\n\n## PANDUAN PENULISAN\n".$guideline->content_guidelines;
        }

        return $base;
    }

    /**
     * @return array{system: string, user: string}
     */
    public function buildOverviewPrompt(NovelStory $story, ?NovelWritingGuideline $guideline): array
    {
        $system = $this->buildSystemPrompt($guideline);

        $genreLabel = $story->genreLabel();
        $totalChapters = $story->total_chapters_planned;
        $notes = $story->overview_prompt_notes ? "\nCatatan tambahan dari editor: {$story->overview_prompt_notes}" : '';

        $characterArchetypes = '';
        if ($guideline?->character_archetypes) {
            $archetypes = collect($guideline->character_archetypes)
                ->map(fn ($a) => "- {$a['name']}: {$a['description']}")
                ->join("\n");
            $characterArchetypes = "\n\nARKETIP KARAKTER YANG TERSEDIA:\n{$archetypes}";
        }

        $plotNotes = $guideline?->plot_structure_notes
            ? "\n\nSTRUKTUR PLOT:\n{$guideline->plot_structure_notes}"
            : '';

        $user = <<<EOT
Buatkan gambaran umum untuk novel genre {$genreLabel} dengan total {$totalChapters} bab.{$notes}{$characterArchetypes}{$plotNotes}

Kembalikan HANYA JSON valid dengan struktur berikut (tanpa markdown, tanpa penjelasan tambahan):
{
  "title_draft": "judul novel yang menarik",
  "theme": "tema utama cerita dalam 1 kalimat",
  "synopsis": "sinopsis 3-4 paragraf yang menarik pembaca, ditulis dengan emosional",
  "characters": [
    {
      "name": "nama tokoh",
      "role": "Protagonis/Antagonis/Pelakor/dll",
      "description": "deskripsi karakter 2-3 kalimat"
    }
  ],
  "general_overview": "overview alur cerita keseluruhan 2-3 paragraf",
  "plot_points": [
    {
      "chapter": nomor_bab,
      "event": "deskripsi plot twist atau momen penting"
    }
  ]
}

Pastikan: judul menarik dan relevan genre, karakter memiliki arc yang jelas, plot_points mencakup minimal 5 momen kunci termasuk twist dan HEA.
EOT;

        return ['system' => $system, 'user' => $user];
    }

    /**
     * @return array{system: string, user: string}
     */
    public function buildAllOutlinesPrompt(NovelStory $story, ?NovelWritingGuideline $guideline): array
    {
        $system = $this->buildSystemPrompt($guideline);

        $totalChapters = $story->total_chapters_planned;
        $targetWords = $guideline?->target_chapter_word_count ?? 1500;

        $characters = '';
        if ($story->characters) {
            $characters = collect($story->characters)
                ->map(fn ($c) => "- {$c['name']} ({$c['role']}): {$c['description']}")
                ->join("\n");
        }

        $plotPoints = '';
        if ($story->plot_points) {
            $plotPoints = collect($story->plot_points)
                ->map(fn ($p) => "- Bab {$p['chapter']}: {$p['event']}")
                ->join("\n");
        }

        $user = <<<EOT
Berdasarkan gambaran umum cerita berikut, buatkan outline untuk semua {$totalChapters} bab.

JUDUL: {$story->title_draft}
TEMA: {$story->theme}
SINOPSIS: {$story->synopsis}

TOKOH-TOKOH:
{$characters}

PLOT POINTS KUNCI:
{$plotPoints}

OVERVIEW UMUM:
{$story->general_overview}

Kembalikan HANYA JSON valid dengan struktur berikut:
{
  "chapters": [
    {
      "chapter_number": 1,
      "title": "judul bab (opsional, singkat)",
      "outline": "outline bab: apa yang terjadi, konflik, emosi, bagaimana chapter berakhir dengan cliffhanger. Target {$targetWords} kata saat ditulis penuh. 3-5 kalimat outline."
    }
  ]
}

Pastikan setiap bab memiliki cliffhanger di akhir. Plot points kunci harus tercermin di bab yang tepat.
EOT;

        return ['system' => $system, 'user' => $user];
    }

    /**
     * @return array{system: string, user: string}
     */
    public function buildSingleOutlinePrompt(NovelChapter $chapter, ?NovelWritingGuideline $guideline): array
    {
        $story = $chapter->story;
        $system = $this->buildSystemPrompt($guideline);

        $targetWords = $guideline?->target_chapter_word_count ?? 1500;

        $characters = '';
        if ($story->characters) {
            $characters = collect($story->characters)
                ->map(fn ($c) => "- {$c['name']} ({$c['role']}): {$c['description']}")
                ->join("\n");
        }

        $prevChapterContext = '';
        if ($chapter->chapter_number > 1) {
            $prevChapter = $story->chapters()->where('chapter_number', $chapter->chapter_number - 1)->first();
            if ($prevChapter?->outline_content) {
                $prevChapterContext = "\n\nOUTLINE BAB SEBELUMNYA (Bab ".($chapter->chapter_number - 1)."):\n{$prevChapter->outline_content}";
            }
        }

        $notes = $chapter->outline_prompt_notes ? "\nCatatan editor: {$chapter->outline_prompt_notes}" : '';

        $user = <<<EOT
Buatkan outline untuk Bab {$chapter->chapter_number} dari novel "{$story->title_draft}".

TOKOH:
{$characters}{$prevChapterContext}{$notes}

Kembalikan HANYA JSON valid:
{
  "title": "judul bab singkat (atau null)",
  "outline": "outline bab: apa yang terjadi, konflik, emosi, bagaimana chapter berakhir cliffhanger. Target {$targetWords} kata saat ditulis penuh. 3-5 kalimat."
}
EOT;

        return ['system' => $system, 'user' => $user];
    }

    /**
     * @return array{system: string, user: string}
     */
    public function buildChapterContentPrompt(NovelChapter $chapter, ?NovelWritingGuideline $guideline): array
    {
        $story = $chapter->story;
        $system = $this->buildSystemPrompt($guideline);

        $targetWords = $guideline?->target_chapter_word_count ?? 1500;
        $pov = $guideline?->narrative_pov === 'first_person' ? 'orang pertama (AKU)' : 'orang ketiga';
        $num = $chapter->chapter_number;
        $total = $story->total_chapters_planned;

        // --- A. Characters ---
        $characters = '';
        if ($story->characters) {
            $characters = collect($story->characters)
                ->map(fn ($c) => "- {$c['name']} ({$c['role']}): {$c['description']}")
                ->join("\n");
        }

        // --- B. Story arc position ---
        $babak = $num <= (int) ($total * 0.33)
            ? 'Babak 1 — perkenalan & konflik awal, nada membangun ketegangan'
            : ($num <= (int) ($total * 0.66)
                ? 'Babak 2 — puncak konflik & perlawanan, intensitas tinggi'
                : 'Babak 3 — resolusi & HEA, nada menuju kebahagiaan');

        // --- C. Previously-on: outlines of up to 5 previous chapters ---
        $previouslyOn = '';
        if ($num > 1) {
            $prevOutlines = $story->chapters()
                ->where('chapter_number', '<', $num)
                ->whereNotNull('outline_content')
                ->orderByDesc('chapter_number')
                ->limit(5)
                ->get()
                ->sortBy('chapter_number');

            if ($prevOutlines->isNotEmpty()) {
                $lines = $prevOutlines->map(
                    fn ($c) => "- Bab {$c->chapter_number}".($c->title ? " ({$c->title})" : '').": {$c->outline_content}"
                )->join("\n");
                $previouslyOn = "\n\nRINGKASAN 5 BAB TERAKHIR (untuk menjaga kontinuitas alur):\n{$lines}";
            }
        }

        // --- D. Continuity anchor: last 3 paragraphs of previous chapter (any status with draft) ---
        $continuityAnchor = '';
        if ($num > 1) {
            $prevChapter = $story->chapters()
                ->where('chapter_number', $num - 1)
                ->whereNotNull('content_draft')
                ->first();

            if ($prevChapter?->content_draft) {
                $paragraphs = array_values(array_filter(explode("\n\n", $prevChapter->content_draft)));
                $lastParagraphs = array_slice($paragraphs, -3);
                if ($lastParagraphs) {
                    $anchor = implode("\n\n", $lastParagraphs);
                    $continuityAnchor = "\n\nAKHIR BAB SEBELUMNYA — sambungkan alur dari sini:\n---\n{$anchor}\n---";
                }
            }
        }

        // --- E. Relevant plot point for this chapter ---
        $plotPointNote = '';
        if ($story->plot_points) {
            $match = collect($story->plot_points)->firstWhere('chapter', $num);
            if ($match) {
                $plotPointNote = "\n\nPLOT POINT WAJIB BAB INI: {$match['event']}\n(Pastikan momen ini terjadi di bab ini sesuai rencana cerita.)";
            }
        }

        // --- F. Revision note & extra notes ---
        $revisionNote = $chapter->content_revision_note
            ? "\n\nCATATAN REVISI DARI EDITOR:\n{$chapter->content_revision_note}"
            : '';

        $notes = $chapter->content_prompt_notes
            ? "\nCatatan tambahan: {$chapter->content_prompt_notes}"
            : '';

        $user = <<<EOT
Tulis konten penuh untuk Bab {$num}: "{$chapter->title}" dari novel "{$story->title_draft}".

POSISI DALAM CERITA: Bab {$num} dari {$total} total bab — {$babak}

OUTLINE BAB INI:
{$chapter->outline_content}

TOKOH:
{$characters}{$previouslyOn}{$continuityAnchor}{$plotPointNote}{$revisionNote}{$notes}

INSTRUKSI:
- Sudut pandang {$pov}
- Target {$targetWords} kata
- Bahasa Indonesia sehari-hari, emosional, natural
- Kalimat pendek maks 20 kata
- Jaga konsistensi nama tokoh, fakta, dan peristiwa dari bab-bab sebelumnya
- Akhiri dengan cliffhanger atau pertanyaan menggantung
- Tulis prosa penuh, BUKAN outline

Tulis langsung konten babnya tanpa judul, tanpa penomoran, langsung mulai dari kalimat pertama.
EOT;

        return ['system' => $system, 'user' => $user];
    }
}
