<?php

namespace Tests\Unit\Ejma;

use App\Ejma\BellowsGeometry;
use App\Ejma\BellowsType;
use App\Ejma\CheckLine;
use App\Ejma\DesignConditions;
use App\Ejma\EjmaInputException;
use App\Ejma\LineStatus;
use App\Ejma\ToroidalBellows;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Ejma\Fixtures\Benchmarks;

/** EJMA 9th Ed. §4.14 benchmark 4 (single toroidal, p097–p098). */
class ToroidalBellowsTest extends TestCase
{
    use EjmaAssertions;

    private const COEFFICIENTS = ['B1', 'B2', 'B3'];

    private const RATIOS = ['e'];

    public function test_benchmark_4(): void
    {
        $b = Benchmarks::case4();
        $result = (new ToroidalBellows)->check($b['geometry'], $b['conditions'], $b['properties']);
        $v = $result->values;

        $this->assertSame(BellowsType::Toroidal, $result->type);
        foreach ($b['expected'] as $key => $expected) {
            $this->assertArrayHasKey($key, $v, "value $key");
            if (in_array($key, self::COEFFICIENTS, true)) {
                $this->assertCoefficient($expected, $v[$key], $key);
            } elseif (in_array($key, self::RATIOS, true)) {
                $this->assertRatio($expected, $v[$key], $key);
            } else {
                $this->assertEjmaValue($expected, $v[$key], $key);
            }
        }

        // St = 3S3 + S5 + S6 (eq. 4-59 note).
        $this->assertEqualsWithDelta(3 * $v['S3'] + $v['S5'] + $v['S6'], $v['St'], 1e-6);
        // θ = 0, y = 0: Cθ = 1, Kθ = 1.
        $this->assertSame(1.0, $v['Ctheta']);
        $this->assertSame(1.0, $v['Ktheta']);
    }

    public function test_benchmark_4_lines(): void
    {
        $b = Benchmarks::case4();
        $c = $b['conditions'];
        $conditions = new DesignConditions($c->P, $c->xComp, $c->xExt, $c->y, $c->theta, 1000);
        $result = (new ToroidalBellows)->check($b['geometry'], $conditions, $b['properties']);

        $this->assertSame([
            'hoop_tangent', 'hoop_collar', 'collar_bending', 'hoop_bellows', 'hoop_reinforcement',
            'meridional', 'fatigue', 'squirm_column',
        ], array_map(fn ($l) => $l->key, $result->lines));
        $this->assertSame(
            ['S1', "S1'", "S1'+S1''", 'S2', "S2'", 'S3', 'Nc', 'Psc'],
            array_map(fn ($l) => $l->symbol, $result->lines),
        );
        $this->assertSame(
            ['4-51', '4-52', '4-53', '4-54', '4-55', '4-56', '4-59', '4-60'],
            array_map(fn ($l) => $l->equation, $result->lines),
        );

        $lines = [];
        foreach ($result->lines as $line) {
            $lines[$line->key] = $line;
        }
        $v = $result->values;
        $this->assertEqualsWithDelta(20000, $lines['hoop_tangent']->limit, 1e-9);
        $this->assertEqualsWithDelta(20000, $lines['hoop_collar']->limit, 1e-9);
        // Ks = 1.5, solid rectangle (Table III, p153).
        $this->assertEqualsWithDelta(1.5 * 20000, $lines['collar_bending']->limit, 1e-9);
        $this->assertEqualsWithDelta($v['S1p'] + $v['S1pp'], $lines['collar_bending']->value, 1e-9);
        $this->assertEqualsWithDelta(20000, $lines['hoop_reinforcement']->limit, 1e-9);
        // S3 ≤ Sab (no Cm for toroidal, p095).
        $this->assertEqualsWithDelta($v['S3'], $lines['meridional']->value, 1e-9);
        $this->assertEqualsWithDelta(20000, $lines['meridional']->limit, 1e-9);
        $this->assertSame(CheckLine::GE, $lines['fatigue']->comparison);
        $this->assertSame(LineStatus::Pass, $lines['fatigue']->status);
        $this->assertEqualsWithDelta(500, $lines['squirm_column']->value, 1e-9);
        $this->assertSame(LineStatus::Pass, $lines['squirm_column']->status);
        foreach ($result->lines as $line) {
            $this->assertNotSame(LineStatus::NotEvaluated, $line->status, $line->key);
        }
        // Benchmark 4 has collar bending 10407 + 12975 = 23382 ≤ 30000 → passes.
        $this->assertSame(LineStatus::Pass, $lines['collar_bending']->status);
    }

