<?php

namespace Tests\Unit\Ejma;

use App\Ejma\AssemblyGeometry;
use App\Ejma\AssemblyType;
use App\Ejma\BellowsGeometry;
use App\Ejma\Derived;
use App\Ejma\DesignConditions;
use App\Ejma\EjmaInputException;
use App\Ejma\Movement;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Ejma\Fixtures\Benchmarks;

class AssemblyMovementTest extends TestCase
{
    use EjmaAssertions;

    private static function conditions(
        AssemblyGeometry $assembly,
        float $xComp = 0.0,
        float $xExt = 0.0,
        float $y = 0.0,
        float $theta = 0.0,
        float $P = 100,
    ): DesignConditions {
        return new DesignConditions(
            P: $P, xComp: $xComp, xExt: $xExt, y: $y, theta: $theta, requiredCycles: null, assembly: $assembly,
        );
    }

    private static function movement(BellowsGeometry $g, DesignConditions $c, ?float $fi = null): Movement
    {
        return Movement::of($g, $c, Derived::of($g, $c), 1.0, $fi);
    }

    public function test_assembly_type_metadata(): void
    {
        $this->assertSame(1, AssemblyType::Single->bellowsCount());
        $this->assertSame(2, AssemblyType::Universal->bellowsCount());
        $this->assertSame(2, AssemblyType::TiedUniversal->bellowsCount());
        $this->assertSame(1, AssemblyType::Hinged->bellowsCount());
        $this->assertSame(1, AssemblyType::Gimbal->bellowsCount());
        $this->assertSame(0, AssemblyGeometry::single()->pins());
        $this->assertSame(2, (new AssemblyGeometry(type: AssemblyType::Hinged))->pins());
        $this->assertSame(4, (new AssemblyGeometry(type: AssemblyType::Gimbal))->pins());
        $this->assertSame('Tied universal', AssemblyType::TiedUniversal->label());
    }

    public function test_benchmark2_e_and_kr(): void
    {
        $b = Benchmarks::case2();
        $d = Derived::of($b['geometry'], $b['conditions']);
        // Concurrent lateral deflection present (y = 2): Ctheta = 1, so Ktheta = 1 (§1.3 p019).
        $m = Movement::of($b['geometry'], $b['conditions'], $d, 1.0);

        // Figure 4.1 (p067): Ku = (3Lu² − 3LbLu) / (3Lu² − 6LbLu + 4Lb²), Lu = 36, Lb = 4.
        $this->assertEqualsWithDelta(3456 / 3088, $m->Ku, 1e-12, 'Ku');
        $this->assertRatio(1 / 8, $m->exComp, 'ex comp (eq. 4-2)');
        $this->assertRatio(0.5 / 8, $m->exExt, 'ex ext (eq. 4-2)');
        $this->assertRatio($b['expected']['e'], $m->e, 'e');
        $this->assertRatio($b['expected']['Kr'], $m->Kr, 'Kr');
    }

    public function test_single_unchanged(): void
    {
        $b = Benchmarks::case1();
        $this->assertSame(AssemblyType::Single, $b['conditions']->assembly->type);

        $m = self::movement($b['geometry'], $b['conditions']);

        $this->assertSame(1.5, $m->Ku);
        $this->assertRatio($b['expected']['e'], $m->e, 'e');
        $this->assertRatio($b['expected']['Kr'], $m->Kr, 'Kr');
    }

    public function test_tied_universal_takes_internal_compression_only(): void
    {
        $g = Benchmarks::case2()['geometry'];
        $c = self::conditions(
            new AssemblyGeometry(type: AssemblyType::TiedUniversal, Lu: 36, tieRods: 2),
            xComp: 1, xExt: 0.5, y: 2,
        );
        $m = self::movement($g, $c);

        // Ruling P2-R3: x = growth between the tie-rod plates (compression), extension ignored.
        $this->assertEqualsWithDelta(1 / 8, $m->exComp, 1e-12, 'ex = x/(2N) (eq. 4-2)');
        $this->assertSame(0.0, $m->exExt);
        $Ku = 3456 / 3088;
        // Eq. 4-6: Ku·Dm·y / (2N(Lu − Lb − x/2)).
        $this->assertEqualsWithDelta($Ku * 25.5 * 2 / (2 * 4 * (36 - 4 - 0.5)), $m->ey, 1e-12, 'ey');
        $this->assertEqualsWithDelta($m->ey + 1 / 8, $m->e, 1e-12, 'e = ec = ey + ex');
    }

    public function test_tied_universal_ignores_angular_rotation(): void
    {
        $g = Benchmarks::case2()['geometry'];
        $tied = new AssemblyGeometry(type: AssemblyType::TiedUniversal, Lu: 36, tieRods: 2);
        $with = self::conditions($tied, xComp: 1, y: 2, theta: deg2rad(5));
        $without = self::conditions($tied, xComp: 1, y: 2);

        // Ruling P2-R7: the tie rods restrain angular rotation, θ is ignored.
        $this->assertSame(0.0, Movement::bellowsRotation($with));
        $m = self::movement($g, $with);
        $this->assertSame(0.0, $m->etheta);
        $this->assertEquals(self::movement($g, $without), $m);
    }

