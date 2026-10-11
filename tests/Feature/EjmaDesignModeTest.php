<?php

namespace Tests\Feature;

use App\Ejma\Data\MaterialRepository;
use App\Ejma\Data\Tooling;
use App\Ejma\DesignRequest;
use App\Ejma\DesignResult;
use App\Ejma\DesignSearch;
use App\Ejma\EjmaCalculator;
use App\Ejma\Verdict;
use App\Models\EjmaCalculation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EjmaDesignModeTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/toolkit/ejma-bellows-calculator/';

    private const FORM_URL = 'http://localhost/toolkit/ejma-bellows-calculator/#calculator-form';

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-11 10:00:00');
        Mail::fake();

        $tables = [
            'allowable_mpa' => [20 => 138, 300 => 118],
            'modulus_gpa' => [20 => 195, 300 => 180],
            'yield_mpa' => [20 => 205, 300 => 150],
        ];
        config(['ejma.materials' => ['materials' => [
            'fixture_ss' => ['name' => 'Fixture stainless', 'family' => 'austenitic', 'roles' => ['bellows', 'collar', 'reinforcement'], 'creep_limit_c' => 250] + $tables,
            // Usable in check mode, but without fatigue constants for unreinforced bellows (ruling P3-R2).
            'fixture_nickel' => ['name' => 'Fixture nickel alloy', 'family' => 'nickel', 'roles' => ['bellows'], 'creep_limit_c' => 250] + $tables,
        ]]]);
        $this->useTooling();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** Benchmark-1 convolution (w 1.44 in, q 1 in, rm 0.25 in) covering Db = 609.6 mm. */
    private function useTooling(?array $tooling = null): void
    {
        config(['ejma.tooling' => $tooling ?? [
            'thicknesses_mm' => [0.762],
            'max_plies' => 3,
            'max_convolutions' => 20,
            'tangent_length_mm' => 25.4,
            'profiles' => [
                ['id' => 'bm1', 'name' => 'Benchmark die', 'w_mm' => 36.576, 'q_mm' => 25.4, 'rm_mm' => 6.35, 'db_min_mm' => 500, 'db_max_mm' => 700],
            ],
        ]]);
        foreach ([MaterialRepository::class, EjmaCalculator::class, Tooling::class, DesignSearch::class] as $class) {
            $this->app->forgetInstance($class);
        }
    }

    /** Benchmark-1 service conditions in metric; design mode needs no convolution geometry. */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'mode' => 'design',
            'units' => 'metric',
            'assembly_type' => 'single',
            'design_pressure' => '2.7579',
            'design_temperature' => '21',
            'required_cycles' => '1000',
            'x_comp' => '25.4',
            'x_ext' => '12.7',
            'lateral' => '1.524',
            'angular' => '5',
            'Db' => '609.6',
            'bellows_material' => 'fixture_ss',
            'forming_condition' => 'annealed',
            'forming_method' => 'mandrel_roll',
            'Cwb' => '1',
            'name' => 'Budi Santoso',
            'company' => 'PT Industri Nusantara',
            'email' => 'budi@example.com',
            'phone' => '0812-3456-7890',
            'notes' => 'Quote for 2 pcs.',
            'website' => '',
        ], $overrides);
    }

    /** The full benchmark-1 check-mode payload (Phase 1 shape, no `mode`). */
    private function checkPayload(array $overrides = []): array
    {
        $payload = $this->payload([
            'bellows_type' => 'unreinforced', 'w' => '36.576', 't' => '0.762', 'n' => '2', 'N' => '12',
            'q' => '25.4', 'rm' => '6.35', 'Lt' => '25.4', 'Cwc' => '1', 'Cwr' => '1',
        ] + $overrides);
        unset($payload['mode'], $payload['assembly_type']);

        return $payload;
    }

    public function test_design_submission_stores_the_best_candidate_and_shows_the_verdict_only(): void
    {
        $response = $this->post(self::URL, $this->payload());
        $response->assertSessionHasNoErrors()->assertRedirect(self::URL.'#result');
        $response->assertSessionHasInput('mode', 'design');

        $this->assertDatabaseCount('ejma_calculations', 1);
        $row = EjmaCalculation::first();
        $this->assertSame('design', $row->mode);
        $this->assertTrue($row->isDesign());
        $this->assertSame('pass', $row->verdict);
        $this->assertSame('unreinforced', $row->bellows_type);
        $this->assertSame('single', $row->assembly_type);

        $this->assertSame('pass', $row->design['verdict']);
        $this->assertSame('bm1', $row->design['best']['profile']['id']);
        $this->assertGreaterThan(0, $row->design['evaluated']);
        $this->assertEqualsWithDelta(24.0, $row->design['best']['geometry']['Db'], 1e-9);

        // results = the best candidate's CheckResult.
        $result = $row->result();
        $this->assertSame(Verdict::Pass, $result->verdict());
        $this->assertNotEmpty($result->lines);
        $this->assertEqualsWithDelta(24.0, $row->inputs['us']['Db'], 1e-9);
        $this->assertEqualsWithDelta(609.6, $row->inputs['metric']['Db'], 1e-9);
        $this->assertSame('fixture_ss', $row->inputs['materials']['bellows']);
        $this->assertNull($row->inputs['materials']['collar']);
        $this->assertNull($row->inputs['materials']['reinforcement']);

        $this->assertSame('pass', $row->results['verdict']);
        $s1 = collect($row->results['lines'])->firstWhere('symbol', 'S1')['value'];
        $bestT = $row->design['best']['t'] * 25.4;
        $bestN = (string) $row->design['best']['N'];
        $page = $this->followRedirects($response);
        $page->assertOk()
            ->assertSee('A suitable bellows design was found')
            ->assertDontSee(Verdict::Pass->label())
            ->assertSee('EJ-2026-0001')
            ->assertSee('Email us for the results')
            ->assertDontSee('Benchmark die')
            ->assertDontSee('bm1')
            ->assertDontSee(number_format($s1, 0, '.', ''))
            ->assertDontSee(number_format($s1, 1, '.', ''))
            ->assertDontSee(number_format($bestT, 3, '.', '').' mm')
            ->assertDontSee('N = '.$bestN)
            ->assertDontSee($bestN.' convolutions');
    }

    public function test_no_passing_design_is_stored_as_review_with_the_closest_candidate_note(): void
    {
        $this->useTooling([
            'thicknesses_mm' => [0.762], 'max_plies' => 1, 'max_convolutions' => 4, 'tangent_length_mm' => 25.4,
            'profiles' => [['id' => 'bm1', 'name' => 'Benchmark die', 'w_mm' => 36.576, 'q_mm' => 25.4, 'rm_mm' => 6.35, 'db_min_mm' => 500, 'db_max_mm' => 700]],
        ]);

        $response = $this->post(self::URL, $this->payload(['design_pressure' => '20']));
        $response->assertSessionHasNoErrors()->assertRedirect(self::URL.'#result');

        $row = EjmaCalculation::first();
        $this->assertSame('design', $row->mode);
        $this->assertSame('review', $row->verdict);
        $this->assertNull($row->design['best']);
        $this->assertNotNull($row->design['closest_failing']);
        $this->assertSame([], $row->results['lines']);
        $this->assertSame('unreinforced', $row->results['type']);
        $this->assertCount(1, $row->results['notes']);
        $this->assertStringStartsWith('No tooling candidate passed all EJMA checks', $row->results['notes'][0]);
        $this->assertSame('review', $row->results['verdict']);

        $this->followRedirects($response)->assertOk()
            ->assertSee('Your conditions need a custom design by our engineers')
            ->assertDontSee(Verdict::Review->label())
            ->assertDontSee('Benchmark die')
            ->assertDontSee('No tooling candidate');
    }

    public function test_stale_geometry_and_type_fields_are_ignored_in_design_mode(): void
    {
        $this->post(self::URL, $this->payload([
            'bellows_type' => 'toroidal', 'w' => 'abc', 't' => '-1', 'n' => '99', 'N' => '0', 'q' => '', 'rm' => '999',
            'Lt' => 'x', 'tc' => '5', 'Lc' => '-2', 'Ld' => 'y', 'Ar' => '0', 'Atc' => 'z', 'Zc' => '-1', 'ng' => '0',
            'r' => '5', 'Dr' => 'q', 'Dm' => '-3', 'fw' => 'w', 'Cwc' => '7', 'Cwr' => '9',
            'collar_material' => 'nope', 'reinforcement_material' => 'nope', 'override_Sab' => '-5', 'override_Sy' => 'abc',
        ]))->assertSessionHasNoErrors()->assertRedirect(self::URL.'#result');

        $row = EjmaCalculation::first();
        $this->assertSame('design', $row->mode);
        $this->assertSame('unreinforced', $row->bellows_type);
        foreach (['w', 't', 'n', 'N', 'q', 'rm', 'Lt', 'tc', 'Lc', 'Ld', 'Ar', 'Atc', 'Zc', 'ng', 'r', 'Dr', 'Dm', 'fw', 'Cwc', 'Cwr', 'override_Sab', 'override_Sy'] as $field) {
            $this->assertArrayNotHasKey($field, $row->inputs['us'], $field);
            $this->assertArrayNotHasKey($field, $row->inputs['entered'], $field);
        }
        $this->assertSame(
            ['angular', 'Cwb', 'Db', 'design_pressure', 'design_temperature', 'lateral', 'required_cycles', 'x_comp', 'x_ext'],
            collect(array_keys($row->inputs['us']))->sort(fn ($a, $b) => strcasecmp($a, $b))->values()->all(),
        );
        $this->assertSame(['bellows' => 'fixture_ss', 'collar' => null, 'reinforcement' => null], $row->inputs['materials']);
    }

    public function test_design_mode_requires_its_own_fields(): void
    {
        $this->post(self::URL, $this->payload(['Db' => '', 'required_cycles' => '', 'bellows_material' => '']))
            ->assertSessionHasErrors(['Db', 'required_cycles', 'bellows_material'])
            ->assertHeader('Location', self::FORM_URL);

        // Universal assemblies need Lu, or every candidate would be skipped.
        $this->post(self::URL, $this->payload(['assembly_type' => 'universal']))
            ->assertSessionHasErrors('Lu');
        $this->post(self::URL, $this->payload(['assembly_type' => 'tied_universal', 'tie_rods' => '4']))
            ->assertSessionHasErrors('Lu');

        $this->post(self::URL, $this->payload(['mode' => 'other']))->assertSessionHasErrors('mode');

        $this->assertDatabaseCount('ejma_calculations', 0);
    }

    public function test_universal_design_is_searched_with_lu(): void
    {
        $this->post(self::URL, $this->payload(['assembly_type' => 'universal', 'Lu' => '1500']))
            ->assertSessionHasNoErrors()->assertRedirect(self::URL.'#result');

        $row = EjmaCalculation::first();
        $this->assertSame('universal', $row->assembly_type);
        $this->assertEqualsWithDelta(1500 / 25.4, $row->inputs['us']['Lu'], 1e-9);
        $this->assertGreaterThan(0, $row->design['evaluated']);
    }

    public function test_a_universal_lu_too_short_for_any_design_is_a_field_error(): void
    {
        $this->post(self::URL, $this->payload(['assembly_type' => 'universal', 'Lu' => '10']))
            ->assertSessionHasErrors(['Lu' => 'The distance between the bellows Lu is too short for any standard design.'])
            ->assertHeader('Location', self::FORM_URL);

        $this->assertDatabaseCount('ejma_calculations', 0);
        Mail::assertNothingOutgoing();
    }

    public function test_only_materials_with_unreinforced_fatigue_constants_are_allowed_in_design_mode(): void
    {
        $html = $this->get(self::URL)->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#<option value="fixture_nickel" data-modes="check"#', $html);
        $this->assertMatchesRegularExpression('#<option value="fixture_ss">#', $html);

        $this->post(self::URL, $this->payload(['bellows_material' => 'fixture_nickel']))
            ->assertSessionHasErrors('bellows_material');
        $this->assertDatabaseCount('ejma_calculations', 0);

        // Check mode still offers it.
        $this->post(self::URL, $this->checkPayload(['bellows_material' => 'fixture_nickel']))
            ->assertSessionDoesntHaveErrors('bellows_material');
    }

    public function test_request_level_engine_errors_map_to_fields(): void
    {
        // Above the material's creep limit (250 °C).
        $this->post(self::URL, $this->payload(['design_temperature' => '280']))
            ->assertSessionHasErrors('design_temperature')
            ->assertHeader('Location', self::FORM_URL);

        $this->assertDatabaseCount('ejma_calculations', 0);
    }

    public function test_design_mode_unavailable_without_tooling(): void
    {
        $this->useTooling(['thicknesses_mm' => [], 'profiles' => []] + require base_path('config/ejma/tooling.php'));

        $html = $this->get(self::URL)->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#<input type="radio" name="mode" value="design"[^>]*disabled#', $html);
        $this->assertStringContainsString('Custom designs: contact us', $html);
        $this->assertMatchesRegularExpression('#<input type="radio" name="mode" value="check"[^>]*checked#', $html);

        $this->post(self::URL, $this->payload())
            ->assertSessionHasErrors('mode')
            ->assertHeader('Location', self::FORM_URL);
        $this->assertDatabaseCount('ejma_calculations', 0);

        // Check mode keeps working.
        $this->post(self::URL, $this->checkPayload())->assertSessionHasNoErrors()->assertRedirect(self::URL.'#result');
        $this->assertSame('check', EjmaCalculation::first()->mode);
    }

    public function test_design_mode_is_offered_when_tooling_is_available(): void
    {
        $html = $this->get(self::URL)->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<input type="radio" name="mode" value="check"[^>]*checked#', $html);
        $this->assertDoesNotMatchRegularExpression('#<input type="radio" name="mode" value="design"[^>]*disabled#', $html);
        $this->assertStringNotContainsString('Custom designs: contact us', $html);
        // Sections that do not apply to design mode are tagged for the mode toggle.
        $this->assertMatchesRegularExpression('#<fieldset class="ejma-section" data-modes="check">\s*<h2><span class="ejma-step">\d</span> Bellows type#', $html);
        $this->assertMatchesRegularExpression('#<div class="ejma-field" data-types="[^"]*" data-modes="check"><label for="ejma_w">#', $html);
        $this->assertMatchesRegularExpression('#<div class="ejma-field" data-types="[^"]*"><label for="ejma_Db">#', $html);
    }

    public function test_check_mode_is_unchanged_with_or_without_mode(): void
    {
        $this->post(self::URL, $this->checkPayload())->assertSessionHasNoErrors()->assertRedirect(self::URL.'#result');
        $this->post(self::URL, $this->checkPayload() + ['mode' => 'check'])->assertSessionHasNoErrors();

        $rows = EjmaCalculation::orderBy('id')->get();
        $this->assertCount(2, $rows);
        foreach ($rows as $row) {
            $this->assertSame('check', $row->mode);
            $this->assertNull($row->design);
            $this->assertEqualsWithDelta(1.44, $row->inputs['us']['w'], 1e-9);
            $this->assertNotEmpty($row->results['lines']);
        }

        $this->followRedirects($this->post(self::URL, $this->checkPayload()))
            ->assertSee(Verdict::from($rows[0]->verdict)->label())
            ->assertDontSee('A suitable bellows design was found');
    }

    public function test_a_design_run_counts_as_one_calculation(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(self::URL, $this->payload())->assertSessionHasNoErrors()->assertRedirect(self::URL.'#result');
        }

        $this->post(self::URL, $this->payload())
            ->assertSessionHasErrors('calculation')
            ->assertSessionHasInput('mode', 'design');
        $this->assertDatabaseCount('ejma_calculations', 5);
    }

    public function test_over_limit_design_post_does_not_run_the_search(): void
    {
        $counter = new \ArrayObject(['calls' => 0]);
        $this->app->singleton(DesignSearch::class, fn ($app) => new class($app->make(EjmaCalculator::class), $app->make(Tooling::class), $counter) extends DesignSearch
        {
            public function __construct(EjmaCalculator $calc, Tooling $tooling, private \ArrayObject $counter)
            {
                parent::__construct($calc, $tooling);
            }

            public function search(DesignRequest $r): DesignResult
            {
                $this->counter['calls']++;

                return parent::search($r);
            }
        });

        for ($i = 0; $i < 5; $i++) {
            $this->post(self::URL, $this->payload())->assertSessionHasNoErrors();
        }
        $this->assertSame(5, $counter['calls']);

        $this->post(self::URL, $this->payload())->assertSessionHasErrors('calculation');
        $this->assertSame(5, $counter['calls'], 'the search must not run once the hourly limit is reached');
        $this->assertDatabaseCount('ejma_calculations', 5);
    }

    public function test_design_labels(): void
    {
        $this->assertSame('A suitable bellows design was found', Verdict::Pass->designLabel());
        $this->assertSame('Your conditions need a custom design by our engineers', Verdict::Review->designLabel());
    }
}
