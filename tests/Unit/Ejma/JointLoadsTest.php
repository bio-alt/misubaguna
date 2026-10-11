<?php

namespace Tests\Unit\Ejma;

use App\Ejma\AssemblyGeometry;
use App\Ejma\AssemblyType;
use App\Ejma\BellowsGeometry;
use App\Ejma\BellowsType;
use App\Ejma\CalculationRequest;
use App\Ejma\CheckResult;
use App\Ejma\Data\MaterialRepository;
use App\Ejma\Derived;
use App\Ejma\DesignConditions;
use App\Ejma\EjmaCalculator;
use App\Ejma\FormingCondition;
use App\Ejma\FormingMethod;
use App\Ejma\Loads;
use App\Ejma\Movement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\EjmaData;
use Tests\Unit\Ejma\Fixtures\Benchmarks;

/**
 * Joint forces, moments and hardware thrust, EJMA 9th Ed. §4.6.1 eqs. 4-14…4-18 (p069),
 * working spring rate fw per §1.3 (p022).
 */
class JointLoadsTest extends TestCase
{
    private const DM = 25.5;

    private const N = 12;

    private const LB = 12.0;

    /** U-shaped bellows whose derived Dm = Db + w + nt is exactly DM, Lb = N·q. */
    private static function geometry(?float $fw = null, float $Dm = self::DM, int $N = self::N, float $Lb = self::LB): BellowsGeometry
    {
        $w = 1.0;
        $t = 0.05;

        return new BellowsGeometry(
            type: BellowsType::Unreinforced, Db: $Dm - $w - $t, t: $t, n: 1, N: $N, q: $Lb / $N,
            w: $w, rm: 0.2, Lt: 1.0, fw: $fw,
        );
    }

    private static function conditions(AssemblyGeometry $a, float $xComp = 0.0, float $xExt = 0.0, float $y = 0.0, float $theta = 0.0, float $P = 150.0): DesignConditions
    {
        return new DesignConditions(P: $P, xComp: $xComp, xExt: $xExt, y: $y, theta: $theta, requiredCycles: null, assembly: $a);
    }

    /** @return array<string, float> */
    private static function loads(BellowsGeometry $g, DesignConditions $c, float $fi = 10000.0, float $St = 0.0, float $Sy = 30000.0): array
    {
        $result = new CheckResult(BellowsType::Unreinforced, [], ['fi' => $fi, 'St' => $St, 'Ktheta' => 1.0]);

        return Loads::of($g, $c, Derived::of($g, $c), $result, $Sy);
    }

    public function test_loads_expose_per_bellows_movements(): void
    {
        $g = self::geometry();
        $c = self::conditions(new AssemblyGeometry(AssemblyType::Universal, Lu: 30.0), xComp: 1.2, y: 0.5, theta: 0.02);
        $d = Derived::of($g, $c);
        $m = Movement::of($g, $c, $d, 1.0, 10000.0);
        $loads = self::loads($g, $c);

        $this->assertEqualsWithDelta(max($m->exComp, $m->exExt), $loads['ex'], 1e-12);
        $this->assertEqualsWithDelta($m->ey, $loads['ey'], 1e-12);
        $this->assertEqualsWithDelta($m->etheta, $loads['etheta'], 1e-12);
        $this->assertGreaterThan(0.0, $loads['ex']);
    }

    public function test_working_spring_rate_rule(): void
    {
        // §1.3 p022: fw = fi for St ≤ 1.5·Sy, 0.67·fi for St > 1.5·Sy; a given fw wins.
        $this->assertSame(1234.0, Loads::workingSpringRate(1234.0, 5000.0, 1e9, 1.0));
        $this->assertSame(5000.0, Loads::workingSpringRate(null, 5000.0, 45000.0, 30000.0), 'St = 1.5·Sy boundary → fi');
        $this->assertEqualsWithDelta(3350.0, Loads::workingSpringRate(null, 5000.0, 45000.01, 30000.0), 1e-9, 'St > 1.5·Sy → 0.67·fi');
    }

