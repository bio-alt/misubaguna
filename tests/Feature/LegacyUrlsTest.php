<?php

namespace Tests\Feature;

use App\Models\PublicSite\PublicProduct;
use App\Models\PublicSite\PublicProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** URLs Search Console reported as 404 / noindex / duplicate after the WordPress migration. */
class LegacyUrlsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['fabric-expansion-joint', 'rubber-expansion-joint', 'mechanical-seal', 'rubber-hose', 'gasket-sheet', 'ptfe-coating'] as $slug) {
            PublicProduct::create(['slug' => $slug, 'url_path' => '/product/' . $slug . '/', 'name' => $slug, 'product_group' => 'Other', 'is_published' => true]);
        }

        // Published but empty, like the live gasket-sheet category.
        PublicProductCategory::create(['slug' => 'gasket-sheet', 'url_path' => '/product-category/gasket-sheet/', 'name' => 'Gasket Sheet', 'is_published' => true]);
    }

    public static function redirects(): array
    {
        return [
            ['/fabric-expansion-joint/', '/product/fabric-expansion-joint/'],
            ['/rubber-hose/', '/product/rubber-hose/'],
            ['/products/fabric-expansion-joint/', '/product/fabric-expansion-joint/'],
            ['/product-category/mechanical-seal/', '/product/mechanical-seal/'],
            ['/product-category/ptfe-coating/feed/', '/product/ptfe-coating/'],
            ['/product-category/flexible-joint/feed/', '/product/rubber-expansion-joint/'],
            ['/product-category/unknown-thing/', '/catalog/'],
            ['/product-category/gasket-sheet/', '/product/gasket-sheet/'],
            ['/flexible-joint/', '/product/rubber-expansion-joint/'],
            ['/new-home/', '/'],
            ['/sparepart-fabrication/', '/services/'],
            ['/safety-equipment/', '/catalog/'],
            ['/metform-form/inquiries/', '/request-a-quote/'],
        ];
    }

    #[DataProvider('redirects')]
    public function test_legacy_page_redirects_permanently_in_one_hop(string $from, string $to): void
    {
        $this->get($from)->assertStatus(301)->assertRedirect('https://misubaguna.com' . $to);
    }

    public static function gone(): array
    {
        return [
            ['/wp-json/metform/v1/forms/views/'],
            ['/wp-content/plugins/litespeed-cache/guest.vary.php'],
            ['/wp-content/themes/hello-elementor/style.css'],
            ['/wp-login.php'],
            ['/wp-admin/'],
        ];
    }

    #[DataProvider('gone')]
    public function test_wordpress_system_paths_are_gone(string $path): void
    {
        $this->get($path)->assertStatus(410);
    }

    public function test_unknown_paths_still_404(): void
    {
        $this->get('/no-such-page/')->assertNotFound();
        $this->get('/product/no-such-product/')->assertNotFound();
    }
}
