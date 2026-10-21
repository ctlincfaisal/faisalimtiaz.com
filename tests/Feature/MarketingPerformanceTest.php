<?php

namespace Tests\Feature;

use App\Models\WebsiteClick;
use App\Models\WebsiteVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_performance_requires_marketing_login(): void
    {
        $this->get(route('marketing', ['tab' => 'performance']))
            ->assertRedirect(route('marketing.login'));
    }

    public function test_analytics_menu_keeps_the_existing_website_analytics_page(): void
    {
        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'analytics']))
            ->assertOk()->assertViewHas('activeTab', 'analytics')
            ->assertSeeInOrder(['Analytics', 'Website Analytics', 'Performance'])
            ->assertSee('Recent visitors');
    }

    public function test_history_includes_all_stored_visits_and_counts_sessions_and_clicks(): void
    {
        $first = $this->createVisit('2026-01-01 08:00:00', 'returning-session');
        $second = $this->createVisit('2026-10-20 10:00:00', 'returning-session');
        $third = $this->createVisit('2026-10-21 12:00:00', 'other-session', [
            'city' => 'Mountain View', 'region' => 'California', 'country' => 'United States',
        ]);
        WebsiteClick::create([
            'website_visit_id' => $second->id,
            'session_id' => $second->session_id,
            'url' => $second->url,
            'path' => $second->path,
            'element' => 'a',
            'clicked_at' => '2026-10-20 10:01:00',
        ]);

        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'performance']))
            ->assertOk()->assertViewHas('performanceReady', true)
            ->assertViewHas('performanceTotalVisits', 3)
            ->assertViewHas('performanceVisitorSessions', 2)
            ->assertViewHas('performanceVisits', fn ($visits) => $visits->pluck('id')->all() === [$third->id, $second->id, $first->id]
                && $visits->firstWhere('id', $second->id)->clicks_count === 1)
            ->assertSee('Unknown location')->assertSee('Mountain View');
    }

    public function test_date_filter_includes_both_entire_selected_days(): void
    {
        $before = $this->createVisit('2026-10-19 23:59:59', 'before');
        $start = $this->createVisit('2026-10-20 00:00:00', 'start');
        $end = $this->createVisit('2026-10-21 23:59:59', 'end');
        $after = $this->createVisit('2026-10-22 00:00:00', 'after');

        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'performance', 'performance_from' => '2026-10-20', 'performance_to' => '2026-10-21']))
            ->assertOk()->assertViewHas('performanceTotalVisits', 2)
            ->assertViewHas('performanceVisitorSessions', 2)
            ->assertViewHas('performanceVisits', fn ($visits) => $visits->pluck('id')->all() === [$end->id, $start->id])
            ->assertDontSee($before->path)->assertDontSee($after->path);
    }

    public function test_single_date_bounds_and_same_day_ranges_are_supported(): void
    {
        $old = $this->createVisit('2026-10-19 23:59:59', 'old');
        $today = $this->createVisit('2026-10-20 12:00:00', 'today');
        $new = $this->createVisit('2026-10-21 00:00:00', 'new');
        $this->withSession(['marketing_authenticated' => true]);

        foreach ([
            [['performance_from' => '2026-10-20'], [$new->id, $today->id]],
            [['performance_to' => '2026-10-20'], [$today->id, $old->id]],
            [['performance_from' => '2026-10-20', 'performance_to' => '2026-10-20'], [$today->id]],
        ] as [$filters, $ids]) {
            $this->get(route('marketing', ['tab' => 'performance', ...$filters]))
                ->assertOk()->assertViewHas('performanceVisits', fn ($visits) => $visits->pluck('id')->all() === $ids);
        }
    }

    public function test_invalid_dates_and_reversed_ranges_show_an_error_without_redirecting(): void
    {
        $this->withSession(['marketing_authenticated' => true]);
        foreach ([
            ['performance_from' => 'not-a-date'],
            ['performance_from' => '2026-02-30'],
            ['performance_to' => '2026-02-30'],
            ['performance_from' => '2026-10-21', 'performance_to' => '2026-10-20'],
        ] as $filters) {
            $this->get(route('marketing', ['tab' => 'performance', ...$filters]))
                ->assertOk()->assertViewHas('performanceDateErrors', fn ($errors) => count($errors) > 0)
                ->assertDontSee('Visitor history</h2>', false);
        }
    }

    public function test_all_history_is_paginated_with_filters_preserved(): void
    {
        for ($index = 0; $index < 55; $index++) {
            $this->createVisit('2026-10-20 12:00:00', "session-{$index}");
        }
        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'performance', 'performance_from' => '2026-10-20', 'performance_to' => '2026-10-20', 'page' => 2]))
            ->assertOk()->assertViewHas('performanceTotalVisits', 55)
            ->assertViewHas('performanceVisits', fn ($visits) => $visits->count() === 5
                && $visits->total() === 55
                && str_contains($visits->url(1), 'performance_from=2026-10-20')
                && str_contains($visits->url(1), 'performance_to=2026-10-20'));
    }

    public function test_an_empty_date_range_shows_an_empty_state(): void
    {
        $this->createVisit('2026-01-01 12:00:00', 'old');
        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'performance', 'performance_from' => '2026-10-20']))
            ->assertOk()->assertSee('No visits recorded in this date range.')
            ->assertViewHas('performanceTotalVisits', 0)->assertViewHas('performanceVisitorSessions', 0);
    }

    private function createVisit(string $date, string $session, array $attributes = []): WebsiteVisit
    {
        return WebsiteVisit::create(array_merge([
            'session_id' => $session,
            'ip_address' => '192.0.2.1',
            'url' => 'https://example.com/'.$session,
            'path' => '/'.$session,
            'visited_at' => $date,
        ], $attributes));
    }
}
