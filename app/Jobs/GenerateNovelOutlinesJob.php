<?php

namespace App\Jobs;

use App\Models\NovelChapter;
use App\Models\NovelStory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateNovelOutlinesJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(
        public readonly int $storyId,
        public readonly int $triggeredBy,
    ) {}

    public function handle(): void
    {
        $story = NovelStory::findOrFail($this->storyId);

        $story->update(['status' => 'outline_pending']);

        // Ensure chapter rows exist, then dispatch one job per chapter.
        // Stagger by 15s each to stay under the 30,000 input tokens/min rate limit
        // (~6,000 tokens/job → max 4 safe jobs/min → 1 job every 15s).
        for ($i = 1; $i <= $story->total_chapters_planned; $i++) {
            $chapter = NovelChapter::firstOrCreate(
                ['novel_story_id' => $story->id, 'chapter_number' => $i],
                ['outline_status' => 'pending', 'content_status' => 'pending']
            );

            GenerateSingleOutlineJob::dispatch($chapter->id, $this->triggeredBy)
                ->delay(now()->addSeconds(($i - 1) * 15));
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateNovelOutlinesJob: Story #{$this->storyId} failed", [
            'message' => $exception->getMessage(),
        ]);

        NovelStory::where('id', $this->storyId)->update(['status' => 'overview_approved']);
    }
}
