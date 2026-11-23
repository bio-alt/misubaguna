<?php

namespace Tests\Feature;

use App\Models\PublicSite\PublicPage;
use App\Models\PublicSite\PublicProduct;
use App\Models\PublicSite\PublicProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPageTest extends TestCase
{
    use RefreshDatabase;

    private function seedPage(): void
    {
        PublicPage::create([
            'slug' => 'catalog',
            'url_path' => '/catalog/',
            'title' => 'Catalog',
            'is_published' => true,
            'sitemap_include' => true,
            'sitemap_priority' => '0.8',
            'sitemap_changefreq' => 'monthly',
        ]);
    }

    public function test_catalog_page_renders_with_reader_config(): void
    {
        config(['catalog.paused' => false]);
        $this->seedPage();

        $html = $this->get('/catalog/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<script type="application/json" id="fb-config">\{"pdf":#', $html);
    }

    public function test_paused_catalog_shows_updating_notice_without_pdf(): void
    {
        config(['catalog.paused' => true]);
        $this->seedPage();

        $html = $this->get('/catalog/')->assertOk()->getContent();

        $this->assertStringContainsString('We are updating our catalog', $html);
        $this->assertStringNotContainsString('.pdf', $html);
        $this->assertStringNotContainsString('fb-config', $html);
        $this->assertStringNotContainsString('Kakati', $html);
    }
}
