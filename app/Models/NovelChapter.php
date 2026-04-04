<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NovelChapter extends Model
{
    protected $fillable = [
        'novel_story_id',
        'chapter_number',
        'title',
        'outline_status',
        'outline_prompt_notes',
        'outline_content',
        'approved_outline_at',
        'approved_outline_by',
        'content_status',
        'content_prompt_notes',
        'content_draft',
        'content_revision_note',
        'approved_content_at',
        'approved_content_by',
        'outline_input_tokens',
        'outline_output_tokens',
        'content_input_tokens',
        'content_output_tokens',
        'content_generation_count',
    ];

    protected function casts(): array
    {
        return [
            'approved_outline_at' => 'datetime',
            'approved_content_at' => 'datetime',
        ];
    }

    public function story(): BelongsTo
    {
        return $this->belongsTo(NovelStory::class, 'novel_story_id');
    }

    public function aiUsages(): HasMany
    {
        return $this->hasMany(NovelAiUsage::class);
    }

    public function outlineApprover(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'approved_outline_by');
    }

    public function contentApprover(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'approved_content_by');
    }

    public function canGenerateOutline(): bool
    {
        return in_array($this->outline_status, ['pending', 'failed']);
    }

    public function canApproveOutline(): bool
    {
        return $this->outline_status === 'ready';
    }

    public function canGenerateContent(): bool
    {
        return $this->outline_status === 'approved'
            && in_array($this->content_status, ['pending', 'revision_requested', 'failed']);
    }

    public function canApproveContent(): bool
    {
        return $this->content_status === 'ready';
    }

    public function chapterLabel(): string
    {
        $label = "Bab {$this->chapter_number}";
        if ($this->title) {
            $label .= ": {$this->title}";
        }

        return $label;
    }

    public function totalChapterTokens(): int
    {
        return $this->outline_input_tokens
            + $this->outline_output_tokens
            + $this->content_input_tokens
            + $this->content_output_tokens;
    }
}
