<?php

namespace Tests\Feature;

use App\Models\MarketingEmailGroupContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MarketingEmailGroupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_groups_requires_marketing_login(): void
    {
        $this->get(route('marketing', ['tab' => 'email-groups']))
            ->assertRedirect(route('marketing.login'));
        $this->post(route('marketing.email-groups.store'), [
            'name' => 'Alice', 'email' => 'alice@example.com', 'source' => 'new_lead',
        ])->assertRedirect(route('marketing.login'));
        $this->assertDatabaseCount('marketing_email_group_contacts', 0);
    }

    public function test_page_has_clients_submenus_and_the_contact_form(): void
    {
        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'email-groups']))
            ->assertOk()
            ->assertSeeInOrder(['Clients', 'Active Clients', 'My Contacts', 'Email groups'])
            ->assertSee('Name')->assertSee('Email')->assertSee('Source')
            ->assertSee('Old client')->assertSee('New lead')
            ->assertSee('No contacts saved yet.')
            ->assertViewHas('emailGroupsReady', true);
    }

    public function test_contact_is_saved_and_listed_after_submission(): void
    {
        $this->withSession(['marketing_authenticated' => true])
            ->post(route('marketing.email-groups.store'), [
                'name' => ' Alice Example ', 'email' => ' ALICE@EXAMPLE.COM ', 'source' => 'old_client',
            ])->assertRedirect(route('marketing', ['tab' => 'email-groups']))
            ->assertSessionHas('marketing_success');

        $this->assertDatabaseHas('marketing_email_group_contacts', [
            'name' => 'Alice Example', 'email' => 'alice@example.com', 'source' => 'old_client',
        ]);
        $this->get(route('marketing', ['tab' => 'email-groups']))
            ->assertOk()->assertSee('Alice Example')->assertSee('alice@example.com');
    }

    public function test_invalid_contacts_are_rejected_and_inputs_are_preserved(): void
    {
        $page = route('marketing', ['tab' => 'email-groups']);
        $this->withSession(['marketing_authenticated' => true])->from($page)
            ->post(route('marketing.email-groups.store'), [
                'name' => '', 'email' => 'not-an-email', 'source' => 'unknown',
            ])->assertRedirect($page)->assertSessionHasErrors(['name', 'email', 'source'])
            ->assertSessionHasInput('email', 'not-an-email');
        $this->assertDatabaseCount('marketing_email_group_contacts', 0);
    }

    public function test_duplicate_email_is_rejected_regardless_of_case(): void
    {
        $this->createContact('Alice', 'alice@example.com', 'old_client');
        $this->withSession(['marketing_authenticated' => true])
            ->from(route('marketing', ['tab' => 'email-groups']))
            ->post(route('marketing.email-groups.store'), [
                'name' => 'Another Alice', 'email' => 'ALICE@EXAMPLE.COM', 'source' => 'new_lead',
            ])->assertSessionHasErrors('email');
        $this->assertDatabaseCount('marketing_email_group_contacts', 1);
    }

    public function test_search_and_source_filter_work_together(): void
    {
        $this->createContact('Alice', 'alice@example.com', 'old_client');
        $this->createContact('Alice Lead', 'lead@example.com', 'new_lead');
        $this->createContact('Bob', 'bob@example.com', 'old_client');

        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'email-groups', 'group_search' => 'Alice', 'group_source' => 'new_lead']))
            ->assertOk()->assertSee('lead@example.com')->assertDontSee('alice@example.com')->assertDontSee('bob@example.com')
            ->assertViewHas('emailGroupContacts', fn ($contacts) => $contacts->total() === 1);

        $this->get(route('marketing', ['tab' => 'email-groups', 'group_search' => 'bob@example']))
            ->assertOk()->assertSee('bob@example.com')->assertDontSee('lead@example.com');
    }

    public function test_contact_list_sorts_by_name_and_date(): void
    {
        $bob = $this->createContact('Bob', 'bob@example.com', 'old_client');
        $alice = $this->createContact('Alice', 'alice@example.com', 'new_lead');
        $bob->forceFill(['created_at' => now()->subDays(2)])->save();
        $alice->forceFill(['created_at' => now()->subDay()])->save();
        $this->withSession(['marketing_authenticated' => true]);

        foreach (['name-asc' => [$alice->id, $bob->id], 'email-desc' => [$bob->id, $alice->id], 'newest' => [$alice->id, $bob->id], 'oldest' => [$bob->id, $alice->id]] as $sort => $ids) {
            $this->get(route('marketing', ['tab' => 'email-groups', 'group_sort' => $sort]))
                ->assertOk()->assertViewHas('emailGroupContacts', fn ($contacts) => $contacts->pluck('id')->all() === $ids);
        }
    }

    public function test_filtered_pagination_preserves_filters_and_sort_order(): void
    {
        for ($index = 0; $index < 27; $index++) {
            $this->createContact('Client '.sprintf('%02d', $index), "client{$index}@example.com", 'old_client');
        }
        $this->createContact('Lead', 'lead@example.com', 'new_lead');
        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'email-groups', 'group_source' => 'old_client', 'group_sort' => 'name-asc', 'page' => 2]))
            ->assertOk()
            ->assertViewHas('emailGroupContacts', fn ($contacts) => $contacts->count() === 2
                && $contacts->total() === 27
                && str_contains($contacts->url(1), 'group_source=old_client')
                && str_contains($contacts->url(1), 'group_sort=name-asc'));
    }

    public function test_missing_email_groups_migration_does_not_break_the_dashboard_or_sending_page(): void
    {
        Schema::drop('marketing_email_group_contacts');
        $this->withSession(['marketing_authenticated' => true]);

        foreach (['dashboard', 'send'] as $tab) {
            $this->get(route('marketing', ['tab' => $tab]))->assertOk()->assertViewHas('databaseReady', true);
        }
        $this->get(route('marketing', ['tab' => 'email-groups']))
            ->assertOk()->assertViewHas('emailGroupsReady', false)->assertSee('Email groups storage is not ready yet.');
        $this->post(route('marketing.email-groups.store'), [
            'name' => 'Alice', 'email' => 'alice@example.com', 'source' => 'new_lead',
        ])->assertRedirect(route('marketing', ['tab' => 'email-groups']))->assertSessionHas('marketing_error');
    }

    private function createContact(string $name, string $email, string $source): MarketingEmailGroupContact
    {
        return MarketingEmailGroupContact::create(compact('name', 'email', 'source'));
    }
}
