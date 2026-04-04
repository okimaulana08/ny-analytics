<?php

namespace App\Jobs;

use App\Models\NovelAiUsage;
use App\Models\NovelChapter;
use App\Models\NovelStory;
use App\Services\NovelGeneratorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateNovelOutlinesJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 180;

    public int $tries = 2;

    public function __construct(
        public readonly int $storyId,
        public readonly int $triggeredBy,
    ) {}

    public function handle(NovelGeneratorService $generator): void
    {
        $story = NovelStory::findOrFail($this->storyId);

        $story->update(['status' => 'outline_pending']);

        // Ensure chapter rows exist
        for ($i = 1; $i <= $story->total_chapters_planned; $i++) {
            NovelChapter::firstOrCreate(
                ['novel_story_id' => $story->id, 'chapter_number' => $i],
                ['outline_status' => 'pending', 'content_status' => 'pending']
            );
        }

        $result = $generator->generateAllOutlines($story);

        $raw = $result['content'];

        $jsonStr = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $jsonStr = preg_replace('/```\s*$/m', '', $jsonStr);
        $jsonStr = trim($jsonStr);

        $data = json_decode($jsonStr, true);

        // If truncated (common when max_tokens is hit), try to close the JSON and re-parse
        if ((! $data || empty($data['chapters'])) && json_last_error() !== JSON_ERROR_NONE) {
            $attempt = $jsonStr;
            // Cut back to the last complete object — drops any incomplete trailing entry
            $lastBrace = strrpos($attempt, '}');
            if ($lastBrace !== false) {
                $attempt = substr($attempt, 0, $lastBrace + 1);
            }
            $attempt = rtrim($attempt, " \t\n\r,");
            $open = substr_count($attempt, '{') - substr_count($attempt, '}');
            $openArr = substr_count($attempt, '[') - substr_count($attempt, ']');
            $attempt .= str_repeat(']', max(0, $openArr));
            $attempt .= str_repeat('}', max(0, $open));
            $data = json_decode($attempt, true);
        }

        if (! $data || empty($data['chapters'])) {
            Log::error("GenerateNovelOutlinesJob: Failed to parse JSON for story #{$this->storyId}", ['raw' => $raw]);
            throw new \RuntimeException('Failed to parse outlines JSON from AI response');
        }

        foreach ($data['chapters'] as $chapterData) {
            NovelChapter::where('novel_story_id', $story->id)
                ->where('chapter_number', $chapterData['chapter_number'])
                ->update([
                    'title' => $chapterData['title'] ?? null,
                    'outline_content' => $chapterData['outline'] ?? null,
                    'outline_status' => 'ready',
                    'outline_input_tokens' => (int) round($result['input_tokens'] / $story->total_chapters_planned),
                    'outline_output_tokens' => (int) round($result['output_tokens'] / $story->total_chapters_planned),
                ]);
        }

        $story->update([
            'status' => 'outline_ready',
            'total_input_tokens' => $story->total_input_tokens + $result['input_tokens'],
            'total_output_tokens' => $story->total_output_tokens + $result['output_tokens'],
        ]);

        NovelAiUsage::record(
            storyId: $this->storyId,
            chapterId: null,
            stage: 'outline',
            model: $result['model'] ?? 'claude-sonnet-4-6',
            inputTokens: $result['input_tokens'],
            outputTokens: $result['output_tokens'],
            triggeredBy: $this->triggeredBy,
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateNovelOutlinesJob: Story #{$this->storyId} failed", [
            'message' => $exception->getMessage(),
        ]);

        NovelStory::where('id', $this->storyId)->update(['status' => 'overview_approved']);

        NovelAiUsage::record(
            storyId: $this->storyId,
            chapterId: null,
            stage: 'outline',
            model: 'claude-sonnet-4-6',
            inputTokens: 0,
            outputTokens: 0,
            triggeredBy: $this->triggeredBy,
            wasSuccessful: false,
            errorMessage: $exception->getMessage(),
        );
    }
}
