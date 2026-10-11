<?php

namespace Tests\Unit\Ejma;

use App\Ejma\BellowsGeometry;
use App\Ejma\BellowsProperties;
use App\Ejma\BellowsType;
use App\Ejma\DesignConditions;
use App\Ejma\LineStatus;
use App\Ejma\UnreinforcedBellows;
use App\Ejma\Verdict;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Ejma\Fixtures\Benchmarks;

class UnreinforcedBellowsTest extends TestCase
{
    use EjmaAssertions;

    private const COEFFICIENTS = ['Cp', 'Cf', 'Cd'];

    private const RATIOS = ['Kr', 'e'];

    public function test_benchmark_1(): void
    {
        $b = Benchmarks::case1();
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
    }

    public function test_benchmark_1_lines(): void
    {
        $b = Benchmarks::case1();
        $c = $b['conditions'];
        $conditions = new DesignConditions($c->P, $c->xComp, $c->xExt, $c->y, $c->theta, 1000);
        $result = (new UnreinforcedBellows)->check($b['geometry'], $conditions, $b['properties']);

        $keys = array_map(fn ($l) => $l->key, $result->lines);
        $this->assertSame(['hoop_tangent', 'hoop_bellows', 'meridional', 'fatigue', 'squirm_column', 'squirm_inplane'], $keys);
        $this->assertArrayNotHasKey('S1p', $result->values, 'no collar when tc = 0');

        $lines = [];
        foreach ($result->lines as $line) {
            $lines[$line->key] = $line;
            $this->assertSame(LineStatus::Pass, $line->status, $line->key);
        }
        $this->assertSame('4-27', $lines['hoop_tangent']->equation);
        $this->assertEqualsWithDelta(1.5 * 20000, $lines['meridional']->limit, 1e-9);
        $this->assertEqualsWithDelta(1000, $lines['fatigue']->limit, 1e-9);
        $this->assertEqualsWithDelta(40, $lines['squirm_column']->value, 1e-9);
        $this->assertSame(Verdict::Pass, $result->verdict());
        $this->assertNull($result->firstFailure());
    }

    public function test_collar_line_present_when_tc_given(): void
    {
        $b = Benchmarks::case1();
        $g = $b['geometry'];
        $withCollar = new BellowsGeometry(
            type: $g->type, Db: $g->Db, t: $g->t, n: $g->n, N: $g->N, q: $g->q, w: $g->w, rm: $g->rm, Lt: $g->Lt,
            tc: 0.25, Lc: 1,
        );
        $p = $b['properties'];
        $props = new BellowsProperties(
            EbRt: $p->EbRt, EbDt: $p->EbDt, EcDt: 25.8e6, ErDt: 0, Sab: $p->Sab, Sac: $p->Sac, Sar: $p->Sar,
            Sy: $p->Sy, Cm: $p->Cm, fatigue: $p->fatigue,
        );
        $result = (new UnreinforcedBellows)->check($withCollar, $b['conditions'], $props);

        $keys = array_map(fn ($l) => $l->key, $result->lines);
        $this->assertContains('hoop_collar', $keys);
        $this->assertArrayHasKey('S1p', $result->values);
        $this->assertLessThan(6301, $result->values['S1'], 'collar relieves tangent hoop stress');
    }

    public function test_angular_only_reduces_column_squirm_pressure(): void
    {
        $b = Benchmarks::case1();
        $c = $b['conditions'];
        $angularOnly = new DesignConditions($c->P, $c->xComp, $c->xExt, 0, $c->theta, null);
        $result = (new UnreinforcedBellows)->check($b['geometry'], $angularOnly, $b['properties']);

        // Ctheta = 0.1157^theta_m (§1.3 p018) with theta_m = 5° in radians.
        $Ctheta = 0.1157 ** deg2rad(5);
        $this->assertEqualsWithDelta(93.18 * $Ctheta, $result->values['Psc'], 0.1);
        $this->assertLessThan(1.0, $result->values['Ktheta']);
    }
}
