<?php

namespace App\Models\Novelya;

use Illuminate\Database\Eloquent\Model;

class NovelyaUser extends Model
{
    protected $connection = 'novel';

    protected $table = 'users';

    protected $keyType = 'string';

    public $incrementing = false;
}