    public function test_fw_from_fi_branches_through_loads(): void
    {
        $g = self::geometry();
        $c = self::conditions(AssemblyGeometry::single(), xComp: 0.6);

        $low = self::loads($g, $c, fi: 10000.0, St: 40000.0, Sy: 30000.0);
        $this->assertSame(10000.0, $low['fw']);
        $this->assertEqualsWithDelta(10000.0 * 0.6 / self::N, $low['Fa'], 1e-9);

        $high = self::loads($g, $c, fi: 10000.0, St: 50000.0, Sy: 30000.0);
        $this->assertEqualsWithDelta(6700.0, $high['fw'], 1e-9);
        $this->assertEqualsWithDelta(6700.0 * 0.6 / self::N, $high['Fa'], 1e-9);
    }

    public function test_single_joint_forces_and_moments(): void
    {
        $fw = 30000.0;
        $x = 0.405;
        $y = 0.216;
        $theta = 0.01;
        $g = self::geometry($fw);
        $c = self::conditions(AssemblyGeometry::single(), xComp: $x, xExt: 0.2, y: $y, theta: $theta, P: 125.0);
        $L = self::loads($g, $c, fi: 20000.0);

        $ex = $x / self::N;                                           // eq. 4-1
        $ey = 3 * self::DM * $y / (self::N * (self::LB - $x));       // eq. 4-7 (compression)
        $et = $theta * self::DM / (2 * self::N);                      // eq. 4-3
        $this->assertSame($fw, $L['fw'], 'given fw is used');
        $this->assertEqualsWithDelta($fw * $ex, $L['Fa'], 1e-6, 'eq. 4-14');
        $this->assertEqualsWithDelta($fw * self::DM * $ey / 4, $L['M_lateral'], 1e-6, 'eq. 4-15');
        $this->assertEqualsWithDelta($fw * self::DM * $et / 4, $L['M_theta'], 1e-6, 'eq. 4-16');
        $this->assertEqualsWithDelta($fw * self::DM * $ey / (2 * (self::LB - $x)), $L['V_lateral'], 1e-6, 'eq. 4-17 with Lb − x');
        $this->assertEqualsWithDelta(125.0 * M_PI * self::DM ** 2 / 4, $L['thrust'], 1e-6);
        // Phase 1 keys unchanged for a single joint.
        $this->assertEqualsWithDelta(20000.0 / self::N, $L['Ksr'], 1e-9);
        $this->assertEqualsWithDelta(20000.0 / self::N * $x, $L['spring_force'], 1e-9);
        $this->assertArrayNotHasKey('hardware_total', $L);
        $this->assertArrayNotHasKey('hardware_per_component', $L);
        $this->assertArrayNotHasKey('tie_rod_axial', $L);
    }

    public function test_universal_uses_lu_minus_x_and_has_no_hardware(): void
    {
        $fw = 30000.0;
        $x = 0.248;
        $y = 0.788;
        $Lu = 48.0;
        $g = self::geometry($fw);
        $c = self::conditions(new AssemblyGeometry(type: AssemblyType::Universal, Lu: $Lu), xComp: $x, y: $y, P: 100.0);
        $L = self::loads($g, $c, fi: 20000.0);

        $Ku = (3 * $Lu ** 2 - 3 * self::LB * $Lu) / (3 * $Lu ** 2 - 6 * self::LB * $Lu + 4 * self::LB ** 2);
        $ey = $Ku * self::DM * $y / (2 * self::N * ($Lu - self::LB - $x / 2));   // eq. 4-6
        $this->assertEqualsWithDelta($fw * $x / (2 * self::N), $L['Fa'], 1e-6, 'eq. 4-14 with eq. 4-2');
        $this->assertEqualsWithDelta($fw * self::DM * $ey / 4, $L['M_lateral'], 1e-6, 'eq. 4-15');
        $this->assertEqualsWithDelta($fw * self::DM * $ey / (2 * ($Lu - $x)), $L['V_lateral'], 1e-6, 'eq. 4-18 with Lu − x');
        $this->assertEqualsWithDelta(20000.0 * $x / (2 * self::N), $L['spring_force'], 1e-9, 'two bellows in series');
        $this->assertArrayNotHasKey('hardware_total', $L);
        $this->assertArrayNotHasKey('hardware_per_component', $L);
    }

