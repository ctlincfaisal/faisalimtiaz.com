<?php

namespace Tests\Feature;

use App\Models\MarketingEmail;
use App\Models\MarketingEmailOpen;
use App\Models\MarketingFollowupEmail;
use App\Models\MarketingFollowupEmailOpen;
use App\Models\MarketingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarketingActiveClientsTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_clients_requires_marketing_login(): void
    {
        $this->get(route('marketing', ['tab' => 'active-clients']))
            ->assertRedirect(route('marketing.login'));
    }

    public function test_lists_only_opened_campaign_and_followup_emails_in_date_order(): void
    {
        $email = $this->createEmail(['template_name' => 'Introduction']);
        $this->createOpen($email, 'client@example.com', '2026-10-20 10:00:00');
        $this->createOpen($email, 'unopened@example.com');

        $followup = MarketingFollowupEmail::create([
            'marketing_email_id' => $email->id,
            'template_name' => 'Follow-up offer',
            'recipients' => ['client@example.com', 'other@example.com'],
            'recipient_count' => 2,
            'subject' => 'Checking in',
            'body' => 'Follow-up body',
            'status' => 'sent',
            'scheduled_at' => '2026-10-21 08:00:00',
            'sent_at' => '2026-10-21 08:00:00',
        ]);
        foreach (['client@example.com', 'other@example.com'] as $recipient) {
            MarketingFollowupEmailOpen::create([
                'marketing_followup_email_id' => $followup->id,
                'email' => $recipient,
                'tracking_id' => (string) Str::uuid(),
                'opened_at' => '2026-10-21 09:00:00',
            ]);
        }

        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'active-clients']))
            ->assertOk()
            ->assertViewHas('databaseReady', true)
            ->assertSee('Active Clients')
            ->assertSee('Email template name')
            ->assertSee('Date sent')
            ->assertSee('Date opened')
            ->assertSee('2 active contacts')
            ->assertSee('3 opened emails')
            ->assertSeeInOrder(['Follow-up offer', 'Introduction'])
            ->assertSee('Oct 20, 2026 08:00 AM')
            ->assertSee('Oct 20, 2026 10:00 AM')
            ->assertDontSee('unopened@example.com')
            ->assertViewHas('activeClients', function ($clients) {
                return $clients->count() === 3
                    && $clients->where('email', 'client@example.com')->count() === 2;
            });
    }

    public function test_tracking_pixels_add_contacts_and_preserve_the_first_open_date(): void
    {
        $email = $this->createEmail();
        $tracker = $this->createOpen($email, 'client@example.com');
        $followup = MarketingFollowupEmail::create([
            'marketing_email_id' => $email->id,
            'recipients' => ['followup@example.com'],
            'recipient_count' => 1,
            'subject' => 'Follow-up',
            'body' => 'Body',
            'status' => 'sent',
            'scheduled_at' => now()->subHour(),
            'sent_at' => now()->subHour(),
        ]);
        $followupTracker = MarketingFollowupEmailOpen::create([
            'marketing_followup_email_id' => $followup->id,
            'email' => 'followup@example.com',
            'tracking_id' => (string) Str::uuid(),
        ]);

        $this->travelTo(now()->startOfSecond());
        $firstOpen = now();
        foreach (['marketing.open' => $tracker, 'marketing.followup-open' => $followupTracker] as $route => $open) {
            $this->get(route($route, $open->tracking_id))
                ->assertOk()->assertHeader('Content-Type', 'image/gif');
        }

        $this->travel(1)->hour();
        foreach (['marketing.open' => $tracker, 'marketing.followup-open' => $followupTracker] as $route => $open) {
            $this->get(route($route, $open->tracking_id))->assertOk();
            $this->assertTrue($open->fresh()->opened_at->equalTo($firstOpen));
            $this->assertEquals(2, $open->fresh()->open_count);
        }

        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'active-clients']))
            ->assertOk()
            ->assertSee('client@example.com')
            ->assertSee('followup@example.com')
            ->assertSee($firstOpen->format('M d, Y h:i A'))
            ->assertViewHas('activeClients', fn ($clients) => $clients->count() === 2);
    }

    public function test_legacy_records_show_unavailable_template_and_sent_dates_without_guessing(): void
    {
        $email = $this->createEmail(['sent_at' => null]);
        $this->createOpen($email, 'legacy@example.com', '2026-10-20 10:00:00');

        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'active-clients']))
            ->assertOk()
            ->assertViewHas('databaseReady', true)
            ->assertSee('legacy@example.com')
            ->assertSee('Template not recorded')
            ->assertSee('Not recorded');
    }

    public function test_empty_state_excludes_recipients_without_opens(): void
    {
        $this->createOpen($this->createEmail(), 'unopened@example.com');

        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'active-clients']))
            ->assertOk()
            ->assertViewHas('databaseReady', true)
            ->assertSee('No email opens recorded yet.')
            ->assertSee('0 active contacts')
            ->assertDontSee('unopened@example.com');
    }

    public function test_legacy_template_matches_subject_options_and_message_with_different_line_endings(): void
    {
        $template = $this->createTemplate();
        $template->update(['subject_options' => ['Alternative subject'], 'content' => "Hello\n\nHow are you?"]);
        $email = $this->createEmail(['subject' => 'Alternative subject', 'body' => "Hello\r\n\r\nHow are you?\r\n"]);
        $this->createOpen($email, 'legacy@example.com', '2026-10-20 10:00:00');

        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'active-clients']))
            ->assertOk()
            ->assertSee('Introduction template')
            ->assertSee('Matched by subject and message')
            ->assertViewHas('activeClients', fn ($clients) => $clients->first()['template_inferred'] === true);
    }

    public function test_legacy_template_is_not_guessed_from_subject_alone(): void
    {
        $template = $this->createTemplate();
        $email = $this->createEmail(['subject' => $template->subject, 'body' => 'Different message']);
        $this->createOpen($email, 'legacy@example.com', '2026-10-20 10:00:00');

        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'active-clients']))
            ->assertOk()
            ->assertSee('Template not recorded')
            ->assertViewHas('activeClients', fn ($clients) => $clients->first()['template_inferred'] === false);
    }

    public function test_legacy_template_is_not_guessed_when_multiple_templates_match(): void
    {
        $template = $this->createTemplate();
        $duplicate = $template->replicate();
        $duplicate->name = 'Another template';
        $duplicate->save();
        $email = $this->createEmail(['subject' => $template->subject, 'body' => $template->content]);
        $this->createOpen($email, 'legacy@example.com', '2026-10-20 10:00:00');

        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'active-clients']))
            ->assertOk()
            ->assertSee('Template not recorded')
            ->assertViewHas('activeClients', fn ($clients) => $clients->first()['template_inferred'] === false);
    }

    public function test_sending_saves_template_name_even_if_template_is_later_deleted(): void
    {
        Queue::fake();
        $template = $this->createTemplate();

        $response = $this->withSession(['marketing_authenticated' => true])
            ->postJson(route('marketing.send'), [
                'recipients' => 'client@example.com',
                'subject' => $template->subject,
                'content' => $template->content,
                'template_id' => $template->id,
            ])->assertOk();

        $email = MarketingEmail::findOrFail($response->json('id'));
        $template->delete();
        $this->assertSame('Introduction template', $email->fresh()->template_name);
        $email->opens()->first()->update(['opened_at' => now()]);

        $this->get(route('marketing', ['tab' => 'active-clients']))
            ->assertOk()->assertSee('Introduction template');
    }

    public function test_followup_saves_template_name_and_legacy_followups_use_their_template(): void
    {
        $template = $this->createTemplate();
        $email = $this->createEmail();

        $this->withSession(['marketing_authenticated' => true])
            ->post(route('marketing.followups.store'), [
                'marketing_email_id' => $email->id,
                'template_id' => $template->id,
                'scheduled_at' => now()->addDay()->toDateTimeString(),
            ])->assertSessionHasNoErrors()->assertRedirect();

        $followup = MarketingFollowupEmail::firstOrFail();
        $this->assertSame($template->name, $followup->template_name);
        $followup->update(['template_name' => null, 'status' => 'sent', 'sent_at' => now()]);
        MarketingFollowupEmailOpen::create([
            'marketing_followup_email_id' => $followup->id,
            'email' => 'client@example.com',
            'tracking_id' => (string) Str::uuid(),
            'opened_at' => now(),
        ]);

        $this->get(route('marketing', ['tab' => 'active-clients']))
            ->assertOk()->assertSee('Introduction template');
    }

    private function createEmail(array $attributes = []): MarketingEmail
    {
        return MarketingEmail::create(array_merge([
            'recipients' => ['client@example.com', 'unopened@example.com'],
            'recipient_count' => 2,
            'subject' => 'Introduction subject',
            'body' => 'Body',
            'delivery_status' => 'delivered',
            'sent_at' => '2026-10-20 08:00:00',
        ], $attributes));
    }

    private function createOpen(MarketingEmail $email, string $recipient, ?string $openedAt = null): MarketingEmailOpen
    {
        return MarketingEmailOpen::create([
            'marketing_email_id' => $email->id,
            'email' => $recipient,
            'tracking_id' => (string) Str::uuid(),
            'opened_at' => $openedAt,
        ]);
    }

    private function createTemplate(): MarketingTemplate
    {
        return MarketingTemplate::create([
            'name' => 'Introduction template',
            'subject' => 'Introduction',
            'content' => 'Template body',
        ]);
    }
}
