<?php

namespace Tests\Unit\Ejma;

use App\Ejma\AssemblyGeometry;
use App\Ejma\AssemblyType;
use App\Ejma\CheckLine;
use App\Ejma\CheckResult;
use App\Ejma\Derived;
use App\Ejma\DesignConditions;
use App\Ejma\LineStatus;
use App\Ejma\Movement;
use App\Ejma\ReinforcedBellows;
use App\Ejma\ToroidalBellows;
use App\Ejma\UnreinforcedBellows;
use App\Ejma\Verdict;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Ejma\Fixtures\Benchmarks;

/** Movement limits §4.2 (p066) and axial instability §4.7 (p073). */
class MovementLimitChecksTest extends TestCase
{
    /** @return array<string, CheckLine> */
    private static function lines(CheckResult $r): array
    {
        $out = [];
        foreach ($r->lines as $l) {
            $out[$l->key] = $l;
        }

        return $out;
    }

    public function test_new_limit_lines_present_for_single(): void
    {
        $b = Benchmarks::case1();
        $result = (new UnreinforcedBellows)->check($b['geometry'], $b['conditions'], $b['properties']);
        $lines = self::lines($result);

        // ec(max) = q − 2rm − nt = 1 − 0.5 − 0.06; ee(max) = 6rm − q = 1.5 − 1.
        $ec = $lines['movement_ec_max'];
        $this->assertSame('ec', $ec->symbol);
        $this->assertSame(CheckLine::LE, $ec->comparison);
        $this->assertEqualsWithDelta(0.44, $ec->limit, 1e-12);
        // Eq. 4-8 compression position: e − ee, ee = 0.5/12.
        $this->assertEqualsWithDelta($result->values['e'] - 0.5 / 12, $ec->value, 1e-12);
        $this->assertSame(LineStatus::Pass, $ec->status);

        $ee = $lines['movement_ee_max'];
        $this->assertSame('ee', $ee->symbol);
        $this->assertEqualsWithDelta(0.5, $ee->limit, 1e-12);
        // Ruling P2-R4: eq. 4-9 at the compression position, max(ey + eθ − |ex|, eθ/Kθ − |ex|, ex ext).
        $m = Movement::of($b['geometry'], $b['conditions'], Derived::of($b['geometry'], $b['conditions']), $result->values['Ktheta'], $result->values['fi']);
        $expected = max($m->ey + $m->etheta - $m->exComp, $m->etheta / $result->values['Ktheta'] - $m->exComp, 0.5 / 12);
        $this->assertEqualsWithDelta($expected, $ee->value, 1e-12);
        $this->assertGreaterThan(0.5 / 12, $ee->value, 'lateral governs the extended side');
        $this->assertSame(LineStatus::Pass, $ee->status);

        // §4.7: ex ≤ 1.25·Dm²/(N²q), Dm = 25.5, N = 12.
        $ax = $lines['instability_axial'];
        $this->assertSame('ex', $ax->symbol);
        $this->assertEqualsWithDelta(1.25 * 25.5 ** 2 / (12 ** 2 * 1.0), $ax->limit, 1e-9);
        $this->assertEqualsWithDelta(1 / 12, $ax->value, 1e-12);
        $this->assertSame(LineStatus::Pass, $ax->status);
        $this->assertSame(1.5, $result->values['Ku']);
        $this->assertSame([], $result->notes, 'no assembly note for a single joint');
    }

    public function test_reinforced_has_all_three_lines(): void
    {
        $b = Benchmarks::case3();
        $lines = self::lines((new ReinforcedBellows)->check($b['geometry'], $b['conditions'], $b['properties']));

        $this->assertEqualsWithDelta(0.44, $lines['movement_ec_max']->limit, 1e-12);
        $this->assertEqualsWithDelta(0.5, $lines['movement_ee_max']->limit, 1e-12);
        $this->assertEqualsWithDelta(1.25 * 25.5 ** 2 / 64, $lines['instability_axial']->limit, 1e-9);
    }

    public function test_toroidal_has_only_instability_line(): void
    {
        $b = Benchmarks::case4();
        $lines = self::lines((new ToroidalBellows)->check($b['geometry'], $b['conditions'], $b['properties']));

        $this->assertArrayNotHasKey('movement_ec_max', $lines);
        $this->assertArrayNotHasKey('movement_ee_max', $lines);
        // Dm = 27.496 (given), N = 2, q = 3.4625; ex = 0.75/2.
        $this->assertEqualsWithDelta(1.25 * 27.496 ** 2 / (4 * 3.4625), $lines['instability_axial']->limit, 1e-9);
        $this->assertEqualsWithDelta(0.375, $lines['instability_axial']->value, 1e-12);
        $this->assertSame(LineStatus::Pass, $lines['instability_axial']->status);
    }

    public function test_universal_instability_uses_total_convolutions(): void
    {
        $b = Benchmarks::case2();
        $lines = self::lines((new UnreinforcedBellows)->check($b['geometry'], $b['conditions'], $b['properties']));

        // N = 2 × 4 interconnected convolutions; ex = 1/(2·4) per eq. 4-2.
        $this->assertEqualsWithDelta(1.25 * 25.5 ** 2 / (8 ** 2 * 1.0), $lines['instability_axial']->limit, 1e-9);
        $this->assertEqualsWithDelta(0.125, $lines['instability_axial']->value, 1e-12);
    }

    public function test_excess_compression_fails_ec_limit(): void
    {
        $b = Benchmarks::case1();
        // ec = 6/12 = 0.5 > 0.44.
        $c = new DesignConditions(P: 40, xComp: 6, xExt: 0, y: 0, theta: 0, requiredCycles: null);
        $result = (new UnreinforcedBellows)->check($b['geometry'], $c, $b['properties']);
        $lines = self::lines($result);

        $this->assertSame(LineStatus::Fail, $lines['movement_ec_max']->status);
        $this->assertSame(Verdict::Fail, $result->verdict());
    }

    public function test_excess_extension_fails_ee_limit(): void
    {
        $b = Benchmarks::case1();
        // ee = 7.2/12 = 0.6 > 0.5.
        $c = new DesignConditions(P: 40, xComp: 0, xExt: 7.2, y: 0, theta: 0, requiredCycles: null);
        $lines = self::lines((new UnreinforcedBellows)->check($b['geometry'], $c, $b['properties']));

        $this->assertSame(LineStatus::Fail, $lines['movement_ee_max']->status);
    }

    public function test_tied_universal_note_and_internal_axial(): void
    {
        $b = Benchmarks::case2();
        $c = new DesignConditions(P: 100, xComp: 1, xExt: 0.5, y: 2, theta: 0, requiredCycles: null,
            assembly: new AssemblyGeometry(type: AssemblyType::TiedUniversal, Lu: 36, tieRods: 2));
        $result = (new UnreinforcedBellows)->check($b['geometry'], $c, $b['properties']);

        // Ruling P2-R3: x is the growth between the tie-rod plates, ex = x/(2N) = 1/8.
        $this->assertEqualsWithDelta(1 / 8, self::lines($result)['instability_axial']->value, 1e-12);
        $this->assertContains('Assembly: Tied universal (2 bellows; checks apply to each bellows)', $result->notes);
    }
}
