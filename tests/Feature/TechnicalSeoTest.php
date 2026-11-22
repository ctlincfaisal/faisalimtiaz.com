<?php

namespace Tests\Feature;

use Tests\TestCase;

class TechnicalSeoTest extends TestCase
{
    public function test_public_studio_pages_expose_canonical_robots_and_social_metadata(): void
    {
        $publicRoutes = [
            'studio',
            'aboutme',
            'blog',
            'studio.testimonials',
            'services.mobile-app-development',
            'services.react-native-development',
            'services.website-development',
            'services.laravel-development',
            'services.web-application-development',
            'services.seo-services',
        ];

        foreach ($publicRoutes as $routeName) {
            $response = $this->get(route($routeName));

            $response->assertOk()
                ->assertSee('<meta name="robots" content="index,follow">', false)
                ->assertSee('<link rel="canonical" href="https://faisalimtiaz.com', false)
                ->assertSee('<meta property="og:title"', false)
                ->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
        }
    }

    public function test_service_pages_use_service_schema_without_commercial_faq_schema(): void
    {
        foreach ([
            'services.mobile-app-development',
            'services.react-native-development',
            'services.website-development',
            'services.laravel-development',
            'services.web-application-development',
            'services.seo-services',
        ] as $routeName) {
            $html = $this->get(route($routeName))->assertOk()->getContent();

            $this->assertStringContainsString('"@type": "Service"', $html);
            $this->assertStringContainsString('"@type": "BreadcrumbList"', $html);
            $this->assertStringNotContainsString('"@type": "FAQPage"', $html);
        }
    }

    public function test_public_json_ld_blocks_are_valid_json(): void
    {
        foreach ([
            route('studio'),
            route('aboutme'),
            route('blog'),
            route('studio.testimonials'),
            route('services.react-native-development'),
            route('studio.work', 'phototrail'),
            route('blog.post', 'react-native-vs-flutter-startup-mvp'),
        ] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            preg_match_all('/<script type="application\\/ld\\+json">(.*?)<\\/script>/s', $html, $blocks);

            $this->assertNotEmpty($blocks[1], "No JSON-LD block found for {$url}");
            foreach ($blocks[1] as $block) {
                json_decode($block, true);
                $this->assertSame(JSON_ERROR_NONE, json_last_error(), "Invalid JSON-LD found for {$url}");
            }
        }
    }

    public function test_sitemap_and_robots_cover_public_pages_without_marketing_urls(): void
    {
        $sitemap = simplexml_load_file(public_path('sitemap.xml'));
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertNotFalse($sitemap);
        $locations = [];
        foreach ($sitemap->url as $url) {
            $locations[] = (string) $url->loc;
        }
        $this->assertContains('https://faisalimtiaz.com/services/react-native-development', $locations);
        $this->assertContains('https://faisalimtiaz.com/work/phototrail', $locations);
        $this->assertTrue(collect($locations)->every(fn (string $url) => ! str_contains($url, '/marketing')));
        $this->assertStringContainsString('Sitemap: https://faisalimtiaz.com/sitemap.xml', $robots);
        $this->assertStringContainsString('Disallow: /marketing/', $robots);
    }

    public function test_every_sitemap_url_renders_successfully(): void
    {
        $sitemap = simplexml_load_file(public_path('sitemap.xml'));

        $this->assertNotFalse($sitemap);
        foreach ($sitemap->url as $url) {
            $path = parse_url((string) $url->loc, PHP_URL_PATH) ?: '/';

            $this->get($path)->assertOk();
        }
    }

    public function test_priority_service_intents_have_distinct_page_assignments(): void
    {
        $this->get(route('services.react-native-development'))
            ->assertSee('Hire a React Native developer for your next app.');

        $this->get(route('services.mobile-app-development'))
            ->assertSee('Freelance mobile app developer for Android and iOS products.')
            ->assertSee('MVP app development for startups')
            ->assertSee('Existing mobile app maintenance and upgrades');

        $this->get(route('services.web-application-development'))
            ->assertSee('Custom web applications and MVPs built around how your business works.')
            ->assertSee('MVP app development for startups');

        $this->get(route('services.laravel-development'))
            ->assertSee('Hire a Laravel backend and API developer for your application.');
    }

    public function test_secondary_public_pages_have_accessible_mobile_navigation(): void
    {
        $urls = [
            route('aboutme'),
            route('blog'),
            route('blog.post', 'react-native-vs-flutter-startup-mvp'),
            route('studio.testimonials'),
            route('services.react-native-development'),
        ];

        foreach ($urls as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('data-site-nav-toggle', false)
                ->assertSee('aria-controls="site-mobile-nav"', false)
                ->assertSee('id="site-mobile-nav"', false);
        }
    }

    public function test_about_page_uses_the_current_portrait_asset(): void
    {
        $this->get(route('aboutme'))
            ->assertOk()
            ->assertSee('assets/faisalimtiaz/faisalimtiaz-about.jpeg', false)
            ->assertSee('alt="Portrait of Faisal Imtiaz"', false);

        $this->get(route('studio'))
            ->assertOk()
            ->assertSee('assets/faisalimtiaz/faisalimtiaz-about.jpeg', false);
    }
}
