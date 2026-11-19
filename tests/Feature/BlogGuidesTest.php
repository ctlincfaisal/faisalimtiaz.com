<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BlogGuidesTest extends TestCase
{
    public static function guideSlugs(): array
    {
        return [
            ['laravel-vs-wordpress-client-portal'],
            ['react-native-vs-flutter-startup-mvp'],
        ];
    }

    public function test_new_guides_are_discoverable_from_the_blog_listing(): void
    {
        $response = $this->get(route('blog'))->assertOk();
        foreach (config('blog.posts') as $slug => $post) {
            $response->assertSee($post['title'])->assertSee(route('blog.post', $slug));
            $this->assertFileExists(public_path($post['cover_image']));
        }
    }

    #[DataProvider('guideSlugs')]
    public function test_guides_render_content_canonical_author_date_and_article_schema(string $slug): void
    {
        $post = config('blog.posts')[$slug];
        $response = $this->get(route('blog.post', $slug).'?ref=preview')
            ->assertOk()->assertSee($post['h1'])->assertSee($post['meta_description'])
            ->assertSee('In this article')->assertSee('Faisal Imtiaz')->assertSee('October 21, 2026')
            ->assertSee($post['sections'][0]['paragraphs'][0]);

        $dom = new \DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $this->assertSame(1, $xpath->query('//h1')->length);
        $this->assertSame('https://faisalimtiaz.com/blog/'.$slug, $xpath->query('//link[@rel="canonical"]')->item(0)->getAttribute('href'));
        $this->assertSame('https://faisalimtiaz.com/'.$post['cover_image'], $xpath->query('//meta[@property="og:image"]')->item(0)->getAttribute('content'));
        $this->assertGreaterThan(0, $xpath->query('//table')->length);
        $graph = [];
        foreach ($xpath->query('//script[@type="application/ld+json"]') as $script) {
            $data = json_decode($script->textContent, true, flags: JSON_THROW_ON_ERROR);
            $graph = array_merge($graph, $data['@graph'] ?? []);
        }
        $article = collect($graph)->firstWhere('@type', 'BlogPosting');
        $this->assertNotNull($article);
        $this->assertSame($post['h1'], $article['headline']);
        $this->assertSame('Faisal Imtiaz', $article['author']['name']);
        $this->assertSame($post['published_at'], $article['datePublished']);
        $this->assertFalse(collect($graph)->contains('@type', 'FAQPage'));
    }

    #[DataProvider('guideSlugs')]
    public function test_guide_links_resolve_and_sitemap_contains_the_article(string $slug): void
    {
        $post = config('blog.posts')[$slug];
        foreach ($post['sections'] as $section) {
            foreach ($section['links'] ?? [] as $link) {
                if (str_starts_with($link['href'], '/')) {
                    $path = parse_url($link['href'], PHP_URL_PATH);
                    $route = app('router')->getRoutes()->match(Request::create($path));
                    $this->assertNotNull($route);
                    if ($route->getName() === 'blog.post') {
                        $this->get($path)->assertOk();
                    }
                }
            }
        }
        $sitemap = simplexml_load_file(public_path('sitemap.xml'));
        $urls = array_map('strval', $sitemap->xpath('//*[local-name()="loc"]'));
        $this->assertContains('https://faisalimtiaz.com/blog/'.$slug, $urls);
    }
}
