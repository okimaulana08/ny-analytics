<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NovelStory extends Model
{
    protected $fillable = [
        'title',
        'genre',
        'novel_writing_guideline_id',
        'status',
        'overview_prompt_notes',
        'overview_ai_raw',
        'title_draft',
        'theme',
        'synopsis',
        'characters',
        'general_overview',
        'plot_points',
        'total_chapters_planned',
        'approved_overview_at',
        'approved_overview_by',
        'approved_outline_at',
        'approved_outline_by',
        'total_input_tokens',
        'total_output_tokens',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'characters' => 'array',
            'plot_points' => 'array',
            'approved_overview_at' => 'datetime',
            'approved_outline_at' => 'datetime',
        ];
    }

    public function guideline(): BelongsTo
    {
        return $this->belongsTo(NovelWritingGuideline::class, 'novel_writing_guideline_id');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(NovelChapter::class)->orderBy('chapter_number');
    }

    public function aiUsages(): HasMany
    {
        return $this->hasMany(NovelAiUsage::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function overviewApprover(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'approved_overview_by');
    }

    public function outlineApprover(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'approved_outline_by');
    }

    public function canGenerateOverview(): bool
    {
        return $this->status === 'draft';
    }

    public function canApproveOverview(): bool
    {
        return $this->status === 'overview_ready';
    }

    public function canGenerateOutlines(): bool
    {
        return $this->status === 'overview_approved';
    }

    public function canApproveOutlines(): bool
    {
        return $this->status === 'outline_ready';
    }

    public function isContentPhase(): bool
    {
        return in_array($this->status, ['outline_approved', 'content_in_progress', 'content_complete', 'published']);
    }

    public function allChaptersContentApproved(): bool
    {
        return $this->chapters()->where('content_status', '!=', 'approved')->doesntExist();
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'draft' => 'bg-gray-500/20 text-gray-300',
            'overview_pending', 'outline_pending' => 'bg-blue-500/20 text-blue-300',
            'overview_ready', 'outline_ready' => 'bg-amber-500/20 text-amber-300',
            'overview_approved', 'outline_approved' => 'bg-green-500/20 text-green-300',
            'content_in_progress' => 'bg-purple-500/20 text-purple-300',
            'content_complete', 'published' => 'bg-emerald-500/20 text-emerald-300',
            default => 'bg-gray-500/20 text-gray-300',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'overview_pending' => 'Membuat Ringkasan...',
            'overview_ready' => 'Ringkasan Siap',
            'overview_approved' => 'Ringkasan Disetujui',
            'outline_pending' => 'Membuat Outline...',
            'outline_ready' => 'Outline Siap',
            'outline_approved' => 'Outline Disetujui',
            'content_in_progress' => 'Konten Diproses',
            'content_complete' => 'Konten Selesai',
            'published' => 'Dipublikasikan',
            default => $this->status,
        };
    }

    public function genreLabel(): string
    {
        return match ($this->genre) {
            'drama_rumah_tangga' => 'Drama Rumah Tangga',
            'drama_perselingkuhan' => 'Drama Perselingkuhan',
            'drama_poligami' => 'Drama Poligami',
            'drama_kdrt' => 'Drama KDRT',
            'drama_pernikahan_kontrak' => 'Pernikahan Kontrak',
            default => $this->genre,
        };
    }
}
