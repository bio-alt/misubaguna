<?php

namespace Tests\Unit\Ejma;

use App\Ejma\BellowsGeometry;
use App\Ejma\BellowsType;
use App\Ejma\CheckLine;
use App\Ejma\DesignConditions;
use App\Ejma\EjmaInputException;
use App\Ejma\LineStatus;
use App\Ejma\ReinforcedBellows;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Ejma\Fixtures\Benchmarks;

/** EJMA 9th Ed. §4.14 benchmark 3 (single reinforced, p097–p098). */
class ReinforcedBellowsTest extends TestCase
{
    use EjmaAssertions;

    private const COEFFICIENTS = ['Cp', 'Cf', 'Cd'];

    private const RATIOS = ['Kr', 'e'];

    /** Documented deviations, asserted separately below. */
    private const DEVIATIONS = ['Psc', 'Nc'];

    public function test_benchmark_3(): void
    {
        $b = Benchmarks::case3();
        $result = (new ReinforcedBellows)->check($b['geometry'], $b['conditions'], $b['properties']);
        $v = $result->values;

        $this->assertSame(BellowsType::Reinforced, $result->type);
        foreach ($b['expected'] as $key => $expected) {
            $this->assertArrayHasKey($key, $v, "value $key");
            if (in_array($key, self::DEVIATIONS, true)) {
                continue;
            }
            if (in_array($key, self::COEFFICIENTS, true)) {
                $this->assertCoefficient($expected, $v[$key], $key);
            } elseif (in_array($key, self::RATIOS, true)) {
                $this->assertRatio($expected, $v[$key], $key);
            } else {
                $this->assertEjmaValue($expected, $v[$key], $key);
            }
        }

        // Kθ with φ = P/(limiting squirm pressure without Cθ) = PN²q/(0.3π fir) (AngularPressureFactor):
        // Cθ = 0.1157^θm = 0.8284, φ = 0.2766, Kθ = 0.7352 → e = 0.3767 (published 0.3756, +0.29 %).
        $this->assertEqualsWithDelta(0.82844, $v['Ctheta'], 1e-5);
        $this->assertEqualsWithDelta(100 * 64 / (0.3 * M_PI * $v['fi']), $v['phi'], 1e-9);
        $this->assertEqualsWithDelta(0.7352, $v['Ktheta'], 1e-4);

        // Known deviation: eq. 4-49 as written gives Psc = 0.3π·Cθ·fir/(N²q) = 299.5; the published
        // 307 is ~2.5 % higher and no reading of the text reproduces it. 299.5 is the conservative side.
        $this->assertEqualsWithDelta(299.48, $v['Psc'], 0.05);

        // Nc is steep in St (exponent 2.9): e +0.29 % (from the Psc gap above, since e uses P/Psc)
        // puts St +0.27 % and Nc −0.9 % (8138 vs 8213). Case-3-only tolerance of 1.5 %;
        // at the published e = 0.3756 the same equations give Nc = 8212.
        $this->assertEqualsWithDelta($b['expected']['Nc'], $v['Nc'], 0.015 * $b['expected']['Nc'], 'Nc');
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
            'meridional', 'fatigue', 'squirm_column', 'movement_ec_max', 'movement_ee_max', 'instability_axial',
        ], $keys);
        $symbols = array_map(fn ($l) => $l->symbol, $result->lines);
        $this->assertSame(['S1', "S1'", "S1'+S1''", 'S2', "S2'", 'S3+S4', 'Nc', 'Psc', 'ec', 'ee', 'ex'], $symbols);
        $equations = array_map(fn ($l) => $l->equation, $result->lines);
        $this->assertSame(['4-38', '4-39', '4-40', '4-41', '4-42', '4-44/4-45', '4-48', '4-49', '§4.2', '§4.2', '§4.7'], $equations);

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
}
