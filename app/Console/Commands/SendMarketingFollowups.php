<?php

namespace App\Console\Commands;

use App\Models\MarketingFollowupEmail;
use App\Models\MarketingFollowupEmailOpen;
use App\Models\MarketingUnsubscribe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SendMarketingFollowups extends Command
{
    protected $signature = 'marketing:send-followups';

    protected $description = 'Send due scheduled marketing follow-up emails.';

    public function handle(): int
    {
        $followups = MarketingFollowupEmail::query()
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->oldest('scheduled_at')
            ->get();

        foreach ($followups as $followup) {
            $recipients = collect($followup->recipients ?: [])
                ->map(fn ($email) => strtolower(trim((string) $email)))
                ->filter()
                ->unique()
                ->values();

            $unsubscribed = MarketingUnsubscribe::query()
                ->whereIn('email', $recipients)
                ->pluck('email');

            $recipients = $recipients->diff($unsubscribed)->values();

            if ($recipients->isEmpty()) {
                $followup->update([
                    'status' => 'failed',
                    'delivery_error' => 'All recipients have unsubscribed.',
                ]);

                continue;
            }

            try {
                foreach ($recipients as $recipient) {
                    $tracker = MarketingFollowupEmailOpen::firstOrCreate(
                        [
                            'marketing_followup_email_id' => $followup->id,
                            'email' => $recipient,
                        ],
                        [
                            'tracking_id' => (string) Str::uuid(),
                        ]
                    );

                    $unsubscribeUrl = route('marketing.unsubscribe', ['email' => $recipient]);
                    $plainBody = trim($followup->body);
                    $trackingUrl = route('marketing.followup-open', ['trackingId' => $tracker->tracking_id], true);
                    $htmlBody = '<div style="white-space:pre-wrap;font-family:Arial,sans-serif;font-size:14px;line-height:1.5;color:#111827;">'
                        .e($plainBody)
                        .'</div>'
                        .'<img src="'.e($trackingUrl).'" width="1" height="1" alt="" style="width:1px;height:1px;border:0;opacity:0;">';

                    Mail::send([], [], function ($message) use ($recipient, $followup, $unsubscribeUrl, $plainBody, $htmlBody) {
                        $message->to($recipient)
                            ->subject($followup->subject)
                            ->text($plainBody)
                            ->html($htmlBody);

                        $headers = $message->getSymfonyMessage()->getHeaders();
                        $headers->addTextHeader('List-Unsubscribe', '<'.$unsubscribeUrl.'>');
                        $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');

                        if ($followup->attachment_path) {
                            $message->attach(Storage::path($followup->attachment_path), ['as' => $followup->attachment_name]);
                        }
                    });
                }

                $followup->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'delivery_error' => null,
                ]);
            } catch (\Throwable $exception) {
                $followup->update([
                    'status' => 'failed',
                    'delivery_error' => $exception->getMessage(),
                ]);
            }
        }

        $this->info('Processed '.$followups->count().' follow-up email'.($followups->count() === 1 ? '.' : 's.'));

        return self::SUCCESS;
    }
}
