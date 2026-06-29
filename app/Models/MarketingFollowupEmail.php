<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingFollowupEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'marketing_email_id',
        'marketing_template_id',
        'recipients',
        'recipient_count',
        'subject',
        'body',
        'attachment_path',
        'attachment_name',
        'status',
        'scheduled_at',
        'sent_at',
        'delivery_error',
    ];

    protected $casts = [
        'recipients' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function originalEmail()
    {
        return $this->belongsTo(MarketingEmail::class, 'marketing_email_id');
    }

    public function template()
    {
        return $this->belongsTo(MarketingTemplate::class, 'marketing_template_id');
    }
}
