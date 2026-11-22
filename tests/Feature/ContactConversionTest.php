<?php

namespace Tests\Feature;

use App\Mail\ContactInquiryMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_has_conversion_focused_positioning_services_process_and_ctas(): void
    {
        $response = $this->get(route('studio'));

        $response->assertOk()
            ->assertSee('I Build Android, iOS &amp; Web Apps That Turn Your Ideas Into Real Products.', false)
            ->assertSee('Get a Free Project Estimate')
            ->assertSee('View My Work')
            ->assertSee('Android &amp; iOS App Development', false)
            ->assertSee('Laravel Backend &amp; API Development', false)
            ->assertSee('Discuss Your Project')
            ->assertSee('Discuss Your Idea')
            ->assertSee('Have an App or Website Idea?')
            ->assertSee('Build It')
            ->assertSee('Share the product, problem, or next phase.')
            ->assertSee('Whether you&rsquo;re launching a new product', false)
            ->assertSee('name="project_type"', false)
            ->assertSee('name="budget"', false)
            ->assertSee('Approximate budget')
            ->assertSee('https://wa.me/923006770770?text=Hi%20Faisal%2C%20I%20would%20like%20to%20discuss%20a%20project.', false)
            ->assertSee('+92 300 6770770')
            ->assertDontSee('+92 304 6770770')
            ->assertSee('ctlinc.faisal@gmail.com');

        $this->get(route('aboutme'))
            ->assertOk()
            ->assertSee('https://wa.me/923006770770?text=Hi%20Faisal%2C%20I%20would%20like%20to%20discuss%20a%20project.', false);
    }

    public function test_project_inquiry_ctas_use_the_contact_form_and_keep_demo_links_separate(): void
    {
        foreach (config('projects') as $project) {
            $response = $this->get(route('studio.work', $project['slug']));
            $inquiryHref = route('aboutme', ['project' => $project['name']]).'#contact';

            $response->assertOk()
                ->assertSee($inquiryHref, false);

            if (! empty($project['live'])) {
                $response->assertSee($project['live'], false);
            }

            foreach ($project['stores'] ?? [] as $store) {
                $response->assertSee($store['url'], false);
            }
        }
    }

    public function test_valid_contact_submission_is_stored_and_emailed_to_the_business_inbox(): void
    {
        Mail::fake();

        $response = $this->postJson(route('contactus'), [
            'firstname' => 'Alex Example',
            'email' => 'alex@example.com',
            'project_type' => 'Mobile app development',
            'budget' => '$5,000–$15,000',
            'details' => 'I need a mobile app for an international launch.',
        ]);

        $response->assertOk()->assertJson(['msg' => 'success']);
        $this->assertDatabaseHas('contacts', [
            'firstname' => 'Alex',
            'lastname' => 'Example',
            'email' => 'alex@example.com',
            'project_type' => 'Mobile app development',
            'budget_range' => '$5,000–$15,000',
            'budget' => 'Mobile app development',
            'details' => 'I need a mobile app for an international launch.',
        ]);
        Mail::assertSent(ContactInquiryMail::class, function (ContactInquiryMail $mail): bool {
            return $mail->inquiry['email'] === 'alex@example.com'
                && $mail->inquiry['name'] === 'Alex Example'
                && $mail->inquiry['project_type'] === 'Mobile app development'
                && $mail->inquiry['budget'] === '$5,000–$15,000'
                && $mail->hasTo('ctlinc.faisal@gmail.com');
        });
    }

    public function test_invalid_contact_submission_returns_field_errors_without_sending_mail(): void
    {
        Mail::fake();

        $response = $this->postJson(route('contactus'), [
            'firstname' => '',
            'email' => 'not-an-email',
            'details' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['firstname', 'email', 'details']);
        Mail::assertNothingSent();
    }

    public function test_contact_submission_returns_a_clear_error_when_mail_delivery_fails(): void
    {
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('SMTP unavailable'));

        $response = $this->postJson(route('contactus'), [
            'firstname' => 'Alex Example',
            'email' => 'alex@example.com',
            'details' => 'Please review an existing app.',
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'msg' => 'error',
                'message' => 'Unable to save contact submission right now.',
            ]);
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_honeypot_submission_is_accepted_without_storage_or_email(): void
    {
        Mail::fake();

        $response = $this->postJson(route('contactus'), [
            'firstname' => 'Bot',
            'email' => 'bot@example.com',
            'details' => 'Automated submission',
            'website' => 'https://spam.example',
        ]);

        $response->assertOk()->assertJson(['msg' => 'success']);
        $this->assertDatabaseCount('contacts', 0);
        Mail::assertNothingSent();
    }
}
