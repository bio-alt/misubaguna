<?php

namespace Tests\Feature;

use Database\Seeders\FabricationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FabricationPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_lists_every_fabrication_product(): void
    {
        $slugs = FabricationSeeder::slugs();
        $this->assertSame(['storage-tank', 'pressurized-tank', 'pinion-gear', 'worm-and-rack-gear'], $slugs);

        $response = $this->get('/product-category/fabrication/')->assertOk()->assertSee('Fabrication');

        foreach ($slugs as $slug) {
            $response->assertSee('/product/'.$slug.'/', false);
        }
    }

    public function test_each_product_page_renders_with_its_own_quote_checklist(): void
    {
        $checklist = [
            'storage-tank' => 'Liquid stored, its density and temperature',
            'pressurized-tank' => 'Medium, design pressure and design temperature',
            'pinion-gear' => 'Drawing, or the worn pinion and its mating gear',
            'worm-and-rack-gear' => 'Drawing, or the worn worm, wheel or rack with its pinion',
        ];

        foreach ($checklist as $slug => $line) {
            $this->get('/product/'.$slug.'/')
                ->assertOk()
                ->assertSee('Fabrication')
                ->assertSee($line)
                ->assertSee('<link rel="canonical" href="https://misubaguna.com/product/'.$slug.'/"', false);
        }
    }

    public function test_pages_are_in_the_sitemap(): void
    {
        $response = $this->get('/sitemap.xml')->assertOk()->assertSee('/product-category/fabrication/', false);

        foreach (FabricationSeeder::slugs() as $slug) {
            $response->assertSee('/product/'.$slug.'/', false);
        }
    }

    public function test_menus_group_company_pages_and_link_fabrication(): void
    {
        $this->seed();

        $this->get('/')->assertOk()
            // Desktop: Company and Fabrication are their own dropdowns, Home/About/Contact/Career are not top-bar links.
            ->assertSee('id="company-btn"', false)
            ->assertSee('id="fab-btn"', false)
            ->assertSee('<a class="mega-link" href="/product/storage-tank/">Storage Tank</a>', false)
            ->assertSee('<a class="mega-link" href="/product/worm-and-rack-gear/">Worm and Rack Gear</a>', false)
            ->assertSee('<div id="company-menu">', false)
            ->assertSee('<a class="mega-link" href="/career/">Career</a>', false)
            // Mobile drawer mirrors it.
            ->assertSee('id="mob-company-body"', false)
            ->assertSee('id="mob-fab-body"', false)
            ->assertSee('<a class="mob-cat-link" href="/product/pinion-gear/">Pinion Gear</a>', false)
            ->assertDontSee('<a class="mob-nav-link" href="/career/">Career</a>', false)
            ->assertDontSee('href="#"', false);
    }
}
