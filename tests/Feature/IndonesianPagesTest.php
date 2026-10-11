<?php

namespace Tests\Feature;

use App\Models\PublicSite\PublicProduct;
use App\Support\IndonesianPages;
use App\Support\PublicSeo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndonesianPagesTest extends TestCase
{
    use RefreshDatabase;

    private const KEYS = ['name', 'meta_title', 'meta_description', 'summary', 'description_html', 'key_features', 'applications', 'materials', 'technical_specs', 'quote_checklist', 'also_known_as'];

    public function test_every_content_file_is_complete_and_fits_search_results(): void
    {
        $this->assertGreaterThanOrEqual(15, count(IndonesianPages::slugs()));

        foreach (IndonesianPages::slugs() as $slug) {
            $c = IndonesianPages::content($slug);
            foreach (self::KEYS as $key) {
                $this->assertArrayHasKey($key, $c, "$slug: $key");
            }
            $this->assertLessThanOrEqual(60, mb_strlen(PublicSeo::title($c['meta_title'])), "$slug title");
            $this->assertLessThanOrEqual(160, mb_strlen($c['meta_description']), "$slug description");
            $this->assertGreaterThanOrEqual(100, mb_strlen($c['meta_description']), "$slug description");
            $this->assertStringNotContainsString('<h1', $c['description_html'], "$slug has its own h1");
            $this->assertStringNotContainsStringIgnoringCase('placeholder', json_encode($c), $slug);
        }
    }

    public function test_indonesian_product_page_is_in_indonesian_and_linked_to_english(): void
    {
        $this->product('rubber-expansion-joint');

        $html = $this->get('/id/product/rubber-expansion-joint/')->assertOk()->getContent();

        $this->assertStringContainsString('<html lang="id">', $html);
        $this->assertStringContainsString('<link rel="canonical" href="https://misubaguna.com/id/product/rubber-expansion-joint/">', $html);
        $this->assertStringContainsString('hreflang="en" href="https://misubaguna.com/product/rubber-expansion-joint/"', $html);
        $this->assertStringContainsString('hreflang="id" href="https://misubaguna.com/id/product/rubber-expansion-joint/"', $html);
        $this->assertStringContainsString('hreflang="x-default"', $html);
        $this->assertStringContainsString('<meta property="og:locale" content="id_ID">', $html);
        $this->assertStringContainsString('Minta penawaran', $html);
        $this->assertStringContainsString('Siapa supplier', $html);
        $this->assertStringContainsString('Juga dikenal sebagai', $html);
        $this->assertStringNotContainsString('>Request a quote<', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertMatchesRegularExpression('/<title>Jual Rubber Expansion Joint[^<]*<\/title>/', $html);
    }

    public function test_english_page_points_to_its_indonesian_version(): void
    {
        $this->product('rubber-expansion-joint');

        $this->get('/product/rubber-expansion-joint/')->assertOk()
            ->assertSee('hreflang="id" href="https://misubaguna.com/id/product/rubber-expansion-joint/"', false)
            ->assertSee('Baca dalam Bahasa Indonesia')
            ->assertSee('<html lang="en">', false);
    }

    public function test_index_lists_indonesian_pages_and_unknown_slugs_404(): void
    {
        $this->product('rubber-expansion-joint');
        $this->product('gland-packing', 'Sealing System');

        $this->get('/id/')->assertOk()
            ->assertSee('/id/product/rubber-expansion-joint/', false)
            ->assertSee('/id/product/gland-packing/', false)
            ->assertSee('Produk Industri PT Misuba Guna Indonesia');

        $this->get('/id/product/does-not-exist/')->assertNotFound();
        $this->product('o-ring', 'Sealing System');
        $this->get('/id/product/o-ring/')->assertNotFound();
    }

    public function test_sitemap_and_llms_include_indonesian_pages(): void
    {
        $this->product('rubber-expansion-joint');

        $this->get('/sitemap.xml')->assertSee('<loc>https://misubaguna.com/id/product/rubber-expansion-joint/</loc>', false)
            ->assertSee('<loc>https://misubaguna.com/id/</loc>', false);
        $this->get('/llms.txt')->assertSee('https://misubaguna.com/id/product/rubber-expansion-joint/');
    }

    public function test_placeholder_cleanup_migration_removes_only_editing_text(): void
    {
        $row = $this->product('gasket-sheet', 'Sealing System', [
            'content_html' => '<p class="product-description">Real text.</p><p class="product-placeholder">[Placeholder: Insert detailed text.]</p><h2>Technical Notes</h2><ul><li>Current page may have repeated heading/content; verify before final import</li></ul>',
            'technical_specs' => ['Current page may have repeated heading/content; verify before final import', 'Thickness: 1.5 mm'],
        ]);

        $migration = require database_path('migrations/2026_10_12_000003_remove_placeholder_text_from_products.php');
        $migration->up();

        $row->refresh();
        $this->assertSame('<p class="product-description">Real text.</p>', $row->content_html);
        $this->assertSame(['Thickness: 1.5 mm'], $row->technical_specs);
    }

    private function product(string $slug, string $group = 'Flexible Joint', array $extra = []): PublicProduct
    {
        return PublicProduct::updateOrCreate(['slug' => $slug], array_merge([
            'url_path' => '/product/' . $slug . '/',
            'name' => ucwords(str_replace('-', ' ', $slug)),
            'product_group' => $group,
            'summary' => 'English summary.',
            'is_published' => true,
            'sitemap_include' => true,
        ], $extra));
    }
}
