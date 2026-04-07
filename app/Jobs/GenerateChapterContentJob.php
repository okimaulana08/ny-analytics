<?php

namespace App\Jobs;

use App\Models\NovelAiUsage;
use App\Models\NovelChapter;
use App\Services\NovelGeneratorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateChapterContentJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public bool $failOnTimeout = true;

    public int $tries = 1;

    public function __construct(
        public readonly int $chapterId,
        public readonly int $triggeredBy,
    ) {}

    public function handle(NovelGeneratorService $generator): void
    {
        $chapter = NovelChapter::with('story')->findOrFail($this->chapterId);
        $story = $chapter->story;

        $chapter->update([
            'content_status' => 'generating',
            'content_generation_count' => $chapter->content_generation_count + 1,
        ]);

        $result = $generator->generateChapterContent($chapter);

        $chapter->update([
            'content_draft' => $result['content'],
            'content_status' => 'approved',
            'approved_content_at' => now(),
            'content_input_tokens' => $result['input_tokens'],
            'content_output_tokens' => $result['output_tokens'],
        ]);

        // Update story token totals and status
        $story->refresh();
        $allApproved = $story->chapters()->where('content_status', '!=', 'approved')->doesntExist();
        $story->update([
            'status' => $allApproved ? 'content_complete' : 'content_in_progress',
            'total_input_tokens' => $story->total_input_tokens + $result['input_tokens'],
            'total_output_tokens' => $story->total_output_tokens + $result['output_tokens'],
        ]);

        NovelAiUsage::record(
            storyId: $story->id,
            chapterId: $this->chapterId,
            stage: 'content',
            model: $result['model'] ?? 'claude-sonnet-4-6',
            inputTokens: $result['input_tokens'],
            outputTokens: $result['output_tokens'],
            triggeredBy: $this->triggeredBy,
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateChapterContentJob: Chapter #{$this->chapterId} failed", [
            'message' => $exception->getMessage(),
        ]);

        $chapter = NovelChapter::find($this->chapterId);

        if ($chapter) {
            $chapter->update(['content_status' => 'failed']);

            NovelAiUsage::record(
                storyId: $chapter->novel_story_id,
                chapterId: $this->chapterId,
                stage: 'content',
                model: 'claude-sonnet-4-6',
                inputTokens: 0,
                outputTokens: 0,
                triggeredBy: $this->triggeredBy,
                wasSuccessful: false,
                errorMessage: $exception->getMessage(),
            );
        }
    }
}
