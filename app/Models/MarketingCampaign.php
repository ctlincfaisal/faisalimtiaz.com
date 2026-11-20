<?php

namespace App\Models;

use App\Support\MarketingAccountScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MarketingCampaign extends Model
{
    use MarketingAccountScoped;

    protected $fillable = [
        'marketing_account_id',
        'name',
        'description',
        'status',
        'audience_type',
        'audience_segment',
        'audience_status',
        'manual_recipients',
        'recipient_count',
        'sequence_count',
        'stop_on_reply',
        'send_rate_per_minute',
        'scheduled_at',
        'started_at',
        'completed_at',
        'current_step',
        'pending_emails',
        'sent_count',
        'failed_count',
        'last_error',
    ];

    protected $casts = [
        'manual_recipients' => 'array',
        'stop_on_reply' => 'boolean',
        'send_rate_per_minute' => 'integer',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function steps()
    {
        return $this->hasMany(MarketingCampaignStep::class)->orderBy('step_order');
    }

    public function emails()
    {
        return $this->hasMany(MarketingEmail::class, 'marketing_campaign_id');
    }

    public function audienceEmails(): Collection
    {
        if ($this->audience_type === 'manual') {
            $emails = collect($this->manual_recipients ?: []);
        } else {
            $emails = MarketingLead::query()
                ->when($this->audience_segment, fn ($query) => $query->where('segment', $this->audience_segment))
                ->when($this->audience_status, fn ($query) => $query->where('status', $this->audience_status))
                ->pluck('email');
        }

        $unsubscribed = MarketingUnsubscribe::query()
            ->whereIn('email', $emails->map(fn ($email) => strtolower(trim((string) $email)))
                ->filter()
                ->unique()
                ->values())
            ->pluck('email')
            ->map(fn ($email) => strtolower(trim((string) $email)));

        return $emails
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->reject(fn ($email) => $unsubscribed->contains($email))
            ->values();
    }
}
