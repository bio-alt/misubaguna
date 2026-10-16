<?php

namespace Tests\Feature;

use App\Support\RalColors;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RalColorChartTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/toolkit/ral-color-chart/';

    public function test_color_data_is_well_formed(): void
    {
        $colors = RalColors::all();
        $codes = array_column($colors, 'code');

        $this->assertSame(count($codes), count(array_unique($codes)), 'RAL numbers must be unique');
        $this->assertSame(RalColors::count(), count($colors));
        $this->assertSame(array_sum(RalColors::familyCounts()), count($colors));

        foreach ($colors as $c) {
            $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/', $c['hex'], 'RAL '.$c['code']);
            $this->assertArrayHasKey($c['family'], RalColors::FAMILIES);
            $this->assertNotSame('', $c['name']);
        }

        $byCode = array_column($colors, null, 'code');
        $this->assertSame('Sky blue', $byCode[5015]['name']);
        $this->assertSame('Pure white', $byCode[9010]['name']);
        $this->assertSame('Jet black', $byCode[9005]['name']);
    }

    public function test_page_lists_every_color_with_number_name_and_hex(): void
    {
        $response = $this->get(self::URL)->assertOk()
            ->assertSee('RAL Color Chart &amp; Color Matcher', false)
            ->assertSee('<link rel="canonical" href="https://misubaguna.com'.self::URL.'"', false)
            ->assertSee('Find the closest RAL')
            ->assertSee('registered trademark of RAL gGmbH');

        foreach (RalColors::all() as $c) {
            $response->assertSee('data-code="'.$c['code'].'"', false);
        }
        $response->assertSee('RAL 5015')->assertSee('Sky blue')->assertSee('#2271B3');
    }

    public function test_page_is_in_the_hub_menus_and_sitemap(): void
    {
        $this->seed();

        $this->get('/toolkit/')->assertOk()->assertSee('href="'.self::URL.'"', false);
        $this->get('/')->assertOk()
            ->assertSee('<a class="mega-link" href="'.self::URL.'">RAL Color Chart</a>', false)
            ->assertSee('<a class="mob-cat-link" href="'.self::URL.'">RAL Color Chart</a>', false);
        $this->get('/sitemap.xml')->assertOk()->assertSee('<loc>https://misubaguna.com'.self::URL.'</loc>', false);
        $this->get('/llms.txt')->assertOk()->assertSee('https://misubaguna.com'.self::URL, false);
    }

    public function test_quote_page_accepts_a_color_prefill(): void
    {
        $this->get('/request-a-quote/?item='.rawurlencode('RAL 5015 Sky blue').'&message='.rawurlencode('Color required: RAL 5015 Sky blue (approx. #2271B3).'))
            ->assertOk()
            ->assertSee('RAL 5015 Sky blue')
            ->assertSee('Color required: RAL 5015 Sky blue');
    }
}
