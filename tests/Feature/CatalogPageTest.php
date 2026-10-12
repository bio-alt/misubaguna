<?php

namespace Tests\Feature;

use App\Models\PublicSite\PublicPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_page_renders_with_reader_config(): void
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

        $html = $this->get('/catalog/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<script type="application/json" id="fb-config">\{"pdf":#', $html);
    }
}
