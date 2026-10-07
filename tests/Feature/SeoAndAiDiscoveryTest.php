<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAndAiDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \App\Models\PublicSite\PublicPage::create([
            'slug' => 'home',
            'url_path' => '/',
            'title' => 'PT Misuba Guna Indonesia',
            'is_published' => true,
            'sitemap_include' => true,
            'sitemap_priority' => '1.0',
            'sitemap_changefreq' => 'weekly',
        ]);
    }

    public function test_robots_txt_allows_all_major_ai_crawlers_and_links_sitemap(): void
    {
        $response = $this->get('/robots.txt');
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('User-agent: GPTBot', $content);
        $this->assertStringContainsString('User-agent: ClaudeBot', $content);
        $this->assertStringContainsString('User-agent: PerplexityBot', $content);
        $this->assertStringContainsString('User-agent: Google-Extended', $content);
        $this->assertStringContainsString('Sitemap: https://misubaguna.com/sitemap.xml', $content);
    }

    public function test_sitemap_xml_contains_all_core_routes_and_toolkit(): void
    {
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('<loc>https://misubaguna.com/</loc>', $content);
        $this->assertStringContainsString('<loc>https://misubaguna.com/toolkit/</loc>', $content);
        $this->assertStringContainsString('<loc>https://misubaguna.com/toolkit/flange-standards/</loc>', $content);
        $this->assertStringContainsString('<loc>https://misubaguna.com/toolkit/calculator/</loc>', $content);
        $this->assertStringContainsString('<loc>https://misubaguna.com/toolkit/material-specs/</loc>', $content);
        $this->assertStringContainsString('</urlset>', $content);
    }

    public function test_llms_txt_and_full_are_accessible_and_structured(): void
    {
        $respSmall = $this->get('/llms.txt');
        $respSmall->assertStatus(200);
        $this->assertStringContainsString('# PT Misuba Guna Indonesia', $respSmall->getContent());
        $this->assertStringContainsString('https://misubaguna.com/', $respSmall->getContent());

        $respFull = $this->get('/llms-full.txt');
        $respFull->assertStatus(200);
        $this->assertStringContainsString('# PT Misuba Guna Indonesia', $respFull->getContent());
        $this->assertStringContainsString('Detailed Technical Catalog & Service Directory', $respFull->getContent());
    }

    public function test_toolkit_pages_have_meta_titles_descriptions_and_schema(): void
    {
        $toolkitUrls = [
            '/toolkit/',
            '/toolkit/flange-standards/',
            '/toolkit/calculator/',
            '/toolkit/material-specs/',
        ];

        foreach ($toolkitUrls as $url) {
            $resp = $this->get($url);
            $resp->assertStatus(200);
            $content = $resp->getContent();

            $this->assertMatchesRegularExpression('/<title>[^<]+PT Misuba Guna Indonesia<\/title>/', $content);
            $this->assertMatchesRegularExpression('/<meta\s+name=["\']description["\']\s+content=["\'][^"\']+["\']/', $content);
            $this->assertStringContainsString('application/ld+json', $content);
            $this->assertStringContainsString('https://misubaguna.com' . $url, $content);
        }
    }
}
