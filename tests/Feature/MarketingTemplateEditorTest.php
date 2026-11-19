<?php

namespace Tests\Feature;

use App\Models\MarketingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketingTemplateEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_template_renders_the_rich_text_editor(): void
    {
        $this->withSession(['marketing_authenticated' => true])
            ->get(route('marketing', ['tab' => 'templates-create']))
            ->assertOk()
            ->assertSee('data-rich-text-editor', false)
            ->assertSee('data-editor-action="bold"', false)
            ->assertSee('data-editor-action="bullet"', false)
            ->assertSee('data-editor-action="image"', false)
            ->assertSee('data-editor-tab="preview"', false)
            ->assertSee('data-editor-tab="code"', false)
            ->assertSee('data-editor-code-surface', false)
            ->assertSee('data-editor-image-width', false)
            ->assertSee('data-editor-link-input', false);
    }

    public function test_rich_template_html_is_sanitized_and_marked_as_html(): void
    {
        Storage::fake('local');

        $this->withSession(['marketing_authenticated' => true])
            ->post(route('marketing.templates.store'), [
                'name' => 'Formatted template',
                'subjects' => ['Hello there'],
                'content' => '<p><strong>Hello</strong></p><script>alert(1)</script><a href="javascript:alert(2)">Click</a>',
            ])->assertRedirect(route('marketing', ['tab' => 'templates-list']));

        $template = MarketingTemplate::firstOrFail();
        $this->assertSame('html', $template->content_format);
        $this->assertStringContainsString('<strong>Hello</strong>', $template->content);
        $this->assertStringNotContainsString('<script', $template->content);
        $this->assertStringNotContainsString('javascript:', $template->content);
    }

    public function test_rich_template_html_preserves_safe_images(): void
    {
        Storage::fake('local');

        $this->withSession(['marketing_authenticated' => true])
            ->post(route('marketing.templates.store'), [
                'name' => 'Image template',
                'subjects' => ['Image email'],
                'content' => '<p>Here is the image</p><img src="data:image/png;base64,AAAA" alt="Banner"><img src="data:text/html;base64,AAAA">',
            ])->assertRedirect(route('marketing', ['tab' => 'templates-list']));

        $template = MarketingTemplate::firstOrFail();
        $this->assertStringContainsString('data:image/png;base64,AAAA', $template->content);
        $this->assertStringContainsString('alt="Banner"', $template->content);
        $this->assertStringNotContainsString('data:text/html', $template->content);
    }
}
