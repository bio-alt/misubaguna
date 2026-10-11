<?php

namespace Tests\Feature;

use App\Support\CouplingCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouplingCatalogPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_hub_lists_all_ten_families(): void
    {
        $families = CouplingCatalog::families();
        $this->assertCount(10, $families);

        $response = $this->get('/product/coupling/')->assertOk()->assertSee('Coupling families');

        foreach ($families as $family) {
            $response->assertSee($family['url'], false)->assertSee($family['name']);
        }
    }

    public function test_each_family_page_shows_its_series_with_photos(): void
    {
        foreach (CouplingCatalog::families() as $family) {
            $response = $this->get($family['url'])
                ->assertOk()
                ->assertSee('Series in this range')
                ->assertSee('aria-current="page"', false);

            foreach ($family['series'] as $series) {
                $response->assertSee($series['name'])->assertSee($series['image'], false);
            }
        }
    }

    public function test_every_referenced_photo_exists(): void
    {
        foreach (CouplingCatalog::families() as $family) {
            foreach (array_merge($family['gallery'], array_column($family['series'], 'image')) as $path) {
                $this->assertFileExists(public_path($path), $path);
            }
        }
    }

    public function test_pages_do_not_name_the_supplier(): void
    {
        foreach (array_merge(['/product/coupling/'], array_column(CouplingCatalog::families(), 'url')) as $url) {
            $this->get($url)->assertDontSee('JLBH')->assertDontSee('Binhao');
        }
    }

    public function test_product_pages_publish_honest_structured_data(): void
    {
        $html = $this->get('/product/jaw-coupling/')->assertOk()->getContent();

        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $blocks);
        $types = [];
        foreach ($blocks[1] as $json) {
            $data = json_decode($json, true);
            $this->assertNotNull($data, 'JSON-LD must be valid JSON');
            foreach (isset($data['@type']) ? [$data] : $data as $item) {
                $types[] = $item['@type'];
            }
        }

        $this->assertContains('Product', $types);
        $this->assertContains('FAQPage', $types);
        $this->assertContains('ItemList', $types);
        $this->assertContains('BreadcrumbList', $types);
        // No invented price or stock claim.
        $this->assertStringNotContainsString('"price":"0"', $html);
        $this->assertStringNotContainsString('InStock', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('<meta property="og:type" content="product">', $html);
        $this->assertMatchesRegularExpression('/<title>Jaw Coupling Supplier in Indonesia \| [^<]+<\/title>/', $html);
    }

    public function test_llms_txt_lists_every_coupling_page(): void
    {
        $text = $this->get('/llms.txt')->assertOk()->getContent();
        foreach (CouplingCatalog::families() as $family) {
            $this->assertStringContainsString('https://misubaguna.com' . $family['url'], $text);
        }
    }
}
