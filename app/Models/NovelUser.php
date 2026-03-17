<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NovelUser extends Model
{
    protected $connection  = 'novel';
    protected $table       = 'users';
    protected $keyType     = 'string';
    public    $incrementing = false;
    protected $guarded     = [];
}
