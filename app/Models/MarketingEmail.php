<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\MarketingAccountScoped;

class MarketingEmail extends Model
{
    use HasFactory, MarketingAccountScoped;

    protected $fillable = [
        'marketing_account_id',
        'marketing_campaign_id',
        'marketing_campaign_step_id',
        'template_name',
        'marketing_mail_service_id',
        'marketing_signature_id',
        'recipients',
        'recipient_count',
        'sent_count',
        'failed_count',
        'subject',
        'body',
        'content_format',
        'attachment_path',
        'attachment_name',
        'delivery_status',
        'delivery_error',
        'sent_at',
    ];

    protected $casts = [
        'recipients' => 'array',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
        'sent_at' => 'datetime',
    ];

    public function opens()
    {
        return $this->hasMany(MarketingEmailOpen::class);
    }

    public function mailService()
    {
        return $this->belongsTo(MarketingMailService::class, 'marketing_mail_service_id');
    }

    public function signature()
    {
        return $this->belongsTo(MarketingSignature::class, 'marketing_signature_id');
    }

    public function followups()
    {
        return $this->hasMany(MarketingFollowupEmail::class);
    }

    public function campaign()
    {
        return $this->belongsTo(MarketingCampaign::class, 'marketing_campaign_id');
    }

    public function campaignStep()
    {
        return $this->belongsTo(MarketingCampaignStep::class, 'marketing_campaign_step_id');
    }
}