    public function test_hinged_angular_only(): void
    {
        $g = Benchmarks::case1()['geometry'];
        $theta = deg2rad(5);
        $c = self::conditions(new AssemblyGeometry(type: AssemblyType::Hinged), xComp: 1, xExt: 0.5, y: 0.5, theta: $theta);
        $m = self::movement($g, $c);

        $this->assertSame(0.0, $m->exComp);
        $this->assertSame(0.0, $m->exExt);
        $this->assertSame(0.0, $m->ey);
        // Eq. 4-3: θ·Dm / (2N), Dm = 25.5, N = 12.
        $this->assertEqualsWithDelta($theta * 25.5 / 24, $m->etheta, 1e-12, 'etheta');
        $this->assertEqualsWithDelta($m->etheta, $m->e, 1e-12, 'e');
    }

    public function test_gimbal_resultant_equals_hinged_when_one_plane_zero(): void
    {
        $g = Benchmarks::case1()['geometry'];
        $theta = deg2rad(5);
        $hinged = self::movement($g, self::conditions(new AssemblyGeometry(type: AssemblyType::Hinged), theta: $theta));

        foreach ([[$theta, 0.0], [0.0, $theta]] as [$t1, $t2]) {
            $gimbal = self::movement($g, self::conditions(
                new AssemblyGeometry(type: AssemblyType::Gimbal, theta2: $t2), xComp: 1, y: 0.5, theta: $t1,
            ));
            $this->assertEqualsWithDelta($hinged->etheta, $gimbal->etheta, 1e-12, 'etheta');
            $this->assertEqualsWithDelta($hinged->e, $gimbal->e, 1e-12, 'e');
            $this->assertEqualsWithDelta($hinged->Kr, $gimbal->Kr, 1e-12, 'Kr');
        }
    }

    public function test_gimbal_resultant(): void
    {
        $g = Benchmarks::case1()['geometry'];
        $m = self::movement($g, self::conditions(
            new AssemblyGeometry(type: AssemblyType::Gimbal, theta2: deg2rad(4)), theta: deg2rad(3),
        ));

        $this->assertEqualsWithDelta(deg2rad(5) * 25.5 / 24, $m->etheta, 1e-12, 'etheta of the 5° resultant');
    }

    public function test_universal_short_lu_throws_on_lu(): void
    {
        $g = Benchmarks::case2()['geometry']; // Lb = 4

        $cases = [
            'Lu missing' => [new AssemblyGeometry(type: AssemblyType::Universal), 1.0],
            'Lu = 2Lb' => [new AssemblyGeometry(type: AssemblyType::Universal, Lu: 8), 1.0],
            'tied Lu < 2Lb' => [new AssemblyGeometry(type: AssemblyType::TiedUniversal, Lu: 6, tieRods: 2), 0.0],
            'Lu − Lb − x/2 = 0' => [new AssemblyGeometry(type: AssemblyType::Universal, Lu: 9), 10.0],
        ];

        foreach ($cases as $label => [$assembly, $xComp]) {
            $c = self::conditions($assembly, xComp: $xComp, y: 1);
            try {
                self::movement($g, $c);
                $this->fail("Expected EjmaInputException: $label");
            } catch (EjmaInputException $e) {
                $this->assertSame('Lu', $e->field, $label);
            }
        }
    }

    public function test_tied_universal_needs_two_tie_rods(): void
    {
        $g = Benchmarks::case2()['geometry'];
        $c = self::conditions(new AssemblyGeometry(type: AssemblyType::TiedUniversal, Lu: 36, tieRods: 1), y: 1);

        try {
            self::movement($g, $c);
            $this->fail('Expected EjmaInputException');
        } catch (EjmaInputException $e) {
            $this->assertSame('tie_rods', $e->field);
        }
    }

    public function test_dead_weight_adds_movement(): void
    {
        $b = Benchmarks::case2();
        $g = $b['geometry'];
        $fi = $b['expected']['fi'];
        $base = self::movement($g, $b['conditions'], $fi);

        $Wcs = 500.0;
        $thetaU = deg2rad(30);
        $c = self::conditions(
            new AssemblyGeometry(type: AssemblyType::Universal, Lu: 36, Wcs: $Wcs, thetaU: $thetaU),
            xComp: 1, xExt: 0.5, y: 2,
        );
        $m = self::movement($g, $c, $fi);

        // Eq. 4-12 (p068): x per bellows = Wcs·sinθu·N / (2fi); per convolution / N.
        $exDw = $Wcs * sin($thetaU) / (2 * $fi);
        $this->assertEqualsWithDelta($base->exComp + $exDw, $m->exComp, 1e-12, 'ex comp + dead weight');
        $this->assertEqualsWithDelta($base->exExt + $exDw, $m->exExt, 1e-12, 'ex ext + dead weight');
        $this->assertGreaterThan($base->ey, $m->ey);
        $this->assertGreaterThan($base->e, $m->e);
    }

    public function test_dead_weight_without_fi_is_a_programming_error(): void
    {
        $g = Benchmarks::case2()['geometry'];
        $c = self::conditions(new AssemblyGeometry(type: AssemblyType::Universal, Lu: 36, Wcs: 500), y: 1);

        $this->expectException(\InvalidArgumentException::class);
        self::movement($g, $c);
    }
}
