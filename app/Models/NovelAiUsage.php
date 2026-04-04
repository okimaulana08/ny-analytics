<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NovelAiUsage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'novel_story_id',
        'novel_chapter_id',
        'stage',
        'model_used',
        'input_tokens',
        'output_tokens',
        'estimated_cost_usd',
        'was_successful',
        'error_message',
        'triggered_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'was_successful' => 'boolean',
            'estimated_cost_usd' => 'decimal:6',
            'created_at' => 'datetime',
        ];
    }

    public function story(): BelongsTo
    {
        return $this->belongsTo(NovelStory::class, 'novel_story_id');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(NovelChapter::class, 'novel_chapter_id');
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'triggered_by');
    }

    public static function record(
        int $storyId,
        ?int $chapterId,
        string $stage,
        string $model,
        int $inputTokens,
        int $outputTokens,
        int $triggeredBy,
        bool $wasSuccessful = true,
        ?string $errorMessage = null,
    ): self {
        // Pricing: claude-sonnet-4-6 = $3/$15 per 1M tokens
        $costUsd = ($inputTokens * 3 + $outputTokens * 15) / 1_000_000;

        return self::create([
            'novel_story_id' => $storyId,
            'novel_chapter_id' => $chapterId,
            'stage' => $stage,
            'model_used' => $model,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'estimated_cost_usd' => $costUsd,
            'was_successful' => $wasSuccessful,
            'error_message' => $errorMessage,
            'triggered_by' => $triggeredBy,
            'created_at' => now(),
        ]);
    }
}