    public function test_fg_uses_theoretical_spring_rate_when_fw_not_given(): void
    {
        $b = Benchmarks::case4();
        $withFw = (new ToroidalBellows)->check($b['geometry'], $b['conditions'], $b['properties']);
        $noFw = (new ToroidalBellows)->check($this->geometry(['fw' => null]), $b['conditions'], $b['properties']);

        $this->assertEqualsWithDelta(15714, $withFw->values['fspring'], 1e-9);
        $this->assertEqualsWithDelta($noFw->values['fi'], $noFw->values['fspring'], 1e-9);
    }

    public function test_angular_rotation_reduces_squirm_pressure_and_raises_e(): void
    {
        $b = Benchmarks::case4();
        $c = $b['conditions'];
        $rotated = new DesignConditions($c->P, $c->xComp, $c->xExt, 0, deg2rad(2), null);
        $v = (new ToroidalBellows)->check($b['geometry'], $rotated, $b['properties'])->values;

        $this->assertLessThan(1.0, $v['Ctheta']);
        $this->assertLessThan(1851, $v['Psc']);
        $this->assertGreaterThan(0.375, $v['e']);
        // φ = PN²r/(0.15π fit) (AngularPressureFactor, ruling R7).
        $this->assertEqualsWithDelta(500 * 4 * 1 / (0.15 * M_PI * $v['fi']), $v['phi'], 1e-9);
    }

    public function test_missing_inputs_are_field_errors(): void
    {
        $b = Benchmarks::case4();
        $c = $b['conditions'];
        $cases = [
            'r' => [$this->geometry(['r' => null]), $c],
            'Dm' => [$this->geometry(['DmToroidal' => null]), $c],
            'Dr' => [$this->geometry(['Dr' => null]), $c],
            'Ar' => [$this->geometry(['Ar' => 0.0]), $c],
            'tc' => [$this->geometry(['tc' => 0.0]), $c],
            'Atc' => [$this->geometry(['Atc' => 0.0]), $c],
            'Zc' => [$this->geometry(['Zc' => 0.0]), $c],
            'ng' => [$this->geometry(['ng' => 0]), $c],
            'geometry' => [$this->geometry(['r' => 5.0, 'DmToroidal' => 30.0]), $c],
            'design_pressure' => [$b['geometry'], new DesignConditions(-1, $c->xComp, $c->xExt, $c->y, $c->theta, null)],
        ];
        foreach ($cases as $field => [$geometry, $conditions]) {
            try {
                (new ToroidalBellows)->check($geometry, $conditions, $b['properties']);
                $this->fail("Expected EjmaInputException on $field");
            } catch (EjmaInputException $e) {
                $this->assertSame($field, $e->field, $e->getMessage());
            }
        }
    }

    public function test_r_too_large_for_dm_is_field_error(): void
    {
        // Dm − 2r ≤ 0 makes eq. 4-56 undefined.
        $this->expectException(EjmaInputException::class);
        $b = Benchmarks::case4();
        try {
            (new ToroidalBellows)->check($this->geometry(['r' => 14.0, 'Dr' => 25.1]), $b['conditions'], $b['properties']);
        } catch (EjmaInputException $e) {
            $this->assertSame('r', $e->field);
            throw $e;
        }
    }

    /** @param  array<string, mixed>  $override */
    private function geometry(array $override): BellowsGeometry
    {
        $g = Benchmarks::case4()['geometry'];
        $args = [
            'type' => $g->type, 'Db' => $g->Db, 't' => $g->t, 'n' => $g->n, 'N' => $g->N, 'q' => $g->q,
            'w' => $g->w, 'rm' => $g->rm, 'Lt' => $g->Lt, 'tc' => $g->tc, 'Ld' => $g->Ld, 'Ar' => $g->Ar,
            'Atc' => $g->Atc, 'Zc' => $g->Zc, 'ng' => $g->ng, 'r' => $g->r, 'Dr' => $g->Dr,
            'DmToroidal' => $g->DmToroidal, 'fw' => $g->fw,
        ];

        return new BellowsGeometry(...array_merge($args, $override));
    }
}
