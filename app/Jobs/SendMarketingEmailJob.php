<?php

namespace App\Jobs;

use App\Models\MarketingEmail;
use App\Models\MarketingCampaign;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Support\MarketingHtml;
use App\Support\MarketingTracking;

class SendMarketingEmailJob implements ShouldQueue
{
    use Queueable;

    public $tries = 1;

    public $timeout = 120;

    public function __construct(
        public int $marketingEmailId,
        public string $recipient,
        public string $trackingId,
    ) {}

    public function handle(): void
    {
        $email = MarketingEmail::with(['opens', 'mailService', 'signature'])->find($this->marketingEmailId);

        if (! $email) {
            return;
        }

        $wasPending = $email->delivery_status === 'pending';

        try {
            $unsubscribeUrl = route('marketing.unsubscribe', ['email' => $this->recipient]);
            $isHtml = $email->content_format === 'html';
            $plainBody = MarketingHtml::plainText((string) $email->body, $isHtml);
            $contentHtml = MarketingHtml::render((string) $email->body, $isHtml);
            $trackingUrl = MarketingTracking::url('marketing.open', ['trackingId' => $this->trackingId]);
            $mailer = $email->mailService
                ? app('mail.manager')->build([
                    'name' => 'marketing-service-'.$email->mailService->id,
                    'transport' => 'smtp',
                    'host' => $email->mailService->smtp_host,
                    'port' => $email->mailService->smtp_port,
                    'scheme' => $email->mailService->smtp_encryption === 'ssl' ? 'smtps' : 'smtp',
                    'auto_tls' => $email->mailService->smtp_encryption !== null,
                    'username' => $email->mailService->smtp_username,
                    'password' => $email->mailService->decryptedSmtpPassword(),
                    'timeout' => null,
                ])
                : null;

            $send = function ($message) use ($email, $unsubscribeUrl, $plainBody, $contentHtml, $trackingUrl) {
                $message->to($this->recipient)
                    ->subject($email->subject)
                    ->text($plainBody);

                if ($email->mailService) {
                    $message->from($email->mailService->from_email, $email->mailService->from_name ?: null);
                }

                $signatureHtml = '';

                if ($email->signature && Storage::exists($email->signature->image_path)) {
                    $embeddedImage = $message->embed(Storage::path($email->signature->image_path));
                    $signatureHtml = '<div style="margin-top:18px;"><img src="'.e($embeddedImage).'" alt="Email signature" style="display:block;max-width:100%;height:auto;border:0;"></div>';
                } else {
                    $signatureHtml = '<div style="margin-top:18px;"><img src="'.e($this->marketingDebugImageUrl()).'" width="320" alt="Faisal Imtiaz" style="display:block;width:320px;max-width:100%;height:auto;border:0;"></div>';
                }

                $htmlBody = $contentHtml
                    .$signatureHtml
                    .'<img src="'.e($trackingUrl).'" width="1" height="1" alt="" style="width:1px;height:1px;border:0;opacity:0;">';

                $message->html($htmlBody);

                $headers = $message->getSymfonyMessage()->getHeaders();
                $headers->addTextHeader('List-Unsubscribe', '<'.$unsubscribeUrl.'>');
                $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');

                if ($email->attachment_path) {
                    $message->attach(Storage::path($email->attachment_path), ['as' => $email->attachment_name]);
                }
            };

            if ($mailer) {
                $mailer->send([], [], $send);
            } else {
                Mail::send([], [], $send);
            }

            $email->increment('sent_count');
        } catch (\Throwable $exception) {
            $email->increment('failed_count');

            $errorMessage = $this->recipient.': '.$exception->getMessage();
            $email->update([
                'delivery_error' => $email->delivery_error
                    ? trim($email->delivery_error."\n".$errorMessage)
                    : $errorMessage,
            ]);
        }

        $email->refresh();

        $processedCount = $email->sent_count + $email->failed_count;

        if ($processedCount >= $email->recipient_count) {
            $email->update([
                'delivery_status' => $email->sent_count > 0 ? 'delivered' : 'failed',
                'sent_at' => now(),
            ]);

            if ($wasPending && $email->marketing_campaign_id) {
                $campaign = MarketingCampaign::find($email->marketing_campaign_id);
                if ($campaign) {
                    $campaign->refresh();
                    $pending = max(0, (int) $campaign->pending_emails - 1);
                    $campaign->update([
                        'pending_emails' => $pending,
                        'sent_count' => $campaign->sent_count + ($email->sent_count > 0 ? 1 : 0),
                        'failed_count' => $campaign->failed_count + ($email->sent_count > 0 ? 0 : 1),
                        'status' => $pending === 0 ? 'completed' : 'running',
                        'completed_at' => $pending === 0 ? now() : $campaign->completed_at,
                    ]);
                }
            }
        }
    }

    private function marketingDebugImageUrl(): string
    {
        $path = $this->marketingDebugImagePath();

        return route('marketing.debug-image', ['v' => file_exists($path) ? filemtime($path) : time()], true);
    }

    private function marketingDebugImagePath(): string
    {
        return public_path('assets/faisalimtiaz/email-logo.png');
    }
}
