<?php

namespace Tests\Feature;

use App\Models\MarketingLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MarketingLeadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_leads_page_is_available_inside_clients_menu(): void
    {
        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'leads']))
            ->assertOk()
            ->assertSeeInOrder(['Clients', 'Leads', 'Active Clients'])
            ->assertSee('All Leads')
            ->assertDontSee('Import CSV')
            ->assertDontSee('Generate more leads')
            ->assertViewHas('leadsReady', true);
    }

    public function test_generate_leads_page_exposes_import_and_search_sources(): void
    {
        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'leads-generate', 'lead_source_tab' => 'import']))
            ->assertOk()
            ->assertSee('Generate Leads')
            ->assertSeeInOrder(['Import Leads', 'Search Leads'])
            ->assertSee('Upload a lead list');

        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'leads-generate', 'lead_source_tab' => 'search']))
            ->assertOk()
            ->assertSee('Search Leads')
            ->assertSee('Coming soon');
    }

    public function test_segments_can_be_created(): void
    {
        $this->withSession(['marketing_authenticated' => true])
            ->post(route('marketing.leads.segments.store'), ['name' => 'Photographers'])
            ->assertRedirect(route('marketing', ['tab' => 'leads']))
            ->assertSessionHas('marketing_success');

        $this->assertDatabaseHas('marketing_lead_segments', [
            'name' => 'Photographers',
            'color' => 'violet',
        ]);
    }

    public function test_sample_csv_download_contains_all_import_columns(): void
    {
        $response = $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing.leads.sample'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertHeader('content-disposition', 'attachment; filename=lead-import-template.csv');

        $this->assertStringContainsString('"First name","Last name",Email,Company,Status', $response->streamedContent());
    }

    public function test_csv_can_be_previewed_mapped_and_imported(): void
    {
        $this->withSession(['marketing_authenticated' => true]);
        $this->post(route('marketing.leads.segments.store'), ['name' => 'Photographers']);

        $csv = UploadedFile::fake()->createWithContent('leads.csv', implode("\n", [
            'First name,Last name,Email,Company,Status',
            'Emma,Sinclair,emma@example.com,Evermore Weddings,replied',
            'James,Rooke,james@example.com,Rooke Photography,contacted',
        ]));

        $this->post(route('marketing.leads.import.preview'), ['file' => $csv])
            ->assertRedirect(route('marketing', ['tab' => 'leads']))
            ->assertSessionHas('marketing_leads_import_preview');

        $this->post(route('marketing.leads.import'), [
            'first_name_column' => 'First name',
            'last_name_column' => 'Last name',
            'email_column' => 'Email',
            'company_column' => 'Company',
            'status_column' => 'Status',
            'segment' => 'Photographers',
        ])->assertRedirect(route('marketing', ['tab' => 'leads']))
            ->assertSessionHas('marketing_success');

        $this->assertDatabaseHas('marketing_leads', [
            'first_name' => 'Emma',
            'last_name' => 'Sinclair',
            'email' => 'emma@example.com',
            'company' => 'Evermore Weddings',
            'status' => 'replied',
            'segment' => 'Photographers',
        ]);
        $this->assertDatabaseHas('marketing_leads', [
            'email' => 'james@example.com',
            'status' => 'contacted',
        ]);
    }

    public function test_csv_import_can_create_a_category_from_the_mapping_form(): void
    {
        $this->withSession(['marketing_authenticated' => true]);
        $csv = UploadedFile::fake()->createWithContent('leads.csv', implode("\n", [
            'First name,Email',
            'Emma,emma@example.com',
        ]));

        $this->post(route('marketing.leads.import.preview'), ['file' => $csv])
            ->assertRedirect(route('marketing', ['tab' => 'leads']))
            ->assertSessionHas('marketing_leads_import_preview');

        $response = $this->post(route('marketing.leads.import'), [
            'first_name_column' => 'First name',
            'last_name_column' => '',
            'email_column' => 'Email',
            'company_column' => '',
            'status_column' => '',
            'segment' => '__create__',
            'new_category' => 'Wedding photographers',
        ])->assertRedirect(route('marketing', ['tab' => 'leads']))
            ->assertSessionHas('marketing_success');

        $this->assertDatabaseHas('marketing_lead_segments', [
            'name' => 'Wedding photographers',
        ]);
        $this->assertDatabaseHas('marketing_leads', [
            'email' => 'emma@example.com',
            'segment' => 'Wedding photographers',
        ]);
    }

    public function test_leads_can_be_searched_and_filtered(): void
    {
        MarketingLead::create(['first_name' => 'Emma', 'email' => 'emma@example.com', 'company' => 'Evermore Weddings', 'status' => 'replied', 'segment' => 'Photographers']);
        MarketingLead::create(['first_name' => 'James', 'email' => 'james@example.com', 'company' => 'Rooke Photography', 'status' => 'new']);

        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'leads', 'lead_search' => 'Evermore', 'lead_status' => 'replied']))
            ->assertOk()
            ->assertSee('emma@example.com')
            ->assertDontSee('james@example.com')
            ->assertViewHas('marketingLeads', fn ($leads) => $leads->total() === 1);
    }

    public function test_active_segment_is_the_only_highlighted_segment(): void
    {
        $this->withSession(['marketing_authenticated' => true])
            ->post(route('marketing.leads.segments.store'), ['name' => 'Photographers']);

        $response = $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'leads', 'lead_segment' => 'Photographers']))
            ->assertOk()
            ->assertViewHas('leadSegments', fn ($segments) => $segments->pluck('name')->contains('Photographers'));

        $this->assertSame(1, preg_match('/data-lead-segment="Photographers"[^>]*aria-current="page"/', $response->getContent()));
        $this->assertSame(0, preg_match('/data-lead-segment="all"[^>]*aria-current="page"/', $response->getContent()));
    }
}
