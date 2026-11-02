<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GearCalculatorPageTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/toolkit/gear-calculator/';

    public function test_page_renders_the_calculator_shell_and_crawlable_reference(): void
    {
        $this->get(self::URL)->assertOk()
            ->assertSee('<h1>Gear Calculator</h1>', false)
            ->assertSee('<link rel="canonical" href="https://misubaguna.com'.self::URL.'"', false)
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('value="spur"', false)
            ->assertSee('value="worm"', false)
            ->assertSee('js/gear-calculator.js', false)
            ->assertSee('sales@misubaguna.com')
            ->assertSee('+62 811-8715-671')
            ->assertSee('ISO 21771')
            ->assertSee('data-download="dxf"', false)
            ->assertSee('data-download="svg"', false)
            ->assertSee('data-download="step"', false)
            ->assertSee('strength and load rating (ISO 6336, AGMA) is not covered');
    }

    public function test_math_module_is_served_as_a_static_file(): void
    {
        $this->assertFileExists(public_path('js/gear-math.js'));
        $this->assertStringContainsString('export function invInverse', file_get_contents(public_path('js/gear-math.js')));
        $this->assertStringContainsString('export function gearView', file_get_contents(public_path('js/gear-drawing.js')));
        $this->assertStringContainsString('export function toDxf', file_get_contents(public_path('js/gear-dxf.js')));
        $this->assertStringContainsString('export function toStep', file_get_contents(public_path('js/gear-step.js')));
    }

    public function test_page_is_in_the_hub_menus_sitemap_and_llms(): void
    {
        $this->seed();

        $this->get('/toolkit/')->assertOk()->assertSee('href="'.self::URL.'"', false);
        $this->get('/')->assertOk()
            ->assertSee('<a class="mega-link" href="'.self::URL.'">Gear Calculator</a>', false)
            ->assertSee('<a class="mob-cat-link" href="'.self::URL.'">Gear Calculator</a>', false);
        $this->get('/sitemap.xml')->assertOk()->assertSee('<loc>https://misubaguna.com'.self::URL.'</loc>', false);
        $this->get('/llms.txt')->assertOk()->assertSee('https://misubaguna.com'.self::URL, false);
    }
}