    public function test_tied_universal_tie_rods_carry_thrust(): void
    {
        $fw = 30000.0;
        $Lu = 48.0;
        $g = self::geometry($fw);
        $c = self::conditions(new AssemblyGeometry(type: AssemblyType::TiedUniversal, Lu: $Lu, tieRods: 4), xComp: 0.5, y: 0.788, P: 100.0);
        $L = self::loads($g, $c);

        $T = 100.0 * M_PI * self::DM ** 2 / 4;
        $Ku = (3 * $Lu ** 2 - 3 * self::LB * $Lu) / (3 * $Lu ** 2 - 6 * self::LB * $Lu + 4 * self::LB ** 2);
        // Ruling P2-R3: x = 0.5 between the tie-rod plates compresses the bellows.
        $ey = $Ku * self::DM * 0.788 / (2 * self::N * ($Lu - self::LB - 0.25));
        $tieAxial = $fw * 0.5 / (2 * self::N);
        $this->assertSame(0.0, $L['Fa'], 'anchor axial force: external axial restrained by the tie rods');
        $this->assertEqualsWithDelta($tieAxial, $L['tie_rod_axial'], 1e-6, 'fw·ex reacted by the tie rods');
        $this->assertEqualsWithDelta($fw * self::DM * $ey / (2 * $Lu), $L['V_lateral'], 1e-6, 'eq. 4-18 over Lu');
        $this->assertEqualsWithDelta($T + $tieAxial, $L['hardware_total'], 1e-6);
        $this->assertEqualsWithDelta(($T + $tieAxial) / 4, $L['hardware_per_component'], 1e-6, 'per tie rod');
    }

    public function test_hinged_pins_carry_thrust(): void
    {
        $fw = 30000.0;
        $g = self::geometry($fw);
        $c = self::conditions(new AssemblyGeometry(type: AssemblyType::Hinged), theta: 0.072);
        $L = self::loads($g, $c);

        $T = 150.0 * M_PI * self::DM ** 2 / 4;
        $this->assertEqualsWithDelta($fw * self::DM * (0.072 * self::DM / (2 * self::N)) / 4, $L['M_theta'], 1e-6, 'eq. 4-16');
        $this->assertSame(0.0, $L['Fa']);
        $this->assertSame(0.0, $L['M_lateral']);
        $this->assertSame(0.0, $L['V_lateral']);
        $this->assertEqualsWithDelta($T, $L['hardware_total'], 1e-6);
        $this->assertEqualsWithDelta($T / 2, $L['hardware_per_component'], 1e-6, 'per hinge pin');
    }

    public function test_gimbal_pins_carry_half_thrust_and_resultant_rotation(): void
    {
        $fw = 30000.0;
        $g = self::geometry($fw);
        $c = self::conditions(new AssemblyGeometry(type: AssemblyType::Gimbal, theta2: 0.04), theta: 0.03);
        $L = self::loads($g, $c);

        $T = 150.0 * M_PI * self::DM ** 2 / 4;
        $this->assertEqualsWithDelta($fw * self::DM * (0.05 * self::DM / (2 * self::N)) / 4, $L['M_theta'], 1e-6, 'resultant θ = 0.05');
        $this->assertEqualsWithDelta($T, $L['hardware_total'], 1e-6);
        $this->assertEqualsWithDelta($T / 2, $L['hardware_per_component'], 1e-6, 'per gimbal pin');
    }

    public function test_all_values_are_floats(): void
    {
        $g = self::geometry(30000.0);
        foreach ([
            self::conditions(AssemblyGeometry::single(), xComp: 0.4, y: 0.2),
            self::conditions(new AssemblyGeometry(type: AssemblyType::TiedUniversal, Lu: 48.0, tieRods: 2), y: 0.5),
            self::conditions(new AssemblyGeometry(type: AssemblyType::Hinged), theta: 0.02),
        ] as $c) {
            foreach (self::loads($g, $c) as $k => $v) {
                $this->assertIsFloat($v, $k);
            }
        }
    }

