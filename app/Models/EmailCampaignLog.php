<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCampaignLog extends Model
{
    protected $table = 'email_campaign_logs';

    protected $fillable = [
        'email_campaign_id',
        'recipient_email',
        'recipient_name',
        'status',
        'brevo_message_id',
        'error_message',
        'opened_at',
        'clicked_at',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'email_campaign_id');
    }
}
