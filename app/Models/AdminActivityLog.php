<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminActivityLog extends Model
{
    protected $connection = 'sqlite';

    protected $table = 'admin_activity_logs';

    public $timestamps = false;

    protected $fillable = [
        'admin_user_id',
        'admin_name',
        'admin_email',
        'action',
        'feature',
        'url',
        'http_method',
        'ip_address',
        'user_agent',
        'payload',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    public function actionBadgeClass(): string
    {
        return match ($this->action) {
            'Login' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
            'Create' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
            'Update' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
            'Delete' => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300',
            'Send' => 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
            'Toggle' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
            'Export' => 'bg-slate-100 text-slate-700 dark:bg-slate-500/15 dark:text-slate-300',
            'Generate' => 'bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-500/15 dark:text-fuchsia-300',
            default => 'bg-slate-100 text-slate-600 dark:bg-slate-500/15 dark:text-slate-400',
        };
    }
}
