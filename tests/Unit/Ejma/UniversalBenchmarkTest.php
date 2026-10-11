<?php

namespace Tests\Unit\Ejma;

use App\Ejma\AssemblyGeometry;
use App\Ejma\AssemblyType;
use App\Ejma\BellowsType;
use App\Ejma\DesignConditions;
use App\Ejma\ReinforcedBellows;
use App\Ejma\ToroidalBellows;
use App\Ejma\UnreinforcedBellows;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Ejma\Fixtures\Benchmarks;

/**
 * §4.14 benchmark 2 (universal unreinforced, p097–p098) and the assembly-aware
 * angular pressure factors Cθ, φ, Kθ (controller ruling P2-R2).
 */
class UniversalBenchmarkTest extends TestCase
{
    use EjmaAssertions;

    private const COEFFICIENTS = ['Cp', 'Cf', 'Cd'];

    private const RATIOS = ['Kr', 'e'];

    public function test_benchmark_2(): void
    {
        $b = Benchmarks::case2();
        $result = (new UnreinforcedBellows)->check($b['geometry'], $b['conditions'], $b['properties']);

        $this->assertSame(BellowsType::Unreinforced, $result->type);
        foreach ($b['expected'] as $key => $expected) {
            $this->assertArrayHasKey($key, $result->values, "value $key");
            $actual = $result->values[$key];
            if (in_array($key, self::COEFFICIENTS, true)) {
                $this->assertCoefficient($expected, $actual, $key);
            } elseif (in_array($key, self::RATIOS, true)) {
                $this->assertRatio($expected, $actual, $key);
            } else {
                $this->assertEjmaValue($expected, $actual, $key);
            }
        }
        // Figure 4.1 (p067), Lu = 36, Lb = 4.
        $this->assertEqualsWithDelta(3456 / 3088, $result->values['Ku'], 1e-12, 'Ku');
        $this->assertContains('Assembly: Universal (2 bellows; checks apply to each bellows)', $result->notes);
    }

    public function test_universal_psc_uses_total_convolutions(): void
    {
        $b = Benchmarks::case2();
        $result = (new UnreinforcedBellows)->check($b['geometry'], $b['conditions'], $b['properties']);
        $v = $result->values;

        // Note to eq. 4-35 (p092): N is the total in both bellows, 2 × 4 = 8; Cθ = 1 (y ≠ 0).
        $this->assertEqualsWithDelta(0.34 * M_PI * $v['fi'] / (8 ** 2 * 1.0), $v['Psc'], 1e-9);

        $single = $this->asSingle($b['conditions']);
        $s = (new UnreinforcedBellows)->check($b['geometry'], $single, $b['properties'])->values;
        $this->assertEqualsWithDelta($s['Psc'] / 4, $v['Psc'], 1e-9, 'Psc scales with 1/(2N)² against a single bellows');

        foreach ([[new ReinforcedBellows, Benchmarks::case3()], [new ToroidalBellows, Benchmarks::case4()]] as [$checker, $case]) {
            $c = $case['conditions'];
            $universal = new DesignConditions($c->P, $c->xComp, $c->xExt, $c->y, $c->theta, null,
                new AssemblyGeometry(type: AssemblyType::Universal, Lu: 60));
            $u = $checker->check($case['geometry'], $universal, $case['properties'])->values;
            $one = $checker->check($case['geometry'], $c, $case['properties'])->values;
            // Ctheta differs (θm = θ/2 for universal), so compare Psc/Cθ: notes to eqs. 4-49 / 4-60.
            $this->assertEqualsWithDelta($one['Psc'] / $one['Ctheta'] / 4, $u['Psc'] / $u['Ctheta'], 1e-6, $case['geometry']->type->value);
        }
    }

