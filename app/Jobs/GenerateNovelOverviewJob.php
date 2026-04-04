<?php

namespace App\Jobs;

use App\Models\NovelAiUsage;
use App\Models\NovelStory;
use App\Services\NovelGeneratorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateNovelOverviewJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public int $tries = 2;

    public function __construct(
        public readonly int $storyId,
        public readonly int $triggeredBy,
    ) {}

    public function handle(NovelGeneratorService $generator): void
    {
        $story = NovelStory::findOrFail($this->storyId);

        $story->update(['status' => 'overview_pending']);

        $result = $generator->generateOverview($story);

        $raw = $result['content'];
        $story->update(['overview_ai_raw' => $raw]);

        // Parse JSON response
        $jsonStr = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $jsonStr = preg_replace('/```\s*$/m', '', $jsonStr);
        $data = json_decode(trim($jsonStr), true);

        if (! $data) {
            Log::error("GenerateNovelOverviewJob: Failed to parse JSON for story #{$this->storyId}", ['raw' => $raw]);
            throw new \RuntimeException('Failed to parse overview JSON from AI response');
        }

        $story->update([
            'status' => 'overview_ready',
            'title_draft' => $data['title_draft'] ?? null,
            'theme' => $data['theme'] ?? null,
            'synopsis' => $data['synopsis'] ?? null,
            'characters' => $data['characters'] ?? [],
            'general_overview' => $data['general_overview'] ?? null,
            'plot_points' => $data['plot_points'] ?? [],
            'total_input_tokens' => $story->total_input_tokens + $result['input_tokens'],
            'total_output_tokens' => $story->total_output_tokens + $result['output_tokens'],
        ]);

        NovelAiUsage::record(
            storyId: $this->storyId,
            chapterId: null,
            stage: 'overview',
            model: $result['model'] ?? 'claude-sonnet-4-6',
            inputTokens: $result['input_tokens'],
            outputTokens: $result['output_tokens'],
            triggeredBy: $this->triggeredBy,
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateNovelOverviewJob: Story #{$this->storyId} failed", [
            'message' => $exception->getMessage(),
        ]);

        NovelStory::where('id', $this->storyId)->update(['status' => 'draft']);

        NovelAiUsage::record(
            storyId: $this->storyId,
            chapterId: null,
            stage: 'overview',
            model: 'claude-sonnet-4-6',
            inputTokens: 0,
            outputTokens: 0,
            triggeredBy: $this->triggeredBy,
            wasSuccessful: false,
            errorMessage: $exception->getMessage(),
        );
    }
}
