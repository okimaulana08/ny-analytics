<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NovelWritingGuideline extends Model
{
    protected $fillable = [
        'name',
        'genre',
        'is_active',
        'language_style',
        'narrative_pov',
        'content_guidelines',
        'character_archetypes',
        'plot_structure_notes',
        'forbidden_content',
        'target_chapter_word_count',
        'system_prompt_prefix',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'character_archetypes' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function stories(): HasMany
    {
        return $this->hasMany(NovelStory::class);
    }

    public static function activeForGenre(string $genre): ?self
    {
        return self::where('genre', $genre)->where('is_active', true)->first();
    }
}
