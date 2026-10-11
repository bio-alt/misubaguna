<?php

namespace Tests\Feature;

use App\Ejma\Data\MaterialRepository;
use App\Ejma\EjmaCalculator;
use App\Ejma\Verdict;
use App\Models\EjmaCalculation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EjmaCalculatorPageTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/toolkit/ejma-bellows-calculator/';

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-11 10:00:00');

        $tables = [
            'allowable_mpa' => [20 => 138, 300 => 118],
            'modulus_gpa' => [20 => 195, 300 => 180],
            'yield_mpa' => [20 => 205, 300 => 150],
        ];
        config(['ejma.materials' => ['materials' => [
            'fixture_ss' => ['name' => 'Fixture stainless', 'family' => 'austenitic', 'roles' => ['bellows', 'collar', 'reinforcement'], 'creep_limit_c' => 250] + $tables,
            'inconel625' => ['name' => 'Inconel 625', 'family' => 'nickel', 'roles' => ['bellows', 'collar', 'reinforcement'], 'creep_limit_c' => null,
                'allowable_mpa' => [], 'modulus_gpa' => [], 'yield_mpa' => []],
        ]]]);
        $this->app->forgetInstance(MaterialRepository::class);
        $this->app->forgetInstance(EjmaCalculator::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** EJMA benchmark case 1 (unreinforced) converted to metric. */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'units' => 'metric',
            'bellows_type' => 'unreinforced',
            'design_pressure' => '2.7579',
            'design_temperature' => '21',
            'required_cycles' => '1000',
            'x_comp' => '25.4',
            'x_ext' => '12.7',
            'lateral' => '1.524',
            'angular' => '5',
            'Db' => '609.6',
            'w' => '36.576',
            't' => '0.762',
            'n' => '2',
            'N' => '12',
            'q' => '25.4',
            'rm' => '6.35',
            'Lt' => '25.4',
            'bellows_material' => 'fixture_ss',
            'forming_condition' => 'annealed',
            'forming_method' => 'mandrel_roll',
            'Cwb' => '1',
            'Cwc' => '1',
            'Cwr' => '1',
            'name' => 'Budi Santoso',
            'company' => 'PT Industri Nusantara',
            'email' => 'budi@example.com',
            'phone' => '0812-3456-7890',
            'notes' => 'Quote for 2 pcs.',
            'website' => '',
        ], $overrides);
    }

    public function test_page_renders_and_is_linked(): void
    {
        $this->get(self::URL)->assertOk()->assertSee('EJMA Bellows Calculator');

        $this->get('/toolkit/')->assertOk()->assertSee(self::URL, false);

        $this->get('/sitemap.xml')->assertOk()
            ->assertSee('<loc>https://misubaguna.com/toolkit/ejma-bellows-calculator/</loc>', false);
    }

    private function useShippedMaterials(): void
    {
        config(['ejma.materials' => require base_path('config/ejma/materials.php')]);
        $this->app->forgetInstance(MaterialRepository::class);
        $this->app->forgetInstance(EjmaCalculator::class);
    }

    public function test_without_usable_bellows_materials_a_notice_replaces_the_form(): void
    {
        $this->useShippedMaterials();

        $this->get(self::URL)->assertOk()
            ->assertSee('The EJMA calculator is being prepared')
            ->assertSee('Please contact us for bellows calculations in the meantime.')
            ->assertSee('href="mailto:marketing@misubaguna.com', false)
            ->assertDontSee('<form id="calculator-form"', false)
            ->assertDontSee('action="/toolkit/ejma-bellows-calculator/"', false)
            ->assertDontSee('name="bellows_material"', false);
    }

    public function test_post_without_usable_materials_is_a_validation_error_not_500(): void
    {
        $this->useShippedMaterials();

        $this->post(self::URL, $this->payload())
            ->assertStatus(302)
            ->assertSessionHasErrors('bellows_material');

        $this->assertDatabaseCount('ejma_calculations', 0);
    }

    public function test_only_materials_with_data_are_offered(): void
    {
        $this->get(self::URL)->assertOk()
            ->assertSee('value="fixture_ss"', false)
            ->assertDontSee('inconel625');
    }

    public function test_valid_submission_stores_and_shows_verdict_only(): void
    {
        $response = $this->post(self::URL, $this->payload());
        $response->assertRedirect(self::URL.'#result');
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseCount('ejma_calculations', 1);
        $row = EjmaCalculation::first();
        $this->assertSame('EJ-2026-0001', $row->reference);
        $this->assertSame('unreinforced', $row->bellows_type);
        $this->assertSame('budi@example.com', $row->contact['email']);
        $this->assertSame('metric', $row->inputs['units']);
        $this->assertEqualsWithDelta(24.0, $row->inputs['us']['Db'], 1e-9);
        $this->assertEqualsWithDelta(609.6, $row->inputs['metric']['Db'], 1e-9);
        $this->assertEqualsWithDelta(40.0, $row->inputs['us']['design_pressure'], 0.001);

        $s1 = collect($row->results['lines'])->firstWhere('symbol', 'S1')['value'];
        $this->assertIsFloat($s1);

        $page = $this->followRedirects($response);
        $page->assertOk()
            ->assertSee(Verdict::from($row->verdict)->label())
            ->assertSee('EJ-2026-0001')
            ->assertSee('mailto:marketing@misubaguna.com?subject=EJMA%20calculation%20EJ-2026-0001', false)
            ->assertSee('Email us for the results')
            ->assertDontSee(number_format($s1, 0, '.', ''))
            ->assertDontSee(number_format($s1, 1, '.', ''))
            ->assertDontSee(number_format($s1, 0))
            ->assertDontSee('S3+S4');
    }

    public function test_us_units_submission_stores_metric_conversions(): void
    {
        $this->post(self::URL, $this->payload([
            'units' => 'us', 'design_pressure' => '40', 'design_temperature' => '70',
            'x_comp' => '1', 'x_ext' => '0.5', 'lateral' => '0.06', 'Db' => '24', 'w' => '1.44',
            't' => '0.03', 'q' => '1', 'rm' => '0.25', 'Lt' => '1',
        ]))->assertRedirect(self::URL.'#result');

        $row = EjmaCalculation::first();
        $this->assertSame('us', $row->inputs['units']);
        $this->assertEqualsWithDelta(609.6, $row->inputs['metric']['Db'], 1e-9);
        $this->assertEqualsWithDelta(24.0, $row->inputs['us']['Db'], 1e-9);
        $this->assertSame('24', $row->inputs['entered']['Db']['value']);
        $this->assertSame('in', $row->inputs['entered']['Db']['unit']);
    }

    /** EJMA benchmark case 4 (toroidal), US units. */
    private function toroidalPayload(array $overrides = []): array
    {
        return $this->payload(array_merge([
            'units' => 'us', 'bellows_type' => 'toroidal', 'design_pressure' => '500', 'design_temperature' => '70',
            'x_comp' => '0.75', 'x_ext' => '', 'lateral' => '', 'angular' => '',
            'Db' => '24', 't' => '0.05', 'n' => '1', 'N' => '2', 'q' => '3.4625', 'w' => '', 'rm' => '', 'Lt' => '',
            'tc' => '1', 'Ld' => '2.106', 'Ar' => '2.282', 'Atc' => '1.27', 'Zc' => '0.271', 'ng' => '12',
            'r' => '1', 'Dr' => '25.1', 'Dm' => '27.496', 'fw' => '15714',
            'collar_material' => 'fixture_ss', 'reinforcement_material' => 'fixture_ss',
        ], $overrides));
    }

    public function test_toroidal_submission_is_calculated(): void
    {
        $this->post(self::URL, $this->toroidalPayload())
            ->assertSessionHasNoErrors()
            ->assertRedirect(self::URL.'#result');

        $row = EjmaCalculation::first();
        $this->assertSame('toroidal', $row->bellows_type);
        $this->assertEqualsWithDelta(27.496, $row->inputs['us']['Dm'], 1e-9);
        $this->assertArrayNotHasKey('rm', $row->inputs['us']);
        $this->assertSame('fixture_ss', $row->inputs['materials']['collar']);
    }

    public function test_toroidal_geometry_error_maps_to_r(): void
    {
        $this->post(self::URL, $this->toroidalPayload(['r' => '5']))
            ->assertSessionHasErrors('r');

        $this->post(self::URL, $this->toroidalPayload(['reinforcement_material' => '']))
            ->assertSessionHasErrors('reinforcement_material');

        $this->assertDatabaseCount('ejma_calculations', 0);
    }

    public function test_reinforced_submission_is_calculated(): void
    {
        $this->post(self::URL, $this->payload([
            'units' => 'us', 'bellows_type' => 'reinforced', 'design_pressure' => '100', 'design_temperature' => '70',
            'x_comp' => '1', 'x_ext' => '0.5', 'lateral' => '', 'angular' => '5',
            'Db' => '24', 't' => '0.03', 'n' => '2', 'N' => '8', 'q' => '1', 'w' => '1.44', 'rm' => '0.25', 'Lt' => '1.25',
            'tc' => '0.375', 'Ar' => '0.15', 'Atc' => '0.43', 'Zc' => '0.0872', 'ng' => '8', 'fw' => '14751',
            'collar_material' => 'fixture_ss', 'reinforcement_material' => 'fixture_ss',
        ]))->assertSessionHasNoErrors()->assertRedirect(self::URL.'#result');

        $this->assertSame('reinforced', EjmaCalculation::first()->bellows_type);
    }

    public function test_invalid_submission_shows_field_errors(): void
    {
        $this->post(self::URL, $this->payload(['Db' => '']))
            ->assertSessionHasErrors('Db')
            ->assertHeader('Location', 'http://localhost/toolkit/ejma-bellows-calculator/#calculator-form');

        $this->assertDatabaseCount('ejma_calculations', 0);
    }

    public function test_required_cycles_is_required(): void
    {
        $this->post(self::URL, $this->payload(['required_cycles' => '']))
            ->assertSessionHasErrors('required_cycles');

        $this->assertDatabaseCount('ejma_calculations', 0);
    }

    public function test_engine_input_error_maps_to_field(): void
    {
        // T = 2·rm/w > 1: outside the Appendix I charts.
        // Redirect keeps the trailing slash: a second (slash) redirect would consume the flashed errors.
        $this->post(self::URL, $this->payload(['rm' => '25']))
            ->assertSessionHasErrors('rm')
            ->assertHeader('Location', 'http://localhost/toolkit/ejma-bellows-calculator/#calculator-form');
        $this->assertStringContainsString('outside the range of the EJMA design charts', session('errors')->first('rm'));

        // Above the material's creep limit (250 °C).
        $this->post(self::URL, $this->payload(['design_temperature' => '280']))
            ->assertSessionHasErrors('design_temperature');

        // Tampered material without data.
        $this->post(self::URL, $this->payload(['bellows_material' => 'inconel625']))
            ->assertSessionHasErrors('bellows_material');

        $this->assertDatabaseCount('ejma_calculations', 0);
    }

    public function test_honeypot_discards(): void
    {
        $this->post(self::URL, $this->payload(['website' => 'http://spam.example']))
            ->assertRedirect(self::URL.'#calculator-form')
            ->assertHeader('Location', 'http://localhost/toolkit/ejma-bellows-calculator/#calculator-form');

        $this->assertDatabaseCount('ejma_calculations', 0);
    }

    public function test_rate_limit_is_separate_from_contact_form(): void
    {
        Mail::fake();
        $contact = ['name' => 'Budi', 'company' => 'PT X', 'email' => 'budi@example.com', 'phone' => '0812',
            'category' => 'Flexible Joint', 'message' => 'Hello'];
        for ($i = 0; $i < 5; $i++) {
            $this->post('/contact-us/', $contact)->assertStatus(302);
        }

        for ($i = 0; $i < 5; $i++) {
            $this->post(self::URL, $this->payload(['Db' => '']))->assertStatus(302);
        }
        $this->post(self::URL, $this->payload(['Db' => '']))->assertStatus(429);

        // Calculator submissions do not lock the contact form either.
        $this->travel(2)->minutes();
        $this->post('/contact-us/', $contact)->assertStatus(302);
    }

    public function test_out_of_range_numbers_are_rejected_not_500(): void
    {
        $this->post(self::URL, $this->payload(['design_pressure' => '1e400']))
            ->assertSessionHasErrors('design_pressure');
        $this->post(self::URL, $this->payload(['Db' => '20000']))
            ->assertSessionHasErrors('Db');
        $this->post(self::URL, $this->payload(['units' => 'us', 'design_pressure' => '15000']))
            ->assertSessionHasErrors('design_pressure');
        $this->post(self::URL, $this->payload(['units' => 'us', 'design_pressure' => '14000', 'design_temperature' => '70']))
            ->assertSessionDoesntHaveErrors('design_pressure')
            ->assertSessionHasErrors('Db');

        // The metric lengths are out of range in US units (609.6 in > 394 in), so nothing is stored.
        $this->assertDatabaseCount('ejma_calculations', 0);
    }

    public function test_unexpected_engine_error_becomes_general_error(): void
    {
        config(['ejma.materials.materials.zero_e' => ['name' => 'Zero modulus', 'family' => 'other', 'roles' => ['collar', 'reinforcement'],
            'creep_limit_c' => 250, 'allowable_mpa' => [20 => 100, 300 => 90], 'modulus_gpa' => [20 => 0, 300 => 0], 'yield_mpa' => [20 => 200, 300 => 150]]]);
        $this->app->forgetInstance(MaterialRepository::class);
        $this->app->forgetInstance(EjmaCalculator::class);

        $this->post(self::URL, $this->toroidalPayload(['collar_material' => 'zero_e', 'reinforcement_material' => 'zero_e']))
            ->assertSessionHasErrors('calculation')
            ->assertSessionHasInput('Db', '24')
            ->assertHeader('Location', 'http://localhost/toolkit/ejma-bellows-calculator/#calculator-form');
        $this->assertSame('We could not complete this calculation. Please check the inputs or contact us.', session('errors')->first('calculation'));

        $this->assertDatabaseCount('ejma_calculations', 0);
    }

    public function test_required_markers_and_hints_are_type_aware(): void
    {
        $html = $this->get(self::URL)->assertOk()->getContent();

        foreach (['tc', 'Atc', 'Zc', 'ng', 'Ld', 'collar_material'] as $field) {
            $this->assertMatchesRegularExpression('#<label for="ejma_'.$field.'">[^<]*<span class="req" data-req-types="toroidal"#', $html, $field);
        }
        $this->assertMatchesRegularExpression('#data-hint-types="unreinforced reinforced"[^>]*>Leave empty if no collar#', $html);
        $this->assertMatchesRegularExpression('#data-hint-types="toroidal"[^>]*>Required for toroidal bellows#', $html);
    }

    public function test_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(self::URL, $this->payload(['Db' => '']))->assertStatus(302);
        }

        $this->post(self::URL, $this->payload(['Db' => '']))->assertStatus(429);
    }
}
