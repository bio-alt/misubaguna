<?php

namespace Tests\Unit\Ejma;

use App\Ejma\AssemblyGeometry;
use App\Ejma\AssemblyType;
use App\Ejma\Checker;
use App\Ejma\CheckLine;
use App\Ejma\DesignConditions;
use App\Ejma\ReinforcedBellows;
use App\Ejma\ToroidalBellows;
use App\Ejma\UnreinforcedBellows;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Ejma\Fixtures\Benchmarks;

/** §4.4 (p068), ruling P2-R6: St ≤ 1.5·Cm·Sab when the centre-spool dead weight is considered. */
class DeadWeightCheckTest extends TestCase
{
    /** @return array<string, array{array<string, mixed>, Checker}> */
    public static function checkers(): array
    {
        return [
            'unreinforced' => [Benchmarks::case2(), new UnreinforcedBellows],
            'reinforced' => [Benchmarks::case3(), new ReinforcedBellows],
            'toroidal' => [Benchmarks::case4(), new ToroidalBellows],
        ];
    }

    private static function universal(DesignConditions $c, ?float $Wcs): DesignConditions
    {
        return new DesignConditions(P: $c->P, xComp: $c->xComp, xExt: $c->xExt, y: $c->y, theta: $c->theta,
            requiredCycles: $c->requiredCycles,
            assembly: new AssemblyGeometry(type: AssemblyType::Universal, Lu: 36, Wcs: $Wcs, thetaU: deg2rad(30)));
    }

    /** @param  array<string, mixed>  $b */
    #[DataProvider('checkers')]
    public function test_dead_weight_line_present_with_spool_weight(array $b, Checker $checker): void
    {
        $result = $checker->check($b['geometry'], self::universal($b['conditions'], 200), $b['properties']);
        $lines = array_values(array_filter($result->lines, fn (CheckLine $l) => $l->key === 'deadweight_St'));

        $this->assertCount(1, $lines);
        $line = $lines[0];
        $this->assertSame('St', $line->symbol);
        $this->assertSame('§4.4', $line->equation);
        $this->assertSame(CheckLine::LE, $line->comparison);
        $this->assertSame('Total stress range with spool dead weight', $line->label);
        $this->assertSame('1.5·Cm·Sab', $line->limitExpression);
        $this->assertEqualsWithDelta($result->values['St'], $line->value, 1e-9);
        $this->assertEqualsWithDelta(1.5 * 1.5 * 20000, $line->limit, 1e-9);
    }

    /** @param  array<string, mixed>  $b */
    #[DataProvider('checkers')]
    public function test_dead_weight_line_absent_without_spool_weight(array $b, Checker $checker): void
    {
        foreach ([$b['conditions'], self::universal($b['conditions'], null)] as $c) {
            $result = $checker->check($b['geometry'], $c, $b['properties']);
            $this->assertNotContains('deadweight_St', array_map(fn (CheckLine $l) => $l->key, $result->lines));
        }
    }
}
