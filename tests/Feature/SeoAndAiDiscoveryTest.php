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

            $this->assertMatchesRegularExpression('/<title>[^<]+\| (PT )?Misuba Guna( Indonesia)?<\/title>/', $content);
            preg_match('/<title>([^<]+)<\/title>/', $content, $m);
            $this->assertLessThanOrEqual(60, mb_strlen(html_entity_decode($m[1])), $url);
            $this->assertMatchesRegularExpression('/<meta\s+name=["\']description["\']\s+content=["\'][^"\']+["\']/', $content);
            $this->assertStringContainsString('application/ld+json', $content);
            $this->assertStringContainsString('https://misubaguna.com' . $url, $content);
        }
    }

    public function test_title_helper_keeps_brand_once_and_fits_serp_width(): void
    {
        $this->assertSame('Coupling | PT Misuba Guna Indonesia', \App\Support\PublicSeo::title('Coupling - PT Misuba Guna Indonesia'));
        $this->assertSame('Cooling Tower Repair & Refurbishment Services | Misuba Guna', \App\Support\PublicSeo::title('Cooling Tower Repair & Refurbishment Services - PT Misuba Guna Indonesia'));
        $this->assertSame('PT Misuba Guna Indonesia - Build on Trust', \App\Support\PublicSeo::title('PT Misuba Guna Indonesia - Build on Trust'));
    }

    public function test_description_helper_cuts_on_a_word_boundary(): void
    {
        $long = str_repeat('pressure ', 40);
        $desc = \App\Support\PublicSeo::description($long);
        $this->assertLessThanOrEqual(160, mb_strlen($desc));
        $this->assertStringEndsWith('pressure…', $desc);
    }

    public function test_path_helper_adds_trailing_slash_but_not_to_files(): void
    {
        $this->assertSame('/product/coupling/', \App\Support\PublicSeo::path('/product/coupling'));
        $this->assertSame('/sitemap.xml', \App\Support\PublicSeo::path('/sitemap.xml'));
        $this->assertSame('/', \App\Support\PublicSeo::path(''));
    }

    public function test_robots_txt_lists_ai_search_crawlers(): void
    {
        $content = $this->get('/robots.txt')->getContent();
        foreach (['OAI-SearchBot', 'Claude-SearchBot', 'Perplexity-User', 'Bingbot'] as $bot) {
            $this->assertStringContainsString('User-agent: ' . $bot, $content);
        }
    }

    public function test_pages_use_the_share_image_not_the_logo(): void
    {
        $this->get('/toolkit/')->assertSee('og-default.jpg', false)->assertDontSee('og:locale:alternate', false);
    }
}
