<?php

namespace Tests\Unit\Ejma;

use App\Ejma\BellowsGeometry;
use App\Ejma\BellowsType;
use App\Ejma\CheckLine;
use App\Ejma\DesignConditions;
use App\Ejma\EjmaInputException;
use App\Ejma\LineStatus;
use App\Ejma\Movement;
use App\Ejma\ReinforcedBellows;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Ejma\Fixtures\Benchmarks;

/**
 * EJMA 9th Ed. §4.14 benchmark 3 (single reinforced, p097–p098).
 *
 * The published e = 0.3756 cannot be reproduced from the §1.3 definitions of Kθ (p019),
 * Cθ (p018) and φ (p023), and the published Psc = 307 cannot be reproduced from eq. 4-49
 * with Cθ = 0.1157^θm (see ReinforcedBellows and task-5-report.md). The benchmark is
 * therefore verified in three parts:
 *  - every value that does not depend on the angular-rotation factors, against the table;
 *  - every e-dependent value (S1'', S5, S6, St, Nc) evaluated at the published movement;
 *  - e and Psc as computed from the text, pinned so any change is deliberate.
 */
class ReinforcedBellowsTest extends TestCase
{
    use EjmaAssertions;

    private const COEFFICIENTS = ['Cp', 'Cf', 'Cd'];

    private const RATIOS = ['Kr', 'e'];

    /** Values independent of Kθ/Cθ (e enters none of them; Kr is set by the extension expression). */
    private const MOVEMENT_INDEPENDENT = ['S1', 'S1p', 'S2', 'S2p', 'S3', 'S4', 'fi', 'Cp', 'Cf', 'Cd', 'Kr'];

    /** Values that depend on e (or ec) only. */
    private const E_DEPENDENT = ['S1pp', 'S5', 'S6', 'St', 'Nc'];

    public function test_benchmark_3_movement_independent_values(): void
    {
        $b = Benchmarks::case3();
        $result = (new ReinforcedBellows)->check($b['geometry'], $b['conditions'], $b['properties']);

        $this->assertSame(BellowsType::Reinforced, $result->type);
        foreach (self::MOVEMENT_INDEPENDENT as $key) {
            $this->assertBenchmark($key, $b['expected'][$key], $result->values);
        }
    }

    public function test_benchmark_3_e_dependent_values_at_published_movement(): void
    {
        $b = Benchmarks::case3();
        // Published e = 0.3756 = ec + ee with ee = xExt/N = 0.0625 (note 3, p098), Kr = 1.0625.
        $m = new Movement(
            exComp: 0.125, exExt: 0.0625, ey: 0.0, eyExt: 0.0, etheta: deg2rad(5) * 25.5 / 16,
            ec: 0.3756 - 0.0625, ee: 0.0625, e: 0.3756, Kr: 1.0625,
        );
        $result = (new ReinforcedBellows)->checkAt($b['geometry'], $b['conditions'], $b['properties'], $m);

        foreach ([...self::E_DEPENDENT, ...self::MOVEMENT_INDEPENDENT] as $key) {
            $this->assertBenchmark($key, $b['expected'][$key], $result->values);
        }
    }

    public function test_benchmark_3_e_and_psc_per_text(): void
    {
        $b = Benchmarks::case3();
        $result = (new ReinforcedBellows)->check($b['geometry'], $b['conditions'], $b['properties']);
        $v = $result->values;

        // Cθ = 0.1157^θm, θm = 5° in radians (p018).
        $this->assertEqualsWithDelta(0.82844, $v['Ctheta'], 1e-5);
        // φ = PN²q/(0.675π fir) (p023) with fir from eq. 4-50.
        $this->assertEqualsWithDelta(100 * 64 / (0.675 * M_PI * $v['fi']), $v['phi'], 1e-9);
        // Kθ = eθCθ/(eθCθ + 0.15qφ) (p019) → 0.8620; e = eθ/Kθ + ex(comp) + ex(ext).
        $this->assertEqualsWithDelta(0.8620, $v['Ktheta'], 1e-4);
        $this->assertEqualsWithDelta(0.34884, $v['e'], 1e-4);
        // Eq. 4-49: 0.3π·Cθ·fir/(N²q) = 299.4 (published 307).
        $this->assertEqualsWithDelta(299.44, $v['Psc'], 0.05);
        // Published e = 0.3756 and Psc = 307 are not reproduced; pinned here as known deviations.
        $this->assertGreaterThan(0.005 * 0.3756, abs($v['e'] - 0.3756));
        $this->assertGreaterThan(0.005 * 307, abs($v['Psc'] - 307));
    }

