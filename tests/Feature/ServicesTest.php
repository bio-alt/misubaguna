<?php

namespace Tests\Feature;

use App\Models\PublicSite\PublicService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        PublicService::create([
            'slug' => 'cooling-tower-repair',
            'url_path' => '/services/cooling-tower-repair/',
            'name' => 'Cooling Tower Repair & Refurbishment',
            'service_group' => 'Thermal & Cooling Systems',
            'headline' => 'Cooling Tower Repair',
            'summary' => 'Cooling tower repair services.',
            'key_features' => ['Feature 1'],
            'typical_problems' => ['Problem 1'],
            'scope_of_work' => ['Step 1'],
            'industries_served' => ['Industry 1'],
            'materials_equipment' => ['Material 1'],
            'related_products' => ['cooling-tower'],
            'sort_order' => 1,
            'is_published' => true,
            'sitemap_include' => true,
        ]);

        PublicService::create([
            'slug' => 'thermal-spray-coating',
            'url_path' => '/services/thermal-spray-coating/',
            'name' => 'Thermal Spray Coating',
            'service_group' => 'Protective Lining & Coating',
            'headline' => 'Thermal Spray & Metal Spraying Services',
            'summary' => 'High-performance metal spray coating services.',
            'key_features' => ['metal spray restoration'],
            'typical_problems' => ['metal spraying wear'],
            'scope_of_work' => ['metal spray application'],
            'industries_served' => ['Mining'],
            'materials_equipment' => ['Metal Spraying Equipment'],
            'related_products' => ['ceramic-coating'],
            'sort_order' => 2,
            'is_published' => true,
            'sitemap_include' => true,
        ]);

        PublicService::create([
            'slug' => 'valve-repair',
            'url_path' => '/services/valve-repair/',
            'name' => 'Valve Repair & Overhaul',
            'service_group' => 'Mechanical & Field Services',
            'headline' => 'Valve Repair Services',
            'summary' => 'Industrial valve repair services.',
            'key_features' => ['Valve seat lapping'],
            'typical_problems' => ['Valve leakage'],
            'scope_of_work' => ['Valve overhaul'],
            'industries_served' => ['Refineries'],
            'materials_equipment' => ['Test Rig'],
            'related_products' => ['gland-packing'],
            'sort_order' => 3,
            'is_published' => true,
            'sitemap_include' => true,
        ]);
    }

    public function test_services_landing_page_renders_successfully(): void
    {
        $response = $this->get('/services/');
        $response->assertStatus(200);
        $response->assertSee('Services');
        $response->assertSee('Cooling Tower Repair');
    }

    public function test_service_detail_page_renders_successfully(): void
    {
        $response = $this->get('/services/cooling-tower-repair/');
        $response->assertStatus(200);
        $response->assertSee('Cooling Tower Repair');
        $response->assertSee('BreadcrumbList');
        $response->assertSee('"@type":"Service"', false);
    }

    public function test_thermal_spray_coating_page_contains_seo_keywords(): void
    {
        $response = $this->get('/services/thermal-spray-coating/');
        $response->assertStatus(200);
        $response->assertSee('metal spray', false);
        $response->assertSee('metal spraying', false);
    }

    public function test_sitemap_includes_service_urls(): void
    {
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $response->assertSee('/services/cooling-tower-repair');
        $response->assertSee('/services/valve-repair');
    }
}
