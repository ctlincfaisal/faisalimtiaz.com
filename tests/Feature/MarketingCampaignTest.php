<?php

namespace Tests\Feature;

use App\Jobs\SendMarketingCampaignStepJob;
use App\Jobs\SendMarketingEmailJob;
use App\Jobs\StartMarketingCampaignJob;
use App\Models\MarketingCampaign;
use App\Models\MarketingCampaignStep;
use App\Models\MarketingEmail;
use App\Models\MarketingEmailOpen;
use App\Models\MarketingLead;
use App\Models\MarketingMailService;
use App\Models\MarketingSignature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MarketingCampaignTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        MarketingMailService::create([
            'name' => 'Test SMTP',
            'from_email' => 'sender@example.com',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_username' => 'sender@example.com',
            'smtp_password' => 'secret',
            'smtp_encryption' => 'tls',
            'is_active' => true,
        ]);

        MarketingSignature::create([
            'name' => 'Test signature',
            'image_path' => 'marketing-signatures/test.png',
            'image_name' => 'test.png',
            'mime_type' => 'image/png',
            'is_active' => true,
        ]);
    }

    public function test_campaigns_page_replaces_send_page_in_the_navigation(): void
    {
        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'campaigns']))
            ->assertOk()
            ->assertSee('Campaigns')
            ->assertSee('Create Campaign')
            ->assertSee('All Campaigns')
            ->assertSee('Choose your audience')
            ->assertSee('Content')
            ->assertSee('Sequence')
            ->assertSee('Schedule')
            ->assertDontSee('Campaign storage is not ready yet.', false)
            ->assertViewHas('campaignsReady', true);
    }

    public function test_campaign_can_be_built_from_a_lead_segment_and_started(): void
    {
        Queue::fake();
        MarketingLead::create(['first_name' => 'Emma', 'email' => 'emma@example.com', 'status' => 'new', 'segment' => 'Photographers']);

        $this->withSession(['marketing_authenticated' => true]);
        $audience = $this->post(route('marketing.campaigns.audience'), [
            'name' => 'Photographer outreach',
            'description' => 'Introduce the platform',
            'audience_type' => 'segment',
            'audience_segment' => 'Photographers',
            'stop_on_reply' => '1',
        ]);
        $campaign = MarketingCampaign::firstOrFail();
        $audience->assertRedirect(route('marketing', ['tab' => 'campaigns', 'campaign_id' => $campaign->id, 'campaign_step' => 2]));
        $this->assertSame(1, $campaign->recipient_count);

        $this->post(route('marketing.campaigns.content'), [
            'campaign_id' => $campaign->id,
            'subject' => 'Hello {{first_name}}',
            'content' => 'Welcome to the campaign.',
            'marketing_mail_service_id' => MarketingMailService::firstOrFail()->id,
            'marketing_signature_id' => MarketingSignature::firstOrFail()->id,
        ])->assertRedirect(route('marketing', ['tab' => 'campaigns', 'campaign_id' => $campaign->id, 'campaign_step' => 3]));

        $this->post(route('marketing.campaigns.sequence'), [
            'campaign_id' => $campaign->id,
            'sequence' => [
                ['wait_days' => 0, 'subject' => 'Hello {{first_name}}', 'content' => 'Welcome to the campaign.'],
                ['wait_days' => 3, 'subject' => 'Following up', 'content' => 'Just following up.'],
            ],
        ])->assertRedirect(route('marketing', ['tab' => 'campaigns', 'campaign_id' => $campaign->id, 'campaign_step' => 4]));

        $this->post(route('marketing.campaigns.start'), [
            'campaign_id' => $campaign->id,
            'scheduled_at' => now()->addMinute()->format('Y-m-d H:i:s'),
        ])->assertRedirect(route('marketing', ['tab' => 'campaigns', 'campaign_id' => $campaign->id, 'campaign_step' => 4]));

        $campaign->refresh();
        $this->assertSame('queued', $campaign->status);
        $this->assertSame(2, $campaign->sequence_count);
        Queue::assertPushed(StartMarketingCampaignJob::class, 1);
    }

    public function test_campaign_content_can_be_saved_as_a_draft_without_delivery_settings(): void
    {
        $campaign = MarketingCampaign::create([
            'name' => 'Draft campaign',
            'audience_type' => 'all_leads',
            'status' => 'draft',
        ]);
        MarketingMailService::query()->delete();
        MarketingSignature::query()->delete();

        $this->withSession(['marketing_authenticated' => true])
            ->post(route('marketing.campaigns.content'), [
                'campaign_id' => $campaign->id,
                'subject' => 'Draft subject',
                'content' => '<h1>Draft content</h1><p>Saved for later.</p>',
                'save_draft' => '1',
            ])
            ->assertRedirect(route('marketing', ['tab' => 'campaigns', 'campaign_id' => $campaign->id, 'campaign_step' => 2]))
            ->assertSessionHas('marketing_success', 'Campaign draft saved.');

        $step = MarketingCampaignStep::where('marketing_campaign_id', $campaign->id)->firstOrFail();
        $this->assertSame('html', $step->content_format);
        $this->assertStringContainsString('<h1>Draft content</h1>', $step->body);
    }

    public function test_campaign_cannot_start_without_smtp_service(): void
    {
        Queue::fake();
        $campaign = MarketingCampaign::create([
            'name' => 'SMTP required campaign',
            'audience_type' => 'all_leads',
            'status' => 'draft',
            'recipient_count' => 1,
        ]);
        MarketingCampaignStep::create([
            'marketing_campaign_id' => $campaign->id,
            'step_order' => 1,
            'subject' => 'Hello',
            'body' => 'Body',
        ]);
        MarketingMailService::query()->delete();

        $this->withSession(['marketing_authenticated' => true])
            ->post(route('marketing.campaigns.start'), [
                'campaign_id' => $campaign->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('marketing_error', 'Add at least one SMTP service in Settings → Email settings before creating or sending a campaign.');

        $campaign->refresh();
        $this->assertSame('draft', $campaign->status);
        Queue::assertNothingPushed();
    }

    public function test_campaign_cannot_start_without_signature(): void
    {
        Queue::fake();
        $campaign = MarketingCampaign::create([
            'name' => 'Signature required campaign',
            'audience_type' => 'all_leads',
            'status' => 'draft',
            'recipient_count' => 1,
        ]);
        MarketingCampaignStep::create([
            'marketing_campaign_id' => $campaign->id,
            'step_order' => 1,
            'subject' => 'Hello',
            'body' => 'Body',
        ]);
        MarketingSignature::query()->delete();

        $this->withSession(['marketing_authenticated' => true])
            ->post(route('marketing.campaigns.start'), [
                'campaign_id' => $campaign->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('marketing_error', 'Add at least one email signature in Settings → Email settings before creating or sending a campaign.');

        $campaign->refresh();
        $this->assertSame('draft', $campaign->status);
        Queue::assertNothingPushed();
    }

    public function test_campaign_jobs_create_tracked_emails_and_finish_progress(): void
    {
        Queue::fake();
        MarketingLead::create(['first_name' => 'Emma', 'email' => 'emma@example.com', 'status' => 'new']);
        $campaign = MarketingCampaign::create([
            'name' => 'Queued campaign',
            'audience_type' => 'all_leads',
            'status' => 'queued',
            'stop_on_reply' => true,
        ]);
        $step = MarketingCampaignStep::create([
            'marketing_campaign_id' => $campaign->id,
            'step_order' => 1,
            'subject' => 'Hello {{first_name}}',
            'body' => 'Hi {{first_name}}',
            'content_format' => 'plain',
        ]);

        (new StartMarketingCampaignJob($campaign->id))->handle();
        $campaign->refresh();
        $this->assertSame('running', $campaign->status);
        $this->assertSame(1, $campaign->pending_emails);
        Queue::assertPushed(SendMarketingCampaignStepJob::class, 1);

        (new SendMarketingCampaignStepJob($campaign->id, $step->id, 'emma@example.com'))->handle();
        $email = MarketingEmail::where('marketing_campaign_id', $campaign->id)->firstOrFail();
        Queue::assertPushed(SendMarketingEmailJob::class, 1);
        $this->assertSame('Hello Emma', $email->subject);
        $this->assertSame('Hi Emma', $email->body);
    }

    public function test_all_campaigns_support_search_status_type_and_date_filters(): void
    {
        MarketingCampaign::create([
            'name' => 'Wedding photographers outreach',
            'description' => 'Photographer introduction',
            'status' => 'running',
            'audience_type' => 'segment',
            'audience_segment' => 'Photographers',
            'created_at' => now()->subDay(),
        ]);
        MarketingCampaign::create([
            'name' => 'Old venue follow-up',
            'description' => 'Venue list',
            'status' => 'completed',
            'audience_type' => 'all_leads',
            'created_at' => now()->subDays(10),
        ]);

        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', [
                'tab' => 'campaigns-list',
                'campaign_search' => 'photographer',
                'campaign_status' => 'running',
                'campaign_audience_type' => 'segment',
                'campaign_from' => now()->subDays(2)->toDateString(),
                'campaign_to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Wedding photographers outreach')
            ->assertDontSee('Old venue follow-up')
            ->assertViewHas('campaigns', fn ($campaigns) => $campaigns->total() === 1);
    }

    public function test_campaign_detail_shows_delivery_records_and_open_stats(): void
    {
        $campaign = MarketingCampaign::create([
            'name' => 'Running outreach',
            'status' => 'running',
            'audience_type' => 'all_leads',
            'recipient_count' => 2,
            'sequence_count' => 1,
            'sent_count' => 2,
            'pending_emails' => 1,
        ]);
        $step = MarketingCampaignStep::create([
            'marketing_campaign_id' => $campaign->id,
            'step_order' => 1,
            'subject' => 'Hello',
            'body' => 'Body',
        ]);
        $email = MarketingEmail::create([
            'marketing_campaign_id' => $campaign->id,
            'marketing_campaign_step_id' => $step->id,
            'recipients' => ['opened@example.com'],
            'recipient_count' => 1,
            'subject' => 'Hello',
            'body' => 'Body',
            'delivery_status' => 'delivered',
            'sent_count' => 1,
            'sent_at' => now(),
        ]);
        MarketingEmailOpen::create([
            'marketing_email_id' => $email->id,
            'email' => 'opened@example.com',
            'tracking_id' => 'campaign-open-1',
            'opened_at' => now(),
            'last_opened_at' => now(),
            'open_count' => 1,
        ]);

        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'campaigns-detail', 'campaign_id' => $campaign->id]))
            ->assertOk()
            ->assertSee('All delivery records')
            ->assertSee('opened@example.com')
            ->assertSee('50.0%')
            ->assertViewHas('campaignStats', fn ($stats) => $stats['opened'] === 1
                && $stats['sent'] === 2
                && $stats['remaining'] === 1
                && $stats['open_rate'] === 50.0);
    }

    public function test_failed_campaign_email_can_be_retried_from_campaign_details(): void
    {
        Queue::fake();
        $campaign = MarketingCampaign::create([
            'name' => 'Retry outreach',
            'status' => 'completed',
            'audience_type' => 'manual',
            'manual_recipients' => ['failed@example.com'],
            'recipient_count' => 1,
            'failed_count' => 1,
        ]);
        $step = MarketingCampaignStep::create([
            'marketing_campaign_id' => $campaign->id,
            'step_order' => 1,
            'subject' => 'Hello',
            'body' => 'Body',
        ]);
        $email = MarketingEmail::create([
            'marketing_campaign_id' => $campaign->id,
            'marketing_campaign_step_id' => $step->id,
            'recipients' => ['failed@example.com'],
            'recipient_count' => 1,
            'subject' => 'Hello',
            'body' => 'Body',
            'delivery_status' => 'failed',
            'failed_count' => 1,
            'delivery_error' => 'failed@example.com: SMTP connection refused',
        ]);

        $this->withSession(['marketing_authenticated' => true])
            ->post(route('marketing.campaigns.email.retry', $email->id))
            ->assertRedirect(route('marketing', ['tab' => 'campaigns-detail', 'campaign_id' => $campaign->id]))
            ->assertSessionHas('marketing_success', 'Failed email queued for retry.');

        $email->refresh();
        $campaign->refresh();
        $this->assertSame('pending', $email->delivery_status);
        $this->assertSame('running', $campaign->status);
        $this->assertSame(1, $campaign->pending_emails);
        Queue::assertPushed(SendMarketingEmailJob::class, 1);
    }
}
