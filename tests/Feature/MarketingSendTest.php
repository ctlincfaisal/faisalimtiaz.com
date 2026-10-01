<?php

namespace Tests\Feature;

use App\Jobs\SendMarketingEmailJob;
use App\Models\MarketingEmail;
use App\Models\MarketingEmailOpen;
use App\Models\MarketingUnsubscribe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarketingSendTest extends TestCase
{
    use RefreshDatabase;

    private function authenticate(): void
    {
        $this->withSession(['marketing_authenticated' => true]);
    }

    public function test_send_dispatches_a_job_per_recipient_and_returns_immediately(): void
    {
        Queue::fake();

        $this->authenticate();

        $response = $this->postJson(route('marketing.send'), [
            'recipients' => 'one@example.com, two@example.com, one@example.com',
            'subject' => 'Hello',
            'content' => 'Test body',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['id', 'recipient_count']);

        $this->assertEquals(2, $response->json('recipient_count'));

        $email = MarketingEmail::findOrFail($response->json('id'));
        $this->assertEquals('pending', $email->delivery_status);
        $this->assertEquals(2, $email->recipient_count);
        $this->assertNull($email->sent_at);

        Queue::assertPushed(SendMarketingEmailJob::class, 2);

        $this->assertEquals(2, MarketingEmailOpen::where('marketing_email_id', $email->id)->count());
    }

    public function test_job_sends_the_email_and_tracks_progress(): void
    {
        Queue::fake();

        $this->authenticate();

        $response = $this->postJson(route('marketing.send'), [
            'recipients' => 'one@example.com, two@example.com',
            'subject' => 'Hello',
            'content' => 'Test body',
        ]);

        $email = MarketingEmail::findOrFail($response->json('id'));
        $trackers = MarketingEmailOpen::where('marketing_email_id', $email->id)->get();

        (new SendMarketingEmailJob($email->id, 'one@example.com', $trackers->firstWhere('email', 'one@example.com')->tracking_id))->handle();

        $email->refresh();
        $this->assertEquals(1, $email->sent_count);
        $this->assertEquals(0, $email->failed_count);
        $this->assertEquals('pending', $email->delivery_status);

        $this->assertCount(1, app('mailer')->getSymfonyTransport()->messages());

        (new SendMarketingEmailJob($email->id, 'two@example.com', $trackers->firstWhere('email', 'two@example.com')->tracking_id))->handle();

        $email->refresh();
        $this->assertEquals(2, $email->sent_count);
        $this->assertEquals('delivered', $email->delivery_status);
        $this->assertNotNull($email->sent_at);

        $this->assertCount(2, app('mailer')->getSymfonyTransport()->messages());
    }

    public function test_progress_endpoint_reports_real_time_counters(): void
    {
        $this->authenticate();

        $email = MarketingEmail::create([
            'recipients' => ['one@example.com', 'two@example.com'],
            'recipient_count' => 2,
            'subject' => 'Hello',
            'body' => 'Test body',
            'delivery_status' => 'pending',
            'sent_count' => 1,
            'failed_count' => 0,
        ]);

        $this->getJson(route('marketing.progress', $email))
            ->assertOk()
            ->assertJson([
                'id' => $email->id,
                'recipient_count' => 2,
                'sent_count' => 1,
                'failed_count' => 0,
                'delivery_status' => 'pending',
            ]);
    }

    public function test_dashboard_shows_pending_email_progress(): void
    {
        $this->authenticate();

        MarketingEmail::create([
            'recipients' => ['one@example.com', 'two@example.com'],
            'recipient_count' => 2,
            'subject' => 'In progress send',
            'body' => 'Test body',
            'delivery_status' => 'pending',
            'sent_count' => 1,
            'failed_count' => 0,
        ]);

        $this->get(route('marketing', ['tab' => 'dashboard']))
            ->assertOk()
            ->assertSee('Sending in progress')
            ->assertSee('In progress send')
            ->assertSee('1/2 sent', false);
    }

    public function test_contacts_page_shows_comma_separated_emails(): void
    {
        $this->authenticate();

        MarketingEmail::create([
            'recipients' => ['bob@example.com', 'alice@example.com', 'bob@example.com'],
            'recipient_count' => 2,
            'subject' => 'Batch',
            'body' => 'Body',
            'delivery_status' => 'delivered',
            'sent_count' => 2,
            'failed_count' => 0,
            'sent_at' => now(),
        ]);

        $this->get(route('marketing', ['tab' => 'contacts']))
            ->assertOk()
            ->assertSee('alice@example.com, bob@example.com', false);
    }

    public function test_sent_email_detail_sorts_recipients_by_opened(): void
    {
        $this->authenticate();

        $email = MarketingEmail::create([
            'recipients' => ['a@example.com', 'b@example.com'],
            'recipient_count' => 2,
            'subject' => 'Batch',
            'body' => 'Body',
            'delivery_status' => 'delivered',
            'sent_count' => 2,
            'failed_count' => 0,
            'sent_at' => now(),
        ]);

        MarketingEmailOpen::create([
            'marketing_email_id' => $email->id,
            'email' => 'b@example.com',
            'tracking_id' => (string) Str::uuid(),
            'opened_at' => now(),
            'last_opened_at' => now(),
            'open_count' => 1,
        ]);

        $response = $this->get(route('marketing', ['tab' => 'sent-email-detail', 'email' => $email->id, 'sort' => 'opened']));

        $response->assertOk();

        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'b@example.com') < strpos($content, 'a@example.com'));
    }

    public function test_send_skips_unsubscribed_recipients(): void
    {
        Queue::fake();

        MarketingUnsubscribe::create(['email' => 'unsub@example.com', 'unsubscribed_at' => now()]);

        $this->authenticate();

        $response = $this->postJson(route('marketing.send'), [
            'recipients' => 'unsub@example.com, keep@example.com',
            'subject' => 'Hello',
            'content' => 'Test body',
        ]);

        $response->assertOk();
        $this->assertEquals(1, $response->json('recipient_count'));
        Queue::assertPushed(SendMarketingEmailJob::class, 1);
    }
}
