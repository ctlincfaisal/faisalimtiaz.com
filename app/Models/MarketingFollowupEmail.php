<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\MarketingAccountScoped;

class MarketingFollowupEmail extends Model
{
    use HasFactory, MarketingAccountScoped;

    protected $fillable = [
        'marketing_account_id',
        'template_name',
        'marketing_mail_service_id',
        'marketing_signature_id',
        'marketing_email_id',
        'marketing_template_id',
        'recipients',
        'recipient_count',
        'subject',
        'body',
        'content_format',
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

    public function opens()
    {
        return $this->hasMany(MarketingFollowupEmailOpen::class);
    }

    public function mailService()
    {
        return $this->belongsTo(MarketingMailService::class, 'marketing_mail_service_id');
    }

    public function signature()
    {
        return $this->belongsTo(MarketingSignature::class, 'marketing_signature_id');
    }
}