    public function test_benchmark_3_lines(): void
    {
        $b = Benchmarks::case3();
        $c = $b['conditions'];
        $conditions = new DesignConditions($c->P, $c->xComp, $c->xExt, $c->y, $c->theta, 1000);
        $result = (new ReinforcedBellows)->check($b['geometry'], $conditions, $b['properties']);

        $keys = array_map(fn ($l) => $l->key, $result->lines);
        $this->assertSame([
            'hoop_tangent', 'hoop_collar', 'collar_bending', 'hoop_bellows', 'hoop_reinforcement',
            'meridional', 'fatigue', 'squirm_column',
        ], $keys);
        $symbols = array_map(fn ($l) => $l->symbol, $result->lines);
        $this->assertSame(['S1', "S1'", "S1'+S1''", 'S2', "S2'", 'S3+S4', 'Nc', 'Psc'], $symbols);
        $equations = array_map(fn ($l) => $l->equation, $result->lines);
        $this->assertSame(['4-38', '4-39', '4-40', '4-41', '4-42', '4-44/4-45', '4-48', '4-49'], $equations);

        $lines = [];
        foreach ($result->lines as $line) {
            $lines[$line->key] = $line;
        }
        $v = $result->values;
        // Ks = 1.5, solid rectangle (Table III, p153): collar bending limit 1.5·Cwc·Sac.
        $this->assertEqualsWithDelta(1.5 * 20000, $lines['collar_bending']->limit, 1e-9);
        $this->assertEqualsWithDelta($v['S1p'] + $v['S1pp'], $lines['collar_bending']->value, 1e-9);
        $this->assertEqualsWithDelta(20000, $lines['hoop_reinforcement']->limit, 1e-9);
        $this->assertEqualsWithDelta($v['S3'] + $v['S4'], $lines['meridional']->value, 1e-9);
        $this->assertEqualsWithDelta(1.5 * 20000, $lines['meridional']->limit, 1e-9);
        $this->assertSame(CheckLine::GE, $lines['fatigue']->comparison);
        $this->assertSame(LineStatus::Pass, $lines['fatigue']->status);
        $this->assertEqualsWithDelta(100, $lines['squirm_column']->value, 1e-9);
        $this->assertSame(LineStatus::Pass, $lines['squirm_column']->status);
        foreach ($result->lines as $line) {
            $this->assertNotSame(LineStatus::NotEvaluated, $line->status, $line->key);
        }
    }

    public function test_fg_uses_theoretical_spring_rate_when_fw_not_given(): void
    {
        $b = Benchmarks::case3();
        $withFw = (new ReinforcedBellows)->check($b['geometry'], $b['conditions'], $b['properties']);
        $noFw = (new ReinforcedBellows)->check($this->geometry(['fw' => null]), $b['conditions'], $b['properties']);

        // fir (24545) > fw (14751) → larger gusset force and collar bending stress.
        $this->assertGreaterThan($withFw->values['S1pp'], $noFw->values['S1pp']);
        $this->assertEqualsWithDelta($noFw->values['fi'], $noFw->values['fspring'], 1e-9);
        $this->assertEqualsWithDelta(14751, $withFw->values['fspring'], 1e-9);
    }

    public function test_no_collar_omits_collar_lines(): void
    {
        $b = Benchmarks::case3();
        $result = (new ReinforcedBellows)->check(
            $this->geometry(['tc' => 0.0, 'Atc' => 0.0, 'Zc' => 0.0, 'ng' => 0]),
            $b['conditions'],
            $b['properties'],
        );

        $keys = array_map(fn ($l) => $l->key, $result->lines);
        $this->assertNotContains('hoop_collar', $keys);
        $this->assertNotContains('collar_bending', $keys);
        $this->assertArrayNotHasKey('S1p', $result->values);
        $this->assertArrayNotHasKey('S1pp', $result->values);
        $this->assertGreaterThan(3294, $result->values['S1'], 'no collar to share the tangent hoop load');
    }

    public function test_missing_inputs_are_field_errors(): void
    {
        $b = Benchmarks::case3();
        $c = $b['conditions'];
        $cases = [
            'w' => [$this->geometry(['w' => null]), $c],
            'rm' => [$this->geometry(['rm' => null]), $c],
            'Lt' => [$this->geometry(['Lt' => null]), $c],
            'Ar' => [$this->geometry(['Ar' => 0.0]), $c],
            'Atc' => [$this->geometry(['Atc' => 0.0]), $c],
            'Zc' => [$this->geometry(['Zc' => 0.0]), $c],
            'ng' => [$this->geometry(['ng' => 0]), $c],
            'design_pressure' => [$b['geometry'], new DesignConditions(-1, $c->xComp, $c->xExt, $c->y, $c->theta, null)],
        ];
        foreach ($cases as $field => [$geometry, $conditions]) {
            try {
                (new ReinforcedBellows)->check($geometry, $conditions, $b['properties']);
                $this->fail("Expected EjmaInputException on $field");
            } catch (EjmaInputException $e) {
                $this->assertSame($field, $e->field);
            }
        }
    }

    /** @param  array<string, mixed>  $override */
    private function geometry(array $override): BellowsGeometry
    {
        $g = Benchmarks::case3()['geometry'];
        $args = [
            'type' => $g->type, 'Db' => $g->Db, 't' => $g->t, 'n' => $g->n, 'N' => $g->N, 'q' => $g->q,
            'w' => $g->w, 'rm' => $g->rm, 'Lt' => $g->Lt, 'tc' => $g->tc, 'Ar' => $g->Ar, 'Atc' => $g->Atc,
            'Zc' => $g->Zc, 'ng' => $g->ng, 'fw' => $g->fw,
        ];

        return new BellowsGeometry(...array_merge($args, $override));
    }

    /** @param  array<string, float>  $values */
    private function assertBenchmark(string $key, float $expected, array $values): void
    {
        $this->assertArrayHasKey($key, $values, "value $key");
        if (in_array($key, self::COEFFICIENTS, true)) {
            $this->assertCoefficient($expected, $values[$key], $key);
        } elseif (in_array($key, self::RATIOS, true)) {
            $this->assertRatio($expected, $values[$key], $key);
        } else {
            $this->assertEjmaValue($expected, $values[$key], $key);
        }
    }
}
