<?php

namespace Tests\Feature;

use App\Models\PublicSite\PublicPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToolkitSelectorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_expansion_joint_selector_page_renders(): void
    {
        $this->get('/toolkit/expansion-joint-selector/')
            ->assertOk()
            ->assertSee('Expansion Joint Selector')
            ->assertSee('id="ej-results"', false)
            ->assertSee('/toolkit/chemical-resistance/', false);
    }

    public function test_chemical_resistance_page_renders_with_chart_data(): void
    {
        $this->get('/toolkit/chemical-resistance/')
            ->assertOk()
            ->assertSee('Chemical Resistance Guide')
            ->assertSee('Sulfuric Acid')
            ->assertSee('Chlorine Dioxide');
    }

    public function test_new_tools_are_linked_from_hub_and_sitemap(): void
    {
        $this->get('/toolkit/')
            ->assertOk()
            ->assertSee('/toolkit/expansion-joint-selector/', false)
            ->assertSee('/toolkit/chemical-resistance/', false);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('<loc>https://misubaguna.com/toolkit/expansion-joint-selector/</loc>', false)
            ->assertSee('<loc>https://misubaguna.com/toolkit/chemical-resistance/</loc>', false);
    }

    public function test_material_specs_page_renders_server_side_table(): void
    {
        $this->get('/toolkit/material-specs/')
            ->assertOk()
            ->assertSee('Material Property Library')
            ->assertSee('Stainless Steel 316L')
            ->assertSee('Hastelloy C-276')
            ->assertSee('55 materials')
            ->assertSee('/product/metal-expansion-joint/', false)
            ->assertSee('Storage Tanks')
            ->assertDontSee('SolidWorks');
    }

    public function test_contact_form_is_prefilled_from_toolkit_query(): void
    {
        PublicPage::create([
            'slug' => 'contact-us',
            'url_path' => '/contact-us/',
            'title' => 'Contact Us',
            'is_published' => true,
            'sitemap_include' => true,
            'sitemap_priority' => '0.8',
            'sitemap_changefreq' => 'monthly',
        ]);

        $this->get('/contact-us/?category=Corrosion+Protection&message=Need+PTFE+lining+for+98%25+sulfuric+acid')
            ->assertOk()
            ->assertSee('Need PTFE lining for 98% sulfuric acid')
            ->assertSee('value="Corrosion Protection" selected', false);
    }
}
