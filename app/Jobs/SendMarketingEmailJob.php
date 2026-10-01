<?php

namespace App\Jobs;

use App\Models\MarketingEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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
        $email = MarketingEmail::with('opens')->find($this->marketingEmailId);

        if (! $email) {
            return;
        }

        try {
            $unsubscribeUrl = route('marketing.unsubscribe', ['email' => $this->recipient]);
            $plainBody = trim($email->body);
            $trackingUrl = route('marketing.open', ['trackingId' => $this->trackingId], true);
            $htmlBody = '<div style="white-space:pre-wrap;font-family:Arial,sans-serif;font-size:14px;line-height:1.5;color:#111827;">'
                .e($plainBody)
                .'</div>'
                .'<div style="margin-top:18px;">'
                .'<img src="'.e($this->marketingDebugImageUrl()).'" width="320" alt="Faisal Imtiaz" style="display:block;width:320px;max-width:100%;height:auto;border:0;">'
                .'</div>'
                .'<img src="'.e($trackingUrl).'" width="1" height="1" alt="" style="width:1px;height:1px;border:0;opacity:0;">';

            Mail::send([], [], function ($message) use ($email, $unsubscribeUrl, $plainBody, $htmlBody) {
                $message->to($this->recipient)
                    ->subject($email->subject)
                    ->text($plainBody)
                    ->html($htmlBody);

                $headers = $message->getSymfonyMessage()->getHeaders();
                $headers->addTextHeader('List-Unsubscribe', '<'.$unsubscribeUrl.'>');
                $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');

                if ($email->attachment_path) {
                    $message->attach(Storage::path($email->attachment_path), ['as' => $email->attachment_name]);
                }
            });

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
