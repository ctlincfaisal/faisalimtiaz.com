<?php

namespace App\Models;

use App\Support\MarketingAccountScoped;
use Illuminate\Database\Eloquent\Model;

class MarketingCampaignStep extends Model
{
    use MarketingAccountScoped;

    protected $fillable = [
        'marketing_account_id',
        'marketing_campaign_id',
        'step_order',
        'step_type',
        'wait_days',
        'subject',
        'body',
        'content_format',
        'marketing_template_id',
        'marketing_mail_service_id',
        'marketing_signature_id',
        'attachment_path',
        'attachment_name',
    ];

    protected $casts = [
        'wait_days' => 'integer',
        'step_order' => 'integer',
    ];

    public function campaign()
    {
        return $this->belongsTo(MarketingCampaign::class, 'marketing_campaign_id');
    }

    public function template()
    {
        return $this->belongsTo(MarketingTemplate::class, 'marketing_template_id');
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
