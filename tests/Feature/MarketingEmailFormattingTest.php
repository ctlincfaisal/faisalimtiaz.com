<?php

namespace Tests\Feature;

use App\Jobs\SendMarketingEmailJob;
use App\Models\MarketingEmail;
use App\Models\MarketingEmailOpen;
use App\Models\MarketingFollowupEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

class MarketingEmailFormattingTest extends TestCase
{
    use RefreshDatabase;

    public static function lineEndings(): array
    {
        return [
            'Unix' => ["\n"],
            'Windows' => ["\r\n"],
            'carriage return' => ["\r"],
        ];
    }

    #[DataProvider('lineEndings')]
    public function test_initial_email_preserves_paragraphs_and_list_lines(string $newline): void
    {
        $body = $this->messageBody($newline);
        $email = $this->createEmail($body);
        $tracker = MarketingEmailOpen::create([
            'marketing_email_id' => $email->id,
            'email' => 'recipient@example.com',
            'tracking_id' => (string) Str::uuid(),
        ]);

        (new SendMarketingEmailJob($email->id, $tracker->email, $tracker->tracking_id))->handle();

        $this->assertSame('delivered', $email->fresh()->delivery_status);
        $message = $this->sentMessage();
        $this->assertFormatting($message, $body, $newline);
        $this->assertStringContainsString(route('marketing.open', ['trackingId' => $tracker->tracking_id]), $message->getHtmlBody());
        $this->assertStringContainsString('marketing/debug-image', $message->getHtmlBody());
        $this->assertTrue($message->getHeaders()->has('List-Unsubscribe'));
    }

    #[DataProvider('lineEndings')]
    public function test_followup_preserves_paragraphs_and_list_lines(string $newline): void
    {
        $body = $this->messageBody($newline);
        $email = $this->createEmail('Original message');
        $followup = MarketingFollowupEmail::create([
            'marketing_email_id' => $email->id,
            'recipients' => ['recipient@example.com'],
            'recipient_count' => 1,
            'subject' => 'Follow-up',
            'body' => $body,
            'status' => 'pending',
            'scheduled_at' => now()->subMinute(),
        ]);

        $this->artisan('marketing:send-followups')->assertSuccessful();

        $this->assertSame('sent', $followup->fresh()->status);
        $message = $this->sentMessage();
        $this->assertFormatting($message, $body, $newline);
        $this->assertStringContainsString('marketing/followup-open/', $message->getHtmlBody());
        $this->assertTrue($message->getHeaders()->has('List-Unsubscribe'));
    }

    private function createEmail(string $body): MarketingEmail
    {
        return MarketingEmail::create([
            'recipients' => ['recipient@example.com'],
            'recipient_count' => 1,
            'subject' => 'Formatting check',
            'body' => $body,
            'delivery_status' => 'pending',
        ]);
    }

    private function messageBody(string $newline): string
    {
        return implode($newline, [
            'Hi,',
            '',
            'My experience includes:',
            '• Laravel, PHP & MySQL',
            '• React Native (Android & iOS)',
            '',
            '<strong>Literal text</strong>',
            'Best regards,',
            'Faisal Imtiaz',
        ]);
    }

    private function sentMessage(): Email
    {
        $messages = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);

        return $messages->first()->getOriginalMessage();
    }

    private function assertFormatting(Email $message, string $body, string $newline): void
    {
        $html = $message->getHtmlBody();
        $this->assertSame($body, $message->getTextBody());
        $this->assertStringContainsString('Hi,<br>'.$newline.'<br>'.$newline.'My experience includes:<br>', $html);
        $this->assertStringContainsString('• Laravel, PHP &amp; MySQL<br>'.$newline.'• React Native (Android &amp; iOS)<br>', $html);
        $this->assertStringContainsString('&lt;strong&gt;Literal text&lt;/strong&gt;<br>', $html);
        $this->assertStringNotContainsString('<strong>', $html);
        $this->assertStringNotContainsString('white-space:pre-wrap', $html);

        // Recover the original lines without depending on any CSS support.
        $document = new \DOMDocument;
        $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $content = $document->getElementsByTagName('div')->item(0);
        $this->assertSame(8, $content->getElementsByTagName('br')->length);
        $this->assertSame($body, $content->textContent);
    }
}
