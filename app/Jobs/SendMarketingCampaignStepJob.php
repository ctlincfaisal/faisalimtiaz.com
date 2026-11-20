<?php

namespace App\Jobs;

use App\Models\MarketingCampaign;
use App\Models\MarketingCampaignStep;
use App\Models\MarketingEmail;
use App\Models\MarketingEmailOpen;
use App\Models\MarketingLead;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use App\Support\MarketingHtml;

class SendMarketingCampaignStepJob implements ShouldQueue
{
    use Queueable;

    public $tries = 1;

    public $timeout = 120;

    public function __construct(
        public int $campaignId,
        public int $stepId,
        public string $recipient,
        public int $sendDelaySeconds = 0,
    ) {}

    public function handle(): void
    {
        $campaign = MarketingCampaign::find($this->campaignId);
        $step = MarketingCampaignStep::find($this->stepId);
        if (! $campaign || ! $step || ! in_array($campaign->status, ['queued', 'running'], true)) {
            return;
        }

        if ($campaign->stop_on_reply && $step->step_order > 1 && $this->recipientHasOpenedCampaignEmail()) {
            $this->decrementPending($campaign);

            return;
        }

        $lead = MarketingLead::query()->where('email', $this->recipient)->first();
        $replacements = [
            '{{first_name}}' => $lead?->first_name ?: '',
            '{{last_name}}' => $lead?->last_name ?: '',
            '{{company}}' => $lead?->company ?: '',
            '{{email}}' => $this->recipient,
        ];
        $subject = strtr($step->subject, $replacements);
        $body = strtr($step->body, $replacements);
        $trackingId = (string) Str::uuid();

        $email = MarketingEmail::create([
            'marketing_account_id' => $campaign->marketing_account_id,
            'marketing_campaign_id' => $campaign->id,
            'marketing_campaign_step_id' => $step->id,
            'template_name' => $step->template?->name ?: 'Campaign step '.$step->step_order,
            'marketing_mail_service_id' => $step->marketing_mail_service_id,
            'marketing_signature_id' => $step->marketing_signature_id,
            'recipients' => [$this->recipient],
            'recipient_count' => 1,
            'subject' => $subject,
            'body' => $step->content_format === 'html' ? MarketingHtml::sanitize($body) : $body,
            'content_format' => $step->content_format,
            'attachment_path' => $step->attachment_path,
            'attachment_name' => $step->attachment_name,
            'delivery_status' => 'pending',
            'sent_count' => 0,
            'failed_count' => 0,
        ]);
        $tracking = MarketingEmailOpen::create([
            'marketing_account_id' => $campaign->marketing_account_id,
            'marketing_email_id' => $email->id,
            'email' => $this->recipient,
            'tracking_id' => $trackingId,
        ]);
        $campaign->update(['current_step' => max((int) $campaign->current_step, (int) $step->step_order)]);

        SendMarketingEmailJob::dispatch($email->id, $this->recipient, $tracking->tracking_id)
            ->onConnection('database')
            ->delay(now()->addSeconds($this->sendDelaySeconds));
    }

    private function recipientHasOpenedCampaignEmail(): bool
    {
        $emailIds = MarketingEmail::query()
            ->where('marketing_campaign_id', $this->campaignId)
            ->pluck('id');

        return $emailIds->isNotEmpty()
            && MarketingEmailOpen::query()
                ->whereIn('marketing_email_id', $emailIds)
                ->where('email', $this->recipient)
                ->whereNotNull('opened_at')
                ->exists();
    }

    private function decrementPending(MarketingCampaign $campaign): void
    {
        $campaign->refresh();
        $pending = max(0, (int) $campaign->pending_emails - 1);
        $campaign->update([
            'pending_emails' => $pending,
            'completed_at' => $pending === 0 ? now() : $campaign->completed_at,
            'status' => $pending === 0 ? 'completed' : 'running',
        ]);
    }
}
