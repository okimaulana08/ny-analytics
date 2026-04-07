<?php

namespace App\Services;

use App\Models\AppConfig;
use App\Models\NovelChapter;
use App\Models\NovelStory;
use App\Models\NovelWritingGuideline;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NovelGeneratorService
{
    public function __construct(private readonly NovelPromptBuilder $promptBuilder) {}

    private function isMockMode(): bool
    {
        return (bool) AppConfig::get('novel.mock_mode', false);
    }

    /**
     * @return array{content: string, input_tokens: int, output_tokens: int, model: string}
     */
    public function generateOverview(NovelStory $story): array
    {
        if ($this->isMockMode()) {
            return $this->mockOverview($story);
        }

        $guideline = $story->guideline ?? NovelWritingGuideline::activeForGenre($story->genre);
        $model = AppConfig::get('novel.overview_model', 'claude-sonnet-4-6');

        ['system' => $system, 'user' => $user] = $this->promptBuilder->buildOverviewPrompt($story, $guideline);

        return $this->callClaude($model, $user, $system, 8192);
    }

    /**
     * @return array{content: string, input_tokens: int, output_tokens: int, model: string}
     */
    public function generateAllOutlines(NovelStory $story): array
    {
        if ($this->isMockMode()) {
            return $this->mockAllOutlines($story);
        }

        $guideline = $story->guideline ?? NovelWritingGuideline::activeForGenre($story->genre);
        $model = AppConfig::get('novel.outline_model', 'claude-sonnet-4-6');

        ['system' => $system, 'user' => $user] = $this->promptBuilder->buildAllOutlinesPrompt($story, $guideline);

        return $this->callClaude($model, $user, $system, 16384);
    }

    /**
     * @return array{content: string, input_tokens: int, output_tokens: int, model: string}
     */
    public function generateSingleOutline(NovelChapter $chapter): array
    {
        if ($this->isMockMode()) {
            return $this->mockSingleOutline($chapter);
        }

        $story = $chapter->story;
        $guideline = $story->guideline ?? NovelWritingGuideline::activeForGenre($story->genre);
        $model = AppConfig::get('novel.outline_model', 'claude-sonnet-4-6');

        ['system' => $system, 'user' => $user] = $this->promptBuilder->buildSingleOutlinePrompt($chapter, $guideline);

        return $this->callClaude($model, $user, $system, 1024);
    }

    /**
     * @return array{content: string, input_tokens: int, output_tokens: int, model: string}
     */
    public function generateChapterContent(NovelChapter $chapter): array
    {
        if ($this->isMockMode()) {
            return $this->mockChapterContent($chapter);
        }

        $story = $chapter->story;
        $guideline = $story->guideline ?? NovelWritingGuideline::activeForGenre($story->genre);
        $model = AppConfig::get('novel.content_model', 'claude-sonnet-4-6');
        $maxTokens = (int) AppConfig::get('novel.content_max_tokens', 4096);

        ['system' => $system, 'user' => $user] = $this->promptBuilder->buildChapterContentPrompt($chapter, $guideline);

        return $this->callClaude($model, $user, $system, $maxTokens);
    }

    // -------------------------------------------------------------------------
    // Mock responses — zero token cost, validates pipeline & UI flow only
    // -------------------------------------------------------------------------

    /** @return array{content: string, input_tokens: int, output_tokens: int, model: string} */
    private function mockOverview(NovelStory $story): array
    {
        $n = $story->total_chapters_planned;
        $twist1 = max(1, (int) round($n * 0.11));
        $twist2 = max($twist1 + 1, (int) round($n * 0.34));
        $twist3 = max($twist2 + 1, (int) round($n * 0.59));
        $hea = $n;

        $content = json_encode([
            'title_draft' => '[MOCK] Luka di Balik Senyuman',
            'theme' => 'Pengkhianatan cinta dan kebangkitan diri seorang wanita',
            'synopsis' => '[MOCK] Rara, 32 tahun, mengira pernikahannya sempurna. Hingga suatu malam ia pulang lebih awal dan menemukan kenyataan yang menghancurkan hidupnya. Perlahan ia harus memilih: tenggelam dalam luka atau bangkit menjadi perempuan yang lebih kuat.',
            'characters' => [
                ['name' => 'Rara', 'role' => 'Protagonis', 'description' => '[MOCK] Wanita 32 tahun, ibu satu anak, terlihat kuat tapi rapuh di dalam.'],
                ['name' => 'Arya', 'role' => 'Antagonis Suami', 'description' => '[MOCK] Suami Rara, karier bagus, menyimpan rahasia besar.'],
                ['name' => 'Vanessa', 'role' => 'Pelakor', 'description' => '[MOCK] Rekan kerja Arya, manipulatif dan licik.'],
            ],
            'general_overview' => "[MOCK] Cerita ini mengikuti perjalanan Rara dari keterpurukan hingga kebangkitan dalam {$n} bab.",
            'plot_points' => [
                ['chapter' => $twist1, 'event' => '[MOCK] Rara menemukan bukti pertama pengkhianatan Arya.'],
                ['chapter' => $twist2, 'event' => '[MOCK] Twist besar — Vanessa mengklaim hamil dari Arya.'],
                ['chapter' => $twist3, 'event' => '[MOCK] Rara mengambil keputusan final dan mulai bangkit.'],
                ['chapter' => $hea, 'event' => '[MOCK] HEA — Rara bahagia, Arya mendapat karma.'],
            ],
        ], JSON_UNESCAPED_UNICODE);

        return ['content' => $content, 'input_tokens' => 0, 'output_tokens' => 0, 'model' => 'mock'];
    }

    /** @return array{content: string, input_tokens: int, output_tokens: int, model: string} */
    private function mockAllOutlines(NovelStory $story): array
    {
        $chapters = [];
        for ($i = 1; $i <= $story->total_chapters_planned; $i++) {
            $chapters[] = [
                'chapter_number' => $i,
                'title' => "[MOCK] Judul Bab {$i}",
                'outline' => "[MOCK] Outline bab {$i}: Rara menghadapi situasi baru yang menguji kekuatannya. Konflik memuncak di pertengahan bab. Bab berakhir dengan pertanyaan menggantung yang membuat pembaca penasaran.",
            ];
        }

        $content = json_encode(['chapters' => $chapters], JSON_UNESCAPED_UNICODE);

        return ['content' => $content, 'input_tokens' => 0, 'output_tokens' => 0, 'model' => 'mock'];
    }

    /** @return array{content: string, input_tokens: int, output_tokens: int, model: string} */
    private function mockSingleOutline(NovelChapter $chapter): array
    {
        $n = $chapter->chapter_number;
        $content = json_encode([
            'title' => "[MOCK] Judul Bab {$n}",
            'outline' => "[MOCK] Outline bab {$n}: Rara menghadapi konflik baru yang menguji batas kesabarannya. Ada percakapan penting dengan tokoh pendukung. Bab diakhiri dengan cliffhanger yang membuat pembaca ingin terus membaca.",
        ], JSON_UNESCAPED_UNICODE);

        return ['content' => $content, 'input_tokens' => 0, 'output_tokens' => 0, 'model' => 'mock'];
    }

    /** @return array{content: string, input_tokens: int, output_tokens: int, model: string} */
    private function mockChapterContent(NovelChapter $chapter): array
    {
        $n = $chapter->chapter_number;
        $title = $chapter->title ?? "Bab {$n}";
        $content = "[MOCK — Bab {$n}: {$title}]\n\n"
            ."Aku tidak pernah menyangka hari ini akan menjadi titik balik hidupku.\n\n"
            ."Pagi itu terasa biasa saja. Matahari masih malu-malu menyembul dari balik awan ketika aku menyiapkan sarapan untuk keluarga kecilku. Tapi ada yang berbeda — sesuatu yang belum bisa kunamakan, seperti firasat yang menggelayut di dada sejak subuh.\n\n"
            ."\"Rara, aku pergi dulu,\" suara Arya terdengar dari ruang tamu. Dingin. Singkat. Seperti biasanya belakangan ini.\n\n"
            ."Aku tidak menjawab. Tidak ada gunanya.\n\n"
            ."[MOCK] Konten bab {$n} dari story #{$chapter->novel_story_id}. Total placeholder untuk keperluan testing pipeline dan UI. Konten ini tidak menggunakan Claude API sama sekali.\n\n"
            .'Dan saat itulah aku melihatnya — sesuatu yang mengubah segalanya.';

        return ['content' => $content, 'input_tokens' => 0, 'output_tokens' => 0, 'model' => 'mock'];
    }

    /**
     * @return array{content: string, input_tokens: int, output_tokens: int, model: string}
     */
    private function callClaude(string $model, string $userMessage, string $systemPrompt, int $maxTokens): array
    {
        $apiKey = config('services.anthropic.key');

        $response = Http::timeout(300)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $model,
                'max_tokens' => $maxTokens,
                'system' => $systemPrompt,
                'messages' => [
                    ['role' => 'user', 'content' => $userMessage],
                ],
            ]);

        if (! $response->successful()) {
            Log::error('NovelGeneratorService: Claude API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('Claude API error: '.$response->status().' — '.$response->body());
        }

        $json = $response->json();

        return [
            'content' => $json['content'][0]['text'] ?? '',
            'input_tokens' => $json['usage']['input_tokens'] ?? 0,
            'output_tokens' => $json['usage']['output_tokens'] ?? 0,
            'model' => $model,
        ];
    }
}