    public function test_universal_pure_rotation_uses_assembly_angular_factors(): void
    {
        $b = Benchmarks::case2();
        $g = $b['geometry'];
        $theta = deg2rad(3);
        $c = new DesignConditions(P: 100, xComp: 1, xExt: 0.5, y: 0, theta: $theta, requiredCycles: null,
            assembly: new AssemblyGeometry(type: AssemblyType::Universal, Lu: 36));
        $v = (new UnreinforcedBellows)->check($g, $c, $b['properties'])->values;

        $Dm = 25.5;          // Db + w + nt (§1.3 p018)
        $N = 4;              // per bellows
        $q = 1.0;
        $fi = $v['fi'];
        $ex = 1 / (2 * $N);  // eq. 4-2
        $ee = 0.5 / (2 * $N);

        // P2-R2: eθ per eq. 4-4, θm = θ/2 per bellows (no concurrent lateral), φ with N = 2·4.
        $etheta = $theta * $Dm / (4 * $N);
        $Ctheta = 0.1157 ** ($theta / 2);
        $phi = 100 * (2 * $N) ** 2 * $q / (0.34 * M_PI * $fi);
        $Ktheta = $etheta * $Ctheta / ($etheta * $Ctheta + 0.15 * $q * $phi);
        $e = $etheta / $Ktheta + $ex + $ee;

        // Old behaviour: Cθ, φ, Kθ from single-bellows values (eq. 4-3, θm = θ, N = 4).
        $ethetaOld = $theta * $Dm / (2 * $N);
        $CthetaOld = 0.1157 ** $theta;
        $phiOld = 100 * $N ** 2 * $q / (0.34 * M_PI * $fi);
        $KthetaOld = $ethetaOld * $CthetaOld / ($ethetaOld * $CthetaOld + 0.15 * $q * $phiOld);
        $eOld = $etheta / $KthetaOld + $ex + $ee;

        $this->assertEqualsWithDelta($Ctheta, $v['Ctheta'], 1e-12, 'Ctheta');
        $this->assertEqualsWithDelta($phi, $v['phi'], 1e-12, 'phi');
        $this->assertEqualsWithDelta($Ktheta, $v['Ktheta'], 1e-12, 'Ktheta');
        $this->assertEqualsWithDelta($e, $v['e'], 1e-12, 'e');
        $this->assertEqualsWithDelta(0.5243, $Ktheta, 1e-3, 'hand value Kθ');
        $this->assertEqualsWithDelta(0.3467, $e, 1e-3, 'hand value e');
        $this->assertGreaterThan(0.01, abs($KthetaOld - $v['Ktheta']));
        $this->assertGreaterThan($eOld * 1.2, $v['e'], 'e increases against the single-bellows Kθ');
    }

    public function test_gimbal_second_plane_rotation_applies_pressure_term(): void
    {
        $b = Benchmarks::case1();
        $g = $b['geometry'];
        $theta2 = deg2rad(4);
        $c = new DesignConditions(P: 40, xComp: 0, xExt: 0, y: 0, theta: 0, requiredCycles: null,
            assembly: new AssemblyGeometry(type: AssemblyType::Gimbal, theta2: $theta2));
        $result = (new UnreinforcedBellows)->check($g, $c, $b['properties']);
        $v = $result->values;

        $Dm = 25.5;
        $N = 12;
        $etheta = $theta2 * $Dm / (2 * $N);                     // eq. 4-3 with the resultant θ
        $Ctheta = 0.1157 ** $theta2;                           // θm = resultant, no lateral
        $phi = 40 * $N ** 2 * 1.0 / (0.34 * M_PI * $v['fi']);
        $Ktheta = $etheta * $Ctheta / ($etheta * $Ctheta + 0.15 * 1.0 * $phi);

        $this->assertLessThan(1.0, $v['Ctheta']);
        $this->assertEqualsWithDelta($Ctheta, $v['Ctheta'], 1e-12, 'Ctheta');
        $this->assertEqualsWithDelta($Ktheta, $v['Ktheta'], 1e-12, 'Ktheta');
        $this->assertLessThan(1.0, $v['Ktheta']);
        $this->assertEqualsWithDelta($etheta / $Ktheta, $v['e'], 1e-12, 'e');
        $this->assertEqualsWithDelta(0.34 * M_PI * $Ctheta * $v['fi'] / ($N ** 2), $v['Psc'], 1e-9, 'Psc with Cθ');
        $this->assertContains('Assembly: Gimbal (1 bellows)', $result->notes);
    }

    public function test_hinged_ignores_lateral_for_ctheta(): void
    {
        $b = Benchmarks::case1();
        $theta = deg2rad(5);
        // A stale lateral value must not switch the pressure term off (hinged applies y = 0).
        $c = new DesignConditions(P: 40, xComp: 0, xExt: 0, y: 0.5, theta: $theta, requiredCycles: null,
            assembly: new AssemblyGeometry(type: AssemblyType::Hinged));
        $v = (new UnreinforcedBellows)->check($b['geometry'], $c, $b['properties'])->values;

        $this->assertEqualsWithDelta(0.1157 ** $theta, $v['Ctheta'], 1e-12);
        $this->assertLessThan(1.0, $v['Ktheta']);
    }

    private function asSingle(DesignConditions $c): DesignConditions
    {
        return new DesignConditions($c->P, $c->xComp, $c->xExt, $c->y, $c->theta, $c->requiredCycles);
    }
}
