<?php

namespace Tests\Unit\Ejma;

use App\Ejma\BellowsGeometry;
use App\Ejma\BellowsProperties;
use App\Ejma\CheckLine;
use App\Ejma\DesignConditions;
use App\Ejma\EjmaInputException;
use App\Ejma\LineStatus;
use App\Ejma\UnreinforcedBellows;
use App\Ejma\Verdict;
use PHPUnit\Framework\TestCase;
use Tests\RequiresEjmaData;
use Tests\Unit\Ejma\Fixtures\Benchmarks;

class UnreinforcedBellowsTest extends TestCase
{
    use RequiresEjmaData;

    public function test_benchmark_1_lines(): void
    {
        $b = Benchmarks::case1();
        $c = $b['conditions'];
        $conditions = new DesignConditions($c->P, $c->xComp, $c->xExt, $c->y, $c->theta, 1000);
        $result = (new UnreinforcedBellows)->check($b['geometry'], $conditions, $b['properties']);

        $keys = array_map(fn ($l) => $l->key, $result->lines);
        $this->assertSame([
            'hoop_tangent', 'hoop_bellows', 'meridional', 'fatigue', 'squirm_column', 'squirm_inplane',
            'movement_ec_max', 'movement_ee_max', 'instability_axial',
        ], $keys);
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

        $symbols = array_map(fn ($l) => $l->symbol, $result->lines);
        $this->assertSame(['S1', 'S2', 'S3+S4', 'Nc', 'Psc', 'Psi', 'ec', 'ee', 'ex'], $symbols);
        $this->assertSame(CheckLine::GE, $lines['fatigue']->comparison);
        $this->assertSame(CheckLine::LE, $lines['hoop_tangent']->comparison);
        // GE line: margin (Nc − required)/required; LE line: (limit − value)/limit. Both > 0 on a pass.
        $this->assertEqualsWithDelta(($result->values['Nc'] - 1000) / 1000, $lines['fatigue']->margin(), 1e-9);
        $this->assertGreaterThan(0.0, $lines['fatigue']->margin());
        $meridional = $result->values['S3'] + $result->values['S4'];
        $this->assertEqualsWithDelta((30000 - $meridional) / 30000, $lines['meridional']->margin(), 1e-9);
        $this->assertGreaterThan(0.0, $lines['meridional']->margin());
    }

    public function test_missing_w_or_rm_and_negative_pressure_are_field_errors(): void
    {
        $b = Benchmarks::case1();
        $g = $b['geometry'];
        $c = $b['conditions'];
        $cases = [
            'w' => [new BellowsGeometry(type: $g->type, Db: $g->Db, t: $g->t, n: $g->n, N: $g->N, q: $g->q, w: null, rm: $g->rm, Lt: $g->Lt), $c],
            'rm' => [new BellowsGeometry(type: $g->type, Db: $g->Db, t: $g->t, n: $g->n, N: $g->N, q: $g->q, w: $g->w, rm: null, Lt: $g->Lt), $c],
            'design_pressure' => [$g, new DesignConditions(-1, $c->xComp, $c->xExt, $c->y, $c->theta, null)],
        ];
        foreach ($cases as $field => [$geometry, $conditions]) {
            try {
                (new UnreinforcedBellows)->check($geometry, $conditions, $b['properties']);
                $this->fail("Expected EjmaInputException on $field");
            } catch (EjmaInputException $e) {
                $this->assertSame($field, $e->field);
            }
        }
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
        $this->assertSame("S1'", $result->lines[array_search('hoop_collar', $keys, true)]->symbol);
        $this->assertArrayHasKey('S1p', $result->values);
        $without = (new UnreinforcedBellows)->check($g, $b['conditions'], $props);
        $this->assertLessThan($without->values['S1'], $result->values['S1'], 'collar relieves tangent hoop stress');
    }

    public function test_angular_only_reduces_column_squirm_pressure(): void
    {
        $b = Benchmarks::case1();
        $c = $b['conditions'];
        $angularOnly = new DesignConditions($c->P, $c->xComp, $c->xExt, 0, $c->theta, null);
        $result = (new UnreinforcedBellows)->check($b['geometry'], $angularOnly, $b['properties']);

        // Ctheta = 0.1157^theta_m (§1.3 p018) with theta_m = 5° in radians; with concurrent
        // lateral (the benchmark conditions) Ctheta = 1.
        $Ctheta = 0.1157 ** deg2rad(5);
        $concurrent = (new UnreinforcedBellows)->check($b['geometry'], $c, $b['properties']);
        $this->assertSame(1.0, $concurrent->values['Ctheta']);
        $this->assertEqualsWithDelta($concurrent->values['Psc'] * $Ctheta, $result->values['Psc'], 1e-9);
        $this->assertLessThan(1.0, $result->values['Ktheta']);
    }
}
