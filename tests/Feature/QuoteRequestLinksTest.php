<?php

namespace Tests\Feature;

use App\Models\PublicSite\PublicPage;
use App\Models\PublicSite\PublicProduct;
use App\Models\PublicSite\PublicService;
use App\Support\CouplingCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteRequestLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_page_quote_buttons_carry_the_product(): void
    {
        $this->product('rubber-expansion-joint');

        $this->get('/product/rubber-expansion-joint/')
            ->assertOk()
            ->assertSee('href="/request-a-quote/?product=rubber-expansion-joint"', false)
            ->assertDontSee('href="/contact-us/" class="ds-btn"', false);
    }

    public function test_indonesian_product_page_links_to_indonesian_quote_page(): void
    {
        $this->product('rubber-expansion-joint');

        $this->get('/id/product/rubber-expansion-joint/')
            ->assertOk()
            ->assertSee('href="/id/request-a-quote/?product=rubber-expansion-joint"', false);
    }

    public function test_product_cards_link_each_product(): void
    {
        $this->product('rubber-expansion-joint');

        $this->get('/id/')
            ->assertOk()
            ->assertSee('class="pc-quote" href="/id/request-a-quote/?product=rubber-expansion-joint"', false);
    }

    public function test_coupling_series_links_carry_item(): void
    {
        $family = CouplingCatalog::families()[0];

        $this->get($family['url'])
            ->assertOk()
            ->assertSee('/request-a-quote/?product='.$family['slug'].'&amp;item=', false)
            ->assertDontSee('/contact-us/?category=General', false);
    }

    public function test_service_and_selector_link_to_quote_page(): void
    {
        PublicService::create([
            'slug' => 'cooling-tower-repair',
            'url_path' => '/services/cooling-tower-repair/',
            'name' => 'Cooling Tower Repair',
            'service_group' => 'Thermal & Cooling Systems',
            'summary' => 'Cooling tower repair services.',
            'is_published' => true,
        ]);

        $this->get('/services/cooling-tower-repair/')
            ->assertOk()
            ->assertSee('/request-a-quote/?item=Cooling+Tower+Repair', false);

        $this->get('/services/')->assertOk()->assertSee('href="/request-a-quote/', false);

        $this->get('/toolkit/expansion-joint-selector/')
            ->assertOk()
            ->assertSee("'/request-a-quote/?item='", false)
            ->assertDontSee("'/contact-us/?category=' + encodeURIComponent('Flexible Joint')", false);
    }

    public function test_contact_page_links_to_quote_page(): void
    {
        PublicPage::create([
            'slug' => 'contact-us',
            'url_path' => '/contact-us/',
            'title' => 'Contact Us',
            'is_published' => true,
        ]);

        $this->get('/contact-us/')->assertOk()->assertSee('href="/request-a-quote/"', false);
    }

    private function product(string $slug): PublicProduct
    {
        return PublicProduct::updateOrCreate(['slug' => $slug], [
            'url_path' => '/product/'.$slug.'/',
            'name' => ucwords(str_replace('-', ' ', $slug)),
            'product_group' => 'Flexible Joint',
            'summary' => 'English summary.',
            'is_published' => true,
        ]);
    }
}
