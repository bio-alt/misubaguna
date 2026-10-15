<?php

namespace Tests\Feature;

use Database\Seeders\MaterialHandlingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialHandlingPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_lists_every_material_handling_product(): void
    {
        $slugs = MaterialHandlingSeeder::slugs();
        $this->assertContains('bucket-elevator', $slugs);
        $this->assertContains('screw-conveyor', $slugs);

        $response = $this->get('/product-category/material-handling/')->assertOk()->assertSee('Material Handling');

        foreach ($slugs as $slug) {
            $response->assertSee('/product/'.$slug.'/', false);
        }
    }

    public function test_each_product_page_renders_with_its_quote_checklist(): void
    {
        foreach (MaterialHandlingSeeder::slugs() as $slug) {
            $this->get('/product/'.$slug.'/')
                ->assertOk()
                ->assertSee('Material Handling')
                ->assertSee('Capacity (t/h) and material temperature')
                ->assertSee('<link rel="canonical" href="https://misubaguna.com/product/'.$slug.'/"', false);
        }
    }

    public function test_pages_are_in_the_sitemap(): void
    {
        $response = $this->get('/sitemap.xml')->assertOk()->assertSee('/product-category/material-handling/', false);

        foreach (MaterialHandlingSeeder::slugs() as $slug) {
            $response->assertSee('/product/'.$slug.'/', false);
        }
    }

    public function test_every_gallery_photo_exists(): void
    {
        $galleries = \Illuminate\Support\Facades\DB::table('public_products')
            ->whereIn('slug', MaterialHandlingSeeder::slugs())->pluck('gallery');

        foreach ($galleries as $gallery) {
            foreach (json_decode($gallery, true) as $path) {
                $this->assertFileExists(public_path($path));
            }
        }

        $this->get('/product/bucket-elevator/')->assertSee(MaterialHandlingSeeder::image('bucket-elevator-installation'), false);
    }
}
