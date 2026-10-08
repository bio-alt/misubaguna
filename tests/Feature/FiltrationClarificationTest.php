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
        $response->assertSee('Filter Press & Cake Clarification', false);
        $response->assertSee('Continuous Self-Cleaning', false);
        $response->assertSee('Vessels & Cartridge Housings', false);
        $response->assertSee('Separators & Media', false);
        $response->assertSee('/product/filter-plate-press/', false);
        $response->assertSee('/product/pressure-leaf-filter/', false);
        $response->assertSee('/product/candle-filter/', false);
        $response->assertSee('/product/scraping-self-cleaning-filter/', false);
        $response->assertSee('/product/automated-backwash-filter/', false);
        $response->assertSee('/product/modular-integrated-backwash-filter/', false);
        $response->assertSee('/product/bag-filter-system/', false);
        $response->assertSee('/product/cartridge-filter-housing/', false);
        $response->assertSee('/product/basket-strainer-filter/', false);
        $response->assertSee('/product/centrifugal-solid-liquid-separator/', false);
        $response->assertSee('/product/magnetic-iron-remover/', false);
        $response->assertSee('/product/filter-cartridges-bags-consumables/', false);
        $response->assertSee('CPO Dewatering & Clarification', false);
    }

    public function test_filtration_product_images_are_accurate_and_exist_on_disk(): void
    {
        // 1. Filter Plate Press
        $fpp = $this->get('/product/filter-plate-press/');
        $fpp->assertStatus(200);
        $fpp->assertSee('images/products/filtration/filter-plate-press/filter-plate-press-main.webp', false);
        $fpp->assertSee('images/products/filtration/filter-plate-press/filter-plates-detail.webp', false);
        $fpp->assertSee('images/products/filtration/filter-plate-press/filter-press-automated-plant.webp', false);

        // 2. Candle Filter (CFC)
        $cfc = $this->get('/product/candle-filter/');
        $cfc->assertStatus(200);
        $cfc->assertSee('images/products/filtration/candle-filter/cfc-candle-filter-main.webp', false);
        $cfc->assertSee('images/products/filtration/candle-filter/cfc-candle-elements.webp', false);
        $cfc->assertSee('images/products/filtration/candle-filter/cfc-bottom-discharge-valve.webp', false);

        // 3. Pressure Leaf Filter (CFP)
        $cfp = $this->get('/product/pressure-leaf-filter/');
        $cfp->assertStatus(200);
        $cfp->assertSee('images/products/filtration/pressure-leaf-filter/cfp-plate-filter-main.webp', false);
        $cfp->assertSee('images/products/filtration/pressure-leaf-filter/cfp-leaf-screens-internal.webp', false);
        $cfp->assertSee('images/products/filtration/pressure-leaf-filter/cfp-leaf-screen-plate.webp', false);
        $cfp->assertSee('images/products/filtration/pressure-leaf-filter/cfp-vibrator-unit.webp', false);

        // Verify physical presence of all images on disk
        $expectedFiles = [
            'public/images/products/filtration/filter-plate-press/filter-plate-press-main.webp',
            'public/images/products/filtration/filter-plate-press/filter-plates-detail.webp',
            'public/images/products/filtration/filter-plate-press/filter-press-automated-plant.webp',
            'public/images/products/filtration/candle-filter/cfc-candle-filter-main.webp',
            'public/images/products/filtration/candle-filter/cfc-candle-elements.webp',
            'public/images/products/filtration/candle-filter/cfc-bottom-discharge-valve.webp',
            'public/images/products/filtration/pressure-leaf-filter/cfp-plate-filter-main.webp',
            'public/images/products/filtration/pressure-leaf-filter/cfp-leaf-screens-internal.webp',
            'public/images/products/filtration/pressure-leaf-filter/cfp-leaf-screen-plate.webp',
            'public/images/products/filtration/pressure-leaf-filter/cfp-vibrator-unit.webp',
        ];

        foreach ($expectedFiles as $file) {
            $this->assertFileExists(base_path($file), "Expected image {$file} does not exist on disk.");
        }
    }
}
