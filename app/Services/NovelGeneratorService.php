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

    /**
     * @return array{content: string, input_tokens: int, output_tokens: int, model: string}
     */
    public function generateOverview(NovelStory $story): array
    {
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
        $story = $chapter->story;
        $guideline = $story->guideline ?? NovelWritingGuideline::activeForGenre($story->genre);
        $model = AppConfig::get('novel.content_model', 'claude-sonnet-4-6');
        $maxTokens = (int) AppConfig::get('novel.content_max_tokens', 4096);

        ['system' => $system, 'user' => $user] = $this->promptBuilder->buildChapterContentPrompt($chapter, $guideline);

        return $this->callClaude($model, $user, $system, $maxTokens);
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
