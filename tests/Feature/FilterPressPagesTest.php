<?php

namespace Tests\Feature;

use App\Models\PublicSite\PublicProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilterPressPagesTest extends TestCase
{
    use RefreshDatabase;

    private const HUB = '/product-category/filter-press-plates/';

    /** @var array<int, string> */
    private const TYPES = [
        '/product/recessed-chamber-filter-plate/',
        '/product/membrane-filter-plate/',
        '/product/cgr-gasketed-filter-plate/',
        '/product/plate-and-frame-filter-plate/',
        '/product/special-material-filter-plate/',
    ];

    public function test_hub_shows_selection_table_and_links_all_five_types(): void
    {
        $response = $this->get(self::HUB)
            ->assertOk()
            ->assertSee('Filter Press Plates')
            ->assertSee('Choosing a plate type');

        foreach (self::TYPES as $url) {
            $response->assertSee($url, false);
        }
    }

    public function test_each_type_page_renders_with_specs_and_gallery(): void
    {
        foreach (self::TYPES as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('Specifications')
                ->assertSee('When to use')
                ->assertSee('Request a quote');
        }
    }

    public function test_every_referenced_photo_exists_with_lowercase_path(): void
    {
        $products = PublicProduct::where('product_group', 'Filter Press Plates')->get();
        $this->assertCount(5, $products);

        foreach ($products as $product) {
            $gallery = is_array($product->gallery) ? $product->gallery : (json_decode((string) $product->gallery, true) ?: []);
            $this->assertNotEmpty($gallery, $product->slug.' has no gallery');

            foreach ($gallery as $path) {
                $this->assertFileExists(public_path($path), $path);
                // Guard the Windows-to-Linux case trap: the live server is case-sensitive.
                $this->assertSame(strtolower($path), $path, 'Image path must be all lowercase: '.$path);
            }
        }
    }

    public function test_rfq_checklist_appears_on_the_quote_form(): void
    {
        $this->get('/request-a-quote/?product=membrane-filter-plate')
            ->assertOk()
            ->assertSee('Plate type: recessed chamber, membrane, gasketed (CGR), plate and frame, or special material');
    }

    public function test_type_pages_publish_product_schema_without_price(): void
    {
        $html = $this->get('/product/membrane-filter-plate/')->assertOk()->getContent();

        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $blocks);
        $types = [];
        foreach ($blocks[1] as $json) {
            $data = json_decode($json, true);
            $this->assertNotNull($data, 'JSON-LD must be valid JSON');
            foreach (isset($data['@type']) ? [$data] : $data as $item) {
                $types[] = $item['@type'] ?? null;
            }
        }

        $this->assertContains('Product', $types);
        $this->assertContains('BreadcrumbList', $types);
        // No invented price or stock claim.
        $this->assertStringNotContainsString('"offers"', $html);
        $this->assertStringNotContainsString('InStock', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
    }

    public function test_pages_do_not_name_the_supplier(): void
    {
        foreach (array_merge([self::HUB], self::TYPES) as $url) {
            $this->get($url)
                ->assertDontSee('Youbang')
                ->assertDontSee('Jinbangtai')
                ->assertDontSee('JingBangTai')
                ->assertDontSee('Ephemeral');
        }
    }
}
