<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NovelContent extends Model
{
    protected $connection  = 'novel';
    protected $table       = 'content';
    protected $keyType     = 'string';
    public    $incrementing = false;
    protected $guarded     = [];

    protected $casts = [
        'is_draft'      => 'boolean',
        'is_published'  => 'boolean',
        'is_completed'  => 'boolean',
        'is_deleted'    => 'boolean',
        'published_at'  => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];
}
