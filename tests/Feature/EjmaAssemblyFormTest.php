<?php

namespace Tests\Feature;

use App\Ejma\AssemblyType;
use App\Ejma\Data\MaterialRepository;
use App\Ejma\EjmaCalculator;
use App\Ejma\Verdict;
use App\Models\EjmaCalculation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\RequiresEjmaData;
use Tests\TestCase;

class EjmaAssemblyFormTest extends TestCase
{
    use RefreshDatabase;
    use RequiresEjmaData;

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
        ]]]);
        $this->app->forgetInstance(MaterialRepository::class);
        $this->app->forgetInstance(EjmaCalculator::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** EJMA benchmark case 1 (unreinforced) in metric, as in EjmaCalculatorPageTest; no assembly_type (Phase 1 payload). */
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
            'name' => 'Budi Santoso',
            'company' => 'PT Industri Nusantara',
            'email' => 'budi@example.com',
            'phone' => '0812-3456-7890',
            'website' => '',
        ], $overrides);
    }

    /** @return array<string, array{0: string, 1: array<string, string>}> */
    public static function assemblies(): array
    {
        return [
            'single' => ['single', []],
            'universal' => ['universal', ['Lu' => '1500', 'spool_weight' => '2000', 'spool_angle' => '0']],
            'tied universal' => ['tied_universal', ['Lu' => '1500', 'tie_rods' => '4', 'x_ext' => '']],
            'hinged' => ['hinged', ['x_comp' => '', 'x_ext' => '', 'lateral' => '']],
            'gimbal' => ['gimbal', ['x_comp' => '', 'x_ext' => '', 'lateral' => '', 'angular' => '3', 'angular_2' => '4']],
        ];
    }

    #[DataProvider('assemblies')]
    public function test_each_assembly_is_stored_and_shows_verdict_only(string $type, array $fields): void
    {
        $response = $this->post(self::URL, $this->payload(['assembly_type' => $type] + $fields));
        $response->assertSessionHasNoErrors()->assertRedirect(self::URL.'#result');

        $this->assertDatabaseCount('ejma_calculations', 1);
        $row = EjmaCalculation::first();
        $this->assertSame($type, $row->assembly_type);
        $this->assertSame($type, $row->inputs['assembly_type']);

        $s1 = collect($row->results['lines'])->firstWhere('symbol', 'S1')['value'];
        $this->assertIsFloat($s1);

        $this->followRedirects($response)->assertOk()
            ->assertSee(Verdict::from($row->verdict)->label())
            ->assertSee('EJ-2026-0001')
            ->assertDontSee(number_format($s1, 1, '.', ''))
            ->assertDontSee(number_format($s1, 0))
            ->assertDontSee('S3+S4');
    }

    public function test_universal_assembly_fields_are_stored_in_all_unit_copies(): void
    {
        $this->post(self::URL, $this->payload([
            'assembly_type' => 'universal', 'Lu' => '1500', 'spool_weight' => '2000', 'spool_angle' => '30',
        ]))->assertSessionHasNoErrors();

        $inputs = EjmaCalculation::first()->inputs;
        $this->assertSame(['value' => '1500', 'unit' => 'mm'], $inputs['entered']['Lu']);
        $this->assertSame('N', $inputs['entered']['spool_weight']['unit']);
        $this->assertEqualsWithDelta(1500 / 25.4, $inputs['us']['Lu'], 1e-9);
        $this->assertEqualsWithDelta(1500.0, $inputs['metric']['Lu'], 1e-9);
        $this->assertEqualsWithDelta(2000 / 4.4482216152605, $inputs['us']['spool_weight'], 1e-6);
        $this->assertEqualsWithDelta(2000.0, $inputs['metric']['spool_weight'], 1e-6);
        $this->assertEqualsWithDelta(30.0, $inputs['us']['spool_angle'], 1e-9);
    }

    public function test_us_units_universal_uses_inch_and_lbf(): void
    {
        $this->post(self::URL, $this->payload([
            'units' => 'us', 'assembly_type' => 'universal', 'design_pressure' => '40', 'design_temperature' => '70',
            'x_comp' => '1', 'x_ext' => '0.5', 'lateral' => '0.06', 'Db' => '24', 'w' => '1.44',
            't' => '0.03', 'q' => '1', 'rm' => '0.25', 'Lt' => '1', 'Lu' => '60', 'spool_weight' => '450', 'spool_angle' => '0',
        ]))->assertSessionHasNoErrors();

        $inputs = EjmaCalculation::first()->inputs;
        $this->assertSame(['value' => '60', 'unit' => 'in'], $inputs['entered']['Lu']);
        $this->assertSame('lbf', $inputs['entered']['spool_weight']['unit']);
        $this->assertEqualsWithDelta(1524.0, $inputs['metric']['Lu'], 1e-9);
    }

    public function test_universal_without_lu_is_a_field_error(): void
    {
        $this->post(self::URL, $this->payload(['assembly_type' => 'universal']))
            ->assertSessionHasErrors('Lu');
        $this->post(self::URL, $this->payload(['assembly_type' => 'tied_universal', 'tie_rods' => '4']))
            ->assertSessionHasErrors('Lu');

        $this->assertDatabaseCount('ejma_calculations', 0);
    }

    public function test_universal_with_too_short_lu_is_an_lu_error_from_the_engine(): void
    {
        $this->post(self::URL, $this->payload(['assembly_type' => 'universal', 'Lu' => '400']))
            ->assertSessionHasErrors('Lu');

        $this->assertDatabaseCount('ejma_calculations', 0);
    }

    public function test_tied_universal_needs_at_least_two_tie_rods(): void
    {
        $this->post(self::URL, $this->payload(['assembly_type' => 'tied_universal', 'Lu' => '1500', 'tie_rods' => '1']))
            ->assertSessionHasErrors('tie_rods');
        $this->post(self::URL, $this->payload(['assembly_type' => 'tied_universal', 'Lu' => '1500']))
            ->assertSessionHasErrors('tie_rods');

        $this->assertDatabaseCount('ejma_calculations', 0);
    }

    public function test_spool_angle_is_required_with_a_spool_weight(): void
    {
        $this->post(self::URL, $this->payload(['assembly_type' => 'universal', 'Lu' => '1500', 'spool_weight' => '2000']))
            ->assertSessionHasErrors('spool_angle');
    }

    public function test_hinged_ignores_stale_axial_and_lateral_values(): void
    {
        $this->post(self::URL, $this->payload(['assembly_type' => 'hinged', 'x_comp' => '-5', 'x_ext' => '12.7', 'lateral' => '99999']))
            ->assertSessionHasNoErrors();

        $row = EjmaCalculation::first();
        foreach (['x_comp', 'x_ext', 'lateral'] as $field) {
            $this->assertSame(0.0, (float) ($row->inputs['us'][$field] ?? 0.0), $field);
            $this->assertArrayNotHasKey($field, $row->inputs['entered']);
        }
        $this->assertEqualsWithDelta(5.0, $row->inputs['us']['angular'], 1e-9);
    }

    public function test_gimbal_ignores_stale_axial_and_lateral_and_keeps_second_plane(): void
    {
        $this->post(self::URL, $this->payload(['assembly_type' => 'gimbal', 'angular_2' => '4']))
            ->assertSessionHasNoErrors();

        $inputs = EjmaCalculation::first()->inputs;
        $this->assertArrayNotHasKey('x_comp', $inputs['us']);
        $this->assertArrayNotHasKey('lateral', $inputs['us']);
        $this->assertEqualsWithDelta(4.0, $inputs['us']['angular_2'], 1e-9);
    }

    public function test_tied_universal_ignores_axial_extension_but_keeps_axial_growth(): void
    {
        $this->post(self::URL, $this->payload(['assembly_type' => 'tied_universal', 'Lu' => '1500', 'tie_rods' => '4', 'x_ext' => '-3']))
            ->assertSessionHasNoErrors();

        $inputs = EjmaCalculation::first()->inputs;
        $this->assertArrayNotHasKey('x_ext', $inputs['us']);
        $this->assertEqualsWithDelta(1.0, $inputs['us']['x_comp'], 1e-9);
        $this->assertSame(4.0, (float) $inputs['us']['tie_rods']);
    }

    public function test_tied_universal_ignores_stale_angular_rotation(): void
    {
        $this->post(self::URL, $this->payload(['assembly_type' => 'tied_universal', 'Lu' => '1500', 'tie_rods' => '4', 'angular' => '95']))
            ->assertSessionHasNoErrors();

        $row = EjmaCalculation::first();
        $this->assertArrayNotHasKey('angular', $row->inputs['us']);
        $this->assertArrayNotHasKey('angular', $row->inputs['entered']);
        $this->assertSame(0.0, $row->result()->values['etheta']);
    }

    public function test_fields_of_other_assemblies_are_dropped(): void
    {
        $this->post(self::URL, $this->payload([
            'assembly_type' => 'single', 'Lu' => 'abc', 'tie_rods' => '1', 'angular_2' => '4', 'spool_weight' => '2000',
        ]))->assertSessionHasNoErrors();

        $inputs = EjmaCalculation::first()->inputs;
        foreach (['Lu', 'tie_rods', 'angular_2', 'spool_weight', 'spool_angle'] as $field) {
            $this->assertArrayNotHasKey($field, $inputs['us'], $field);
        }
    }

    public function test_phase1_payload_without_assembly_type_defaults_to_single(): void
    {
        $this->post(self::URL, $this->payload())->assertSessionHasNoErrors()->assertRedirect(self::URL.'#result');

        $row = EjmaCalculation::first();
        $this->assertSame('single', $row->assembly_type);
        $this->assertSame('single', $row->inputs['assembly_type']);
    }

    public function test_unknown_assembly_type_is_rejected(): void
    {
        $this->post(self::URL, $this->payload(['assembly_type' => 'pressure_balanced']))
            ->assertSessionHasErrors('assembly_type');
    }

    public function test_assembly_bounds_follow_the_unit_system(): void
    {
        $this->post(self::URL, $this->payload(['assembly_type' => 'universal', 'Lu' => '10001']))
            ->assertSessionHasErrors('Lu');
        $this->post(self::URL, $this->payload(['units' => 'us', 'assembly_type' => 'universal', 'Lu' => '395']))
            ->assertSessionHasErrors('Lu');
        $this->post(self::URL, $this->payload(['assembly_type' => 'gimbal', 'angular_2' => '90']))
            ->assertSessionHasErrors('angular_2');
    }

    public function test_form_offers_assemblies_and_their_fields(): void
    {
        $html = $this->get(self::URL)->assertOk()->getContent();

        $this->assertStringContainsString('name="assembly_type"', $html);
        foreach (AssemblyType::cases() as $type) {
            $this->assertMatchesRegularExpression('#<option value="'.$type->value.'"[^>]*>'.preg_quote($type->label(), '#').'</option>#', $html);
        }
        $this->assertMatchesRegularExpression('#<option value="single" selected>#', $html);
        foreach (['Lu', 'tie_rods', 'angular_2', 'spool_weight', 'spool_angle'] as $field) {
            $this->assertStringContainsString('name="'.$field.'"', $html);
        }
        $this->assertMatchesRegularExpression('#data-assemblies="universal tied_universal"[^>]*>\s*<label for="ejma_Lu">#', $html);
        $this->assertMatchesRegularExpression('#data-assemblies="tied_universal"[^>]*>\s*<label for="ejma_tie_rods">#', $html);
        $this->assertMatchesRegularExpression('#data-assemblies="gimbal"[^>]*>\s*<label for="ejma_angular_2">#', $html);
        $this->assertMatchesRegularExpression('#data-assemblies="single universal"[^>]*>\s*<label for="ejma_x_ext">#', $html);
        $this->assertMatchesRegularExpression('#data-assemblies="single universal tied_universal"[^>]*>\s*<label for="ejma_lateral">#', $html);
        $this->assertMatchesRegularExpression('#data-assemblies="single universal hinged gimbal"[^>]*>\s*<label for="ejma_angular">#', $html);
        $this->assertStringContainsString('Axial growth between tie-rod plates', $html);
    }

    public function test_old_assembly_selection_is_restored(): void
    {
        $html = $this->withSession(['_old_input' => ['assembly_type' => 'gimbal']])->get(self::URL)->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<option value="gimbal" selected>#', $html);
        $this->assertDoesNotMatchRegularExpression('#data-assemblies="gimbal" hidden#', $html);
    }
}
