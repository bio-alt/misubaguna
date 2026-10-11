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
}
