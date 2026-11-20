<?php

namespace App\Jobs;

use App\Models\MarketingCampaign;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class StartMarketingCampaignJob implements ShouldQueue
{
    use Queueable;

    public $tries = 1;

    public $timeout = 120;

    public function __construct(public int $campaignId) {}

    public function handle(): void
    {
        $campaign = MarketingCampaign::with('steps')->find($this->campaignId);
        if (! $campaign || ! in_array($campaign->status, ['queued', 'running'], true)) {
            return;
        }

        $recipients = $campaign->audienceEmails();
        $steps = $campaign->steps;
        if ($recipients->isEmpty() || $steps->isEmpty()) {
            $campaign->update([
                'status' => 'completed',
                'recipient_count' => $recipients->count(),
                'sequence_count' => $steps->count(),
                'started_at' => $campaign->started_at ?: now(),
                'completed_at' => now(),
            ]);

            return;
        }

        $campaign->update([
            'status' => 'running',
            'recipient_count' => $recipients->count(),
            'sequence_count' => $steps->count(),
            'started_at' => $campaign->started_at ?: now(),
            'current_step' => 1,
            'pending_emails' => $recipients->count() * $steps->count(),
            'sent_count' => 0,
            'failed_count' => 0,
        ]);

        $sendRatePerMinute = max(1, min(20, (int) ($campaign->send_rate_per_minute ?: 5)));
        $recipientIndex = 0;
        $delayDays = 0;
        foreach ($steps as $step) {
            $delayDays += $step->step_order === 1 ? 0 : max(0, (int) $step->wait_days);
            foreach ($recipients as $recipient) {
                $sendDelaySeconds = (int) floor(($recipientIndex * 60) / $sendRatePerMinute);
                SendMarketingCampaignStepJob::dispatch($campaign->id, $step->id, $recipient, $sendDelaySeconds)
                    ->onConnection('database')
                    ->delay(now()->addDays($delayDays));
                $recipientIndex++;
            }
        }
    }
}
