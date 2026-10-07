<?php

namespace Tests\Feature;

use Database\Seeders\FiltrationClarificationSeeder;
use Database\Seeders\PublicSiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FiltrationClarificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PublicSiteSeeder::class);
        $this->seed(FiltrationClarificationSeeder::class);
    }

    public function test_filtration_clarification_category_page_loads(): void
    {
        $response = $this->get('/product-category/filtration-clarification/');

        $response->assertStatus(200);
        $response->assertSee('Filtration &amp; Clarification', false);
        $response->assertSee('Shanghai JCI Technology Co., Ltd.', false);
        $response->assertSee('Scraping Self-Cleaning Filter (AF Series)', false);
        $response->assertSee('Automated Backwash Filter (AR Series)', false);
        $response->assertSee('Sealed Candle Filter (CFC Series)', false);
        $response->assertSee('Pressure Leaf Filter (CFP Series)', false);
        $response->assertSee('Filter Plate Press', false);
        $response->assertSee('Modular Integrated Filter (MIF Series)', false);
        $response->assertSee('Industrial Bag Filter System (BT Series)', false);
        $response->assertSee('Basket Strainer &amp; Pipeline Filter (ST Series)', false);
        $response->assertSee('Precision Cartridge Filter Housing (CT Series)', false);
        $response->assertSee('Centrifugal Separator &amp; Rotary Filter (CS &amp; RS Series)', false);
        $response->assertSee('MS High-Intensity Magnetic Iron Remover', false);
        $response->assertSee('Precision Filter Cartridges &amp; Bags', false);

        // Verify product images and principal facility images are rendered
        $response->assertSee('images/products/filtration/scraping-filter/af-series-main.webp', false);
        $response->assertSee('images/products/filtration/principal/jci-engineering-facility.webp', false);
        $response->assertSee('images/products/filtration/principal/jci-manufacturing-workshop.webp', false);
        $response->assertSee('images/products/filtration/principal/jci-global-operations.webp', false);
    }

    public function test_all_filtration_products_detail_pages_load(): void
    {
        $slugs = [
            'filter-plate-press',
            'scraping-self-cleaning-filter',
            'automated-backwash-filter',
            'candle-filter',
            'pressure-leaf-filter',
            'modular-integrated-backwash-filter',
            'bag-filter-system',
            'basket-strainer-filter',
            'cartridge-filter-housing',
            'centrifugal-solid-liquid-separator',
            'magnetic-iron-remover',
            'filter-cartridges-bags-consumables',
        ];

        foreach ($slugs as $slug) {
            $response = $this->get("/product/{$slug}/");
            $response->assertStatus(200);
            $response->assertSee('catalog-gallery', false);
            $response->assertSee('mainProductImage', false);
            $response->assertSee('.webp', false);
        }
    }

    public function test_legacy_membrane_filter_press_redirects(): void
    {
        $response = $this->get('/product/membrane-filter-press/');
        $response->assertRedirect('/product/filter-plate-press/');
    }

    public function test_mega_menu_contains_filtration_links(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('/product-category/filtration-clarification/', false);
        $response->assertSee('/product/filter-plate-press/', false);
        $response->assertSee('/product/scraping-self-cleaning-filter/', false);
        $response->assertSee('/product/automated-backwash-filter/', false);
        $response->assertSee('/product/candle-filter/', false);
        $response->assertSee('/product/pressure-leaf-filter/', false);
    }
}