    /** @return array<string, array{AssemblyGeometry, string, ?string}> */
    public static function assemblyNotes(): array
    {
        return [
            'single' => [AssemblyGeometry::single(), 'lateral shear at the compressed length Lb − x.', null],
            'universal' => [new AssemblyGeometry(type: AssemblyType::Universal, Lu: 36.0), 'lateral shear at the compressed length Lu − x.', null],
            'tied universal' => [new AssemblyGeometry(type: AssemblyType::TiedUniversal, Lu: 36.0, tieRods: 3), 'lateral moment per eq. 4-15; lateral shear per eq. 4-18 with the span Lu (the tie rods hold the overall length).', 'carried by the 3 tie rods'],
            'hinged' => [new AssemblyGeometry(type: AssemblyType::Hinged), 'no axial force, lateral moment or lateral shear.', 'carried by the 2 hinge pins'],
            'gimbal' => [new AssemblyGeometry(type: AssemblyType::Gimbal, theta2: 0.01), 'no axial force, lateral moment or lateral shear.', 'carried by the gimbal pins'],
        ];
    }

    #[DataProvider('assemblyNotes')]
    public function test_joint_loads_note_per_assembly(AssemblyGeometry $a, string $loadsTail, ?string $hardware): void
    {
        EjmaData::requireOrSkip();
        $b = Benchmarks::case1();
        $bc = $b['conditions'];
        $c = new DesignConditions(P: $bc->P, xComp: $bc->xComp, xExt: $bc->xExt, y: $bc->y, theta: $bc->theta, requiredCycles: null, assembly: $a);
        $calc = new EjmaCalculator(new MaterialRepository(MaterialRepositoryTest::config()));
        $notes = $calc->calculate(new CalculationRequest($b['geometry'], $c, 70.0, 'aus', null, null,
            FormingCondition::Annealed, FormingMethod::MandrelOrRoll, 1.0, 1.0, 1.0, ['Sab' => 20000.0]))->notes;

        $joint = array_values(array_filter($notes, fn ($n) => str_starts_with($n, 'Joint loads: ')));
        $this->assertCount(1, $joint);
        $this->assertStringEndsWith($loadsTail, $joint[0]);
        $hw = array_values(array_filter($notes, fn ($n) => str_starts_with($n, 'Pressure thrust')));
        if ($hardware === null) {
            $this->assertSame([], $hw);
        } else {
            $this->assertCount(1, $hw);
            $this->assertStringContainsString($hardware, $hw[0]);
        }
    }

    public function test_calculator_merges_loads_and_notes(): void
    {
        EjmaData::requireOrSkip();
        $b = Benchmarks::case1();
        $calc = new EjmaCalculator(new MaterialRepository(MaterialRepositoryTest::config()));
        $result = $calc->calculate(new CalculationRequest($b['geometry'], $b['conditions'], 70.0, 'aus', null, null,
            FormingCondition::Annealed, FormingMethod::MandrelOrRoll, 1.0, 1.0, 1.0, ['Sab' => 20000.0]));

        $v = $result->values;
        foreach (['fw', 'Fa', 'M_lateral', 'M_theta', 'V_lateral', 'Ksr', 'thrust', 'spring_force'] as $k) {
            $this->assertArrayHasKey($k, $v);
        }
        $this->assertArrayNotHasKey('hardware_total', $v);
        $expectedFw = $v['St'] <= 1.5 * $v['prop_Sy'] ? $v['fi'] : 0.67 * $v['fi'];
        $this->assertEqualsWithDelta($expectedFw, $v['fw'], 1e-9);
        $this->assertNotEmpty(array_filter($result->notes, fn ($n) => str_starts_with($n, 'Joint loads: ')));
    }
}
