<?php

namespace Tests\Unit\Ejma;

use App\Ejma\BellowsGeometry;
use App\Ejma\BellowsType;
use App\Ejma\Derived;
use App\Ejma\DesignConditions;
use App\Ejma\EjmaInputException;
use App\Ejma\Movement;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Ejma\Fixtures\Benchmarks;

class MovementTest extends TestCase
{
    use EjmaAssertions;

    public function test_benchmark1_derived_geometry(): void
    {
        $b = Benchmarks::case1();
        $d = Derived::of($b['geometry'], $b['conditions']);

        $this->assertEqualsWithDelta(25.5, $d->Dm, 1e-9, 'Dm');
        $this->assertEqualsWithDelta(0.03 * sqrt(24 / 25.5), $d->tp, 1e-12, 'tp');
        $this->assertEqualsWithDelta(12.0, $d->Lb, 1e-12, 'Lb');
        $this->assertEqualsWithDelta(1.5, $d->Ld, 1e-12, 'Ld = Lt + q/2');
        $this->assertEqualsWithDelta(min(1, 1 / (1.5 * sqrt(24 * 0.03))), $d->k, 1e-12, 'k');
        $expectedAc = (2 * M_PI * 0.25 + 2 * sqrt((0.5 - 0.5) ** 2 + (1.44 - 0.5) ** 2)) * $d->tp * 2;
        $this->assertEqualsWithDelta($expectedAc, $d->Ac, 1e-12, 'Ac');
        $this->assertEqualsWithDelta(0.3 - (100 / (0.6 * 40 ** 1.5 + 320)) ** 2, $d->Cr, 1e-12, 'Cr');
        $expectedEf = 100 * sqrt(log(1 + 2 * 1.44 / 24) ** 2 + log(1 + 2 * $d->tp / 0.5) ** 2);
        $this->assertEqualsWithDelta($expectedEf, $d->epsilonF, 1e-9, 'epsilonF');
    }

    public function test_benchmark1_e_and_kr(): void
    {
        $b = Benchmarks::case1();
        $d = Derived::of($b['geometry'], $b['conditions']);
        // Concurrent lateral deflection present: Ctheta = 1, so Ktheta = 1 (§1.3 p019).
        $m = Movement::of($b['geometry'], $b['conditions'], $d, 1.0);

        $this->assertRatio(1 / 12, $m->exComp, 'ex comp');
        $this->assertRatio(0.5 / 12, $m->exExt, 'ex ext');
        $this->assertRatio(0.0927, $m->etheta, 'etheta');
        $this->assertRatio(0.0348, $m->ey, 'ey');
        $this->assertRatio(0.2108, $m->ec, 'ec');
        $this->assertRatio($b['expected']['e'], $m->e, 'e');
        $this->assertRatio($b['expected']['Kr'], $m->Kr, 'Kr');
    }

    public function test_benchmark3_derived_collar(): void
    {
        $b = Benchmarks::case3();
        $d = Derived::of($b['geometry'], $b['conditions']);

        $this->assertEqualsWithDelta(24 + 0.12 + 0.375, $d->Dc, 1e-12, 'Dc');
        $this->assertEqualsWithDelta(1.75, $d->Ld, 1e-12, 'Ld');
        $this->assertEqualsWithDelta(-0.2431 + 0.0168 * 8 + 0.3024 * 64, $d->Cc, 1e-12, 'Cc');
    }

    public function test_benchmark4_e(): void
    {
        $b = Benchmarks::case4();
        $d = Derived::of($b['geometry'], $b['conditions']);
        $m = Movement::of($b['geometry'], $b['conditions'], $d);

        $this->assertEqualsWithDelta(27.496, $d->Dm, 1e-12, 'Dm toroidal');
        $this->assertEqualsWithDelta(0.05 * sqrt(24 / 27.496), $d->tp, 1e-12, 'tp toroidal');
        $this->assertEqualsWithDelta(25.1, $d->Dc, 1e-12, 'Dc');
        $this->assertEqualsWithDelta(2.106, $d->Ld, 1e-12, 'Ld given');
        $this->assertRatio($b['expected']['e'], $m->e, 'e');
    }

    public function test_missing_ld_and_lt_throws(): void
    {
        $g = new BellowsGeometry(
            type: BellowsType::Toroidal,
            Db: 24, t: 0.05, n: 1, N: 2, q: 3.4625, w: null, rm: null, Lt: null,
            r: 1, DmToroidal: 27.496,
        );
        $c = new DesignConditions(P: 500, xComp: 0.75, xExt: 0, y: 0, theta: 0, requiredCycles: null);

        try {
            Derived::of($g, $c);
            $this->fail('Expected EjmaInputException');
        } catch (EjmaInputException $e) {
            $this->assertSame('Ld', $e->field);
        }
    }

    public function test_lateral_with_compression_exceeding_length_throws(): void
    {
        $g = new BellowsGeometry(
            type: BellowsType::Unreinforced,
            Db: 24, t: 0.03, n: 2, N: 2, q: 1, w: 1.44, rm: 0.25, Lt: 1,
        );
        $c = new DesignConditions(P: 40, xComp: 2, xExt: 0, y: 0.1, theta: 0, requiredCycles: null);
        $d = Derived::of($g, $c);

        try {
            Movement::of($g, $c, $d);
            $this->fail('Expected EjmaInputException');
        } catch (EjmaInputException $e) {
            $this->assertSame('x_comp', $e->field);
        }
    }

    public function test_toroidal_without_dm_throws_on_dm_field(): void
    {
        $g = new BellowsGeometry(
            type: BellowsType::Toroidal,
            Db: 24, t: 0.05, n: 1, N: 2, q: 3.4625, w: null, rm: null, Lt: null, Ld: 2.106, r: 1,
        );
        $c = new DesignConditions(P: 500, xComp: 0.75, xExt: 0, y: 0, theta: 0, requiredCycles: null);

        try {
            Derived::of($g, $c);
            $this->fail('Expected EjmaInputException');
        } catch (EjmaInputException $e) {
            $this->assertSame('Dm', $e->field);
        }
    }

    public function test_non_positive_n_and_q_throw_field_errors(): void
    {
        $c = new DesignConditions(P: 40, xComp: 1, xExt: 0, y: 0, theta: 0, requiredCycles: null);

        foreach (['N' => [0, 1.0], 'q' => [12, 0.0]] as $field => [$N, $q]) {
            $g = new BellowsGeometry(
                type: BellowsType::Unreinforced,
                Db: 24, t: 0.03, n: 2, N: $N, q: $q, w: 1.44, rm: 0.25, Lt: 1,
            );
            try {
                Movement::of($g, $c, Derived::of($g, $c));
                $this->fail("Expected EjmaInputException on $field");
            } catch (EjmaInputException $e) {
                $this->assertSame($field, $e->field);
            }
        }
    }

    public function test_out_of_range_ktheta_is_a_programming_error(): void
    {
        $b = Benchmarks::case1();
        $d = Derived::of($b['geometry'], $b['conditions']);

        $this->expectException(\InvalidArgumentException::class);
        Movement::of($b['geometry'], $b['conditions'], $d, 0.0);
    }
}
