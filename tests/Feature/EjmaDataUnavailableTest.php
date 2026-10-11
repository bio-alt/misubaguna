<?php

namespace Tests\Feature;

use App\Ejma\Coefficients;
use App\Ejma\Data\MaterialRepository;
use App\Ejma\Data\Tooling;
use App\Ejma\DesignSearch;
use App\Ejma\EjmaCalculator;
use App\Http\Requests\EjmaCalculationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\EjmaData;
use Tests\TestCase;

/**
 * Graceful degradation without the private EJMA data (resources/ejma-private/, git-ignored), e.g.
 * a deploy straight from the public repository: the page shows the "being prepared" notice and a
 * submission is a validation error, never a 500. Absence is simulated with Coefficients::useDataFile().
 */
class EjmaDataUnavailableTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/toolkit/ejma-bellows-calculator/';

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $tables = [
            'allowable_mpa' => [20 => 138, 300 => 118],
            'modulus_gpa' => [20 => 195, 300 => 180],
            'yield_mpa' => [20 => 205, 300 => 150],
        ];
        config(['ejma.materials' => ['materials' => [
            'fixture_ss' => ['name' => 'Fixture stainless', 'family' => 'austenitic', 'roles' => ['bellows', 'collar', 'reinforcement'], 'creep_limit_c' => 250] + $tables,
        ]]]);
        config(['ejma.tooling' => [
            'thicknesses_mm' => [0.762],
            'max_plies' => 3,
            'max_convolutions' => 20,
            'tangent_length_mm' => 25.4,
            'profiles' => [
                ['id' => 'die1', 'name' => 'Test die', 'w_mm' => 36.576, 'q_mm' => 25.4, 'rm_mm' => 6.35, 'db_min_mm' => 500, 'db_max_mm' => 700],
            ],
        ]]);
        foreach ([MaterialRepository::class, EjmaCalculator::class, Tooling::class, DesignSearch::class] as $class) {
            $this->app->forgetInstance($class);
        }

        Coefficients::useDataFile(base_path('tests/Feature/missing-ejma-data.php'));
    }

    protected function tearDown(): void
    {
        Coefficients::useDataFile(null);
        parent::tearDown();
    }

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
            'website' => '',
        ], $overrides);
    }

    public function test_page_shows_the_notice_instead_of_the_form(): void
    {
        $this->assertFalse(Coefficients::available());

        $this->get(self::URL)->assertOk()
            ->assertSee('EJMA Bellows Calculator')
            ->assertSee('The EJMA calculator is being prepared')
            ->assertSee('Please contact us for bellows calculations in the meantime.')
            ->assertSee('href="mailto:marketing@misubaguna.com', false)
            ->assertDontSee('<form id="calculator-form"', false)
            ->assertDontSee('name="bellows_material"', false)
            ->assertDontSee('value="fixture_ss"', false);
    }

    public function test_check_submission_is_a_validation_error_not_500(): void
    {
        $this->post(self::URL, $this->payload())
            ->assertStatus(302)
            ->assertSessionHasErrors(['calculation' => EjmaCalculationRequest::UNAVAILABLE_MESSAGE]);

        $this->assertDatabaseCount('ejma_calculations', 0);
        Mail::assertNothingSent();
    }

    public function test_design_submission_is_a_validation_error_not_500(): void
    {
        $payload = $this->payload(['mode' => 'design', 'assembly_type' => 'single']);
        foreach (['bellows_type', 'w', 't', 'n', 'N', 'q', 'rm', 'Lt', 'Cwc', 'Cwr'] as $field) {
            unset($payload[$field]);
        }

        $this->post(self::URL, $payload)
            ->assertStatus(302)
            ->assertSessionHasErrors(['calculation' => EjmaCalculationRequest::UNAVAILABLE_MESSAGE])
            ->assertSessionDoesntHaveErrors('mode');

        $this->assertDatabaseCount('ejma_calculations', 0);
        Mail::assertNothingSent();
    }

    public function test_engine_failure_without_data_is_a_validation_error_not_500(): void
    {
        // Data vanishing between validation and the engine run: the controller's own guard.
        Coefficients::useDataFile(null);
        EjmaData::requireOrSkip();
        $this->app->bind(EjmaCalculator::class, function ($app) {
            Coefficients::useDataFile(base_path('tests/Feature/missing-ejma-data.php'));

            return new EjmaCalculator($app->make(MaterialRepository::class));
        });

        $this->post(self::URL, $this->payload())
            ->assertStatus(302)
            ->assertSessionHasErrors(['calculation' => EjmaCalculationRequest::UNAVAILABLE_MESSAGE]);

        $this->assertDatabaseCount('ejma_calculations', 0);
    }

    public function test_page_offers_the_form_and_design_mode_when_data_is_installed(): void
    {
        Coefficients::useDataFile(null);
        EjmaData::requireOrSkip();

        $this->get(self::URL)->assertOk()
            ->assertDontSee('The EJMA calculator is being prepared')
            ->assertSee('<form id="calculator-form"', false)
            ->assertSee('value="fixture_ss"', false)
            ->assertSee('Design it for me')
            ->assertDontSee('Custom designs: contact us', false);
    }
}
