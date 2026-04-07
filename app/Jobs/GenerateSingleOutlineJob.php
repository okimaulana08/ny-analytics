<?php

namespace App\Jobs;

use App\Models\NovelAiUsage;
use App\Models\NovelChapter;
use App\Services\NovelGeneratorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateSingleOutlineJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 180;

    public bool $failOnTimeout = true;

    public int $tries = 2;

    public int $backoff = 70;

    public function __construct(
        public readonly int $chapterId,
        public readonly int $triggeredBy,
    ) {}

    public function handle(NovelGeneratorService $generator): void
    {
        $chapter = NovelChapter::with('story')->findOrFail($this->chapterId);
        $story = $chapter->story;

        $chapter->update(['outline_status' => 'generating']);

        $result = $generator->generateSingleOutline($chapter);

        $raw = $result['content'];
        $jsonStr = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $jsonStr = preg_replace('/```\s*$/m', '', $jsonStr);
        $data = json_decode(trim($jsonStr), true);

        if (! $data) {
            throw new \RuntimeException('Failed to parse single outline JSON from AI response');
        }

        $chapter->update([
            'title' => $data['title'] ?? $chapter->title,
            'outline_content' => $data['outline'] ?? null,
            'outline_status' => 'approved',
            'outline_input_tokens' => $result['input_tokens'],
            'outline_output_tokens' => $result['output_tokens'],
        ]);

        $story->update([
            'total_input_tokens' => $story->total_input_tokens + $result['input_tokens'],
            'total_output_tokens' => $story->total_output_tokens + $result['output_tokens'],
        ]);

        NovelAiUsage::record(
            storyId: $story->id,
            chapterId: $this->chapterId,
            stage: 'outline',
            model: $result['model'],
            inputTokens: $result['input_tokens'],
            outputTokens: $result['output_tokens'],
            triggeredBy: $this->triggeredBy,
        );

        // If all chapters have a completed or failed outline, mark story as outline_approved
        $allDone = $story->chapters()
            ->whereNotIn('outline_status', ['approved', 'failed'])
            ->doesntExist();

        if ($allDone) {
            $story->update(['status' => 'outline_approved']);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateSingleOutlineJob: Chapter #{$this->chapterId} failed", [
            'message' => $exception->getMessage(),
        ]);

        NovelChapter::where('id', $this->chapterId)->update(['outline_status' => 'failed']);
    }
}
