<?php

namespace App\Console\Commands;

use App\Models\MarketingFollowupEmail;
use App\Models\MarketingFollowupEmailOpen;
use App\Models\MarketingUnsubscribe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Support\MarketingHtml;
use App\Support\MarketingTracking;

class SendMarketingFollowups extends Command
{
    protected $signature = 'marketing:send-followups';

    protected $description = 'Send due scheduled marketing follow-up emails.';

    public function handle(): int
    {
        $followups = MarketingFollowupEmail::with(['mailService', 'signature'])
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
                $mailer = $followup->mailService
                    ? app('mail.manager')->build([
                        'name' => 'marketing-service-'.$followup->mailService->id,
                        'transport' => 'smtp',
                        'host' => $followup->mailService->smtp_host,
                        'port' => $followup->mailService->smtp_port,
                        'scheme' => $followup->mailService->smtp_encryption === 'ssl' ? 'smtps' : 'smtp',
                        'auto_tls' => $followup->mailService->smtp_encryption !== null,
                        'username' => $followup->mailService->smtp_username,
                        'password' => $followup->mailService->decryptedSmtpPassword(),
                        'timeout' => null,
                    ])
                    : null;

                foreach ($recipients as $recipient) {
                    $tracker = MarketingFollowupEmailOpen::firstOrCreate(
                        [
                            'marketing_followup_email_id' => $followup->id,
                            'email' => $recipient,
                        ],
                        [
                            'tracking_id' => (string) Str::uuid(),
                            'marketing_account_id' => $followup->marketing_account_id,
                        ]
                    );

                    $unsubscribeUrl = route('marketing.unsubscribe', ['email' => $recipient]);
                    $isHtml = $followup->content_format === 'html';
                    $plainBody = MarketingHtml::plainText((string) $followup->body, $isHtml);
                    $contentHtml = MarketingHtml::render((string) $followup->body, $isHtml);
                    $trackingUrl = MarketingTracking::url('marketing.followup-open', ['trackingId' => $tracker->tracking_id]);
                    $send = function ($message) use ($recipient, $followup, $unsubscribeUrl, $plainBody, $contentHtml, $trackingUrl) {
                        $message->to($recipient)
                            ->subject($followup->subject)
                            ->text($plainBody);

                        if ($followup->mailService) {
                            $message->from($followup->mailService->from_email, $followup->mailService->from_name ?: null);
                        }

                        $signatureHtml = '';

                        if ($followup->signature && Storage::exists($followup->signature->image_path)) {
                            $embeddedImage = $message->embed(Storage::path($followup->signature->image_path));
                            $signatureHtml = '<div style="margin-top:18px;"><img src="'.e($embeddedImage).'" alt="Email signature" style="display:block;max-width:100%;height:auto;border:0;"></div>';
                        }

                        $htmlBody = $contentHtml
                            .$signatureHtml
                            .'<img src="'.e($trackingUrl).'" width="1" height="1" alt="" style="width:1px;height:1px;border:0;opacity:0;">';

                        $message->html($htmlBody);

                        $headers = $message->getSymfonyMessage()->getHeaders();
                        $headers->addTextHeader('List-Unsubscribe', '<'.$unsubscribeUrl.'>');
                        $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');

                        if ($followup->attachment_path) {
                            $message->attach(Storage::path($followup->attachment_path), ['as' => $followup->attachment_name]);
                        }
                    };

                    if ($mailer) {
                        $mailer->send([], [], $send);
                    } else {
                        Mail::send([], [], $send);
                    }
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
