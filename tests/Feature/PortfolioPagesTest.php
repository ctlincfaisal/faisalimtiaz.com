<?php

namespace Tests\Feature;

use Tests\TestCase;

class PortfolioPagesTest extends TestCase
{
    public function test_all_service_pages_have_conversion_sections_and_working_contact_ctas(): void
    {
        $serviceRoutes = [
            'services.mobile-app-development',
            'services.react-native-development',
            'services.website-development',
            'services.laravel-development',
            'services.web-application-development',
            'services.seo-services',
        ];

        foreach ($serviceRoutes as $routeName) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee('Get a Free Project Estimate')
                ->assertSee('Discuss Your Project')
                ->assertSee('FAQ')
                ->assertSee('Process')
                ->assertSee(route('aboutme').'?', false)
                ->assertSee('#contact', false);
        }
    }

    public function test_all_case_studies_have_story_features_and_inquiry_paths(): void
    {
        foreach (config('projects') as $project) {
            $response = $this->get(route('studio.work', $project['slug']));

            $response->assertOk()
                ->assertSee('Project introduction')
                ->assertSee('Major features')
                ->assertSee('Enquire About Similar Project')
                ->assertSee('Want to Build Something Similar?')
                ->assertSee(route('aboutme', ['project' => $project['name']]).'#contact', false);

            if (! empty($project['tech'])) {
                $response->assertSee('Technical implementation');
            }
        }
    }

    public function test_phototrail_case_study_uses_verified_app_screens_and_role_context(): void
    {
        $this->get(route('studio.work', 'phototrail'))
            ->assertOk()
            ->assertSee('CEO and Founder at Phototrail')
            ->assertSee('PhotoTrail home screen showing shared events and trips')
            ->assertSee('PhotoTrail event album screen with upload and sharing actions');
    }
}
