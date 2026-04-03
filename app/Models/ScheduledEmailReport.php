<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledEmailReport extends Model
{
    protected $connection = 'sqlite';

    protected $table = 'scheduled_email_reports';

    protected $fillable = [
        'name',
        'description',
        'report_type',
        'frequency',
        'day_of_week',
        'day_of_month',
        'recipients',
        'is_active',
        'last_sent_at',
        'next_run_at',
        'created_by',
    ];

    const TYPE_REVENUE_SUMMARY = 'revenue_summary';

    const TYPE_TOP_CONTENT = 'top_content';

    const TYPE_CHURN_ALERT = 'churn_alert';

    const TYPE_ENGAGEMENT_SUMMARY = 'engagement_summary';

    const FREQ_WEEKLY = 'weekly';

    const FREQ_MONTHLY = 'monthly';

    protected function casts(): array
    {
        return [
            'recipients' => 'array',
            'is_active' => 'boolean',
            'last_sent_at' => 'datetime',
            'next_run_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function typeLabel(): string
    {
        return match ($this->report_type) {
            self::TYPE_REVENUE_SUMMARY => 'Revenue Summary',
            self::TYPE_TOP_CONTENT => 'Top Content',
            self::TYPE_CHURN_ALERT => 'Churn Alert',
            self::TYPE_ENGAGEMENT_SUMMARY => 'Engagement Summary',
            default => $this->report_type,
        };
    }

    public function frequencyLabel(): string
    {
        if ($this->frequency === self::FREQ_WEEKLY) {
            $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $day = $days[$this->day_of_week ?? 1] ?? 'Senin';

            return "Mingguan ({$day})";
        }

        return "Bulanan (tgl {$this->day_of_month})";
    }

    public function isDueToday(): bool
    {
        $today = Carbon::now('Asia/Jakarta');

        if ($this->frequency === self::FREQ_WEEKLY) {
            return $today->dayOfWeek === (int) $this->day_of_week;
        }

        return $today->day === (int) $this->day_of_month;
    }

    public function computeNextRun(): Carbon
    {
        $today = Carbon::now('Asia/Jakarta')->startOfDay();

        if ($this->frequency === self::FREQ_WEEKLY) {
            $next = $today->copy()->addDays(1);
            while ($next->dayOfWeek !== (int) $this->day_of_week) {
                $next->addDay();
            }

            return $next;
        }

        $dom = (int) $this->day_of_month;
        $next = $today->copy()->addMonthNoOverflow()->startOfMonth()->addDays($dom - 1);

        return $next;
    }
}
