<?php

namespace App\Models\Novelya;

use Illuminate\Database\Eloquent\Model;

class NovelyaContentCategory extends Model
{
    protected $connection = 'novel';

    protected $table = 'master_content_category';

    protected $keyType = 'string';

    public $incrementing = false;
}
