<?php

namespace Tests\Unit\Ejma;

use App\Ejma\AngularPressureFactor;
use App\Ejma\BellowsProperties;
use App\Ejma\BellowsType;
use App\Ejma\CheckLine;
use App\Ejma\CheckResult;
use App\Ejma\DesignConditions;
use App\Ejma\Fatigue;
use App\Ejma\FatigueConstants;
use App\Ejma\LineStatus;
use App\Ejma\UnreinforcedBellows;
use App\Ejma\Verdict;
use PHPUnit\Framework\TestCase;
use Tests\EjmaData;
use Tests\Unit\Ejma\Fixtures\Benchmarks;

class FatigueTest extends TestCase
{
    use EjmaAssertions;

    public function test_st_below_b_is_infinite(): void
    {
        $this->assertSame(INF, Fatigue::cycles(50000, FatigueConstants::ejmaAustenitic(BellowsType::Unreinforced)));
        $this->assertSame(INF, Fatigue::cycles(54000, FatigueConstants::ejmaAustenitic(BellowsType::Unreinforced)));
    }

    public function test_missing_constants_returns_null(): void
    {
        $this->assertNull(Fatigue::cycles(120000, null));
    }

    public function test_verdict_review_when_fatigue_not_evaluated(): void
    {
        EjmaData::requireOrSkip();
        $b = Benchmarks::case1();
        $c = $b['conditions'];
        $result = (new UnreinforcedBellows)->check(
            $b['geometry'],
            new DesignConditions($c->P, $c->xComp, $c->xExt, $c->y, $c->theta, 1000),
            self::props($b['properties'], withoutFatigue: true),
        );

        $this->assertSame(Verdict::Review, $result->verdict());
        $this->assertSame('Your design needs engineering review', $result->verdict()->label());
        $this->assertArrayNotHasKey('Nc', $result->values);
        $fatigue = self::line($result, 'fatigue');
        $this->assertSame(LineStatus::NotEvaluated, $fatigue->status);
        $this->assertNull($fatigue->value);
    }

    public function test_verdict_fail_names_first_failure(): void
    {
        EjmaData::requireOrSkip();
        $b = Benchmarks::case1();
        $result = (new UnreinforcedBellows)->check($b['geometry'], $b['conditions'], self::props($b['properties'], Sab: 1000));

        $this->assertSame(Verdict::Fail, $result->verdict());
        $this->assertSame('Your design does not pass EJMA checks', $result->verdict()->label());
        $this->assertSame('hoop_tangent', $result->firstFailure()->key);
    }

    public function test_small_movement_fatigue_passes_with_infinite_cycles(): void
    {
        EjmaData::requireOrSkip();
        $b = Benchmarks::case1();
        $result = (new UnreinforcedBellows)->check(
            $b['geometry'],
            new DesignConditions(P: 40, xComp: 0.01, xExt: 0, y: 0, theta: 0, requiredCycles: 1000),
            $b['properties'],
        );

        $fatigue = self::line($result, 'fatigue');
        $this->assertSame(INF, $fatigue->value);
        $this->assertSame(LineStatus::Pass, $fatigue->status);
        $this->assertNull($fatigue->margin(), 'INF Nc has no finite margin');
        $this->assertSame(Verdict::Pass, $result->verdict());
        $this->assertSame('Your design passes all EJMA checks', $result->verdict()->label());
    }

    public function test_result_round_trips_through_json_with_infinite_nc(): void
    {
        $result = new CheckResult(
            BellowsType::Unreinforced,
            [
                new CheckLine('fatigue', 'Fatigue life', '4-34', INF, 1000.0, 'required cycles', LineStatus::Pass, 'Nc', CheckLine::GE),
                new CheckLine('hoop_tangent', 'Hoop', '4-27', 6000.0, 20000.0, 'Cwb·Sab', LineStatus::Pass, 'S1'),
                new CheckLine('x', 'Not evaluated', '4-35', null, null, '', LineStatus::NotEvaluated),
            ],
            ['Nc' => INF, 'S1' => 6000.0],
            ['a note'],
        );

        $json = json_encode($result->toArray(), JSON_THROW_ON_ERROR);
        $back = CheckResult::fromArray(json_decode($json, true, flags: JSON_THROW_ON_ERROR));

        $this->assertSame(BellowsType::Unreinforced, $back->type);
        $this->assertSame(INF, $back->values['Nc']);
        $this->assertSame(6000.0, $back->values['S1']);
        $this->assertSame(INF, $back->lines[0]->value);
        $this->assertSame(1000.0, $back->lines[0]->limit);
        $this->assertNull($back->lines[2]->value);
        $this->assertSame(LineStatus::NotEvaluated, $back->lines[2]->status);
        $this->assertSame(['a note'], $back->notes);
        $this->assertSame('Nc', $back->lines[0]->symbol);
        $this->assertSame(CheckLine::GE, $back->lines[0]->comparison);
        $this->assertSame('S1', $back->lines[1]->symbol);
        $this->assertSame(CheckLine::LE, $back->lines[1]->comparison);
        $this->assertEquals($result->toArray(), $back->toArray());
    }

    public function test_line_margin_and_with_lines(): void
    {
        $line = new CheckLine('k', 'L', '4-27', 15000.0, 20000.0, 'Sab', LineStatus::Pass);
        $this->assertEqualsWithDelta(0.25, $line->margin(), 1e-12);
        $this->assertNull((new CheckLine('k', 'L', '4-27', null, 20000.0, 'Sab', LineStatus::NotEvaluated))->margin());

        $ge = new CheckLine('fatigue', 'F', '4-34', 1500.0, 1000.0, 'required cycles', LineStatus::Pass, 'Nc', CheckLine::GE);
        $this->assertEqualsWithDelta(0.5, $ge->margin(), 1e-12);

        // A non-positive limit (e.g. ee(max) = 6rm − q < 0 when q > 6rm) has no meaningful margin.
        $this->assertNull((new CheckLine('movement_ee_max', 'E', '§4.2', 0.05, -0.2, '6rm − q', LineStatus::Fail))->margin());
        $this->assertNull((new CheckLine('k', 'L', '4-27', 0.05, 0.0, 'x', LineStatus::Fail))->margin());

        // Stored lines from before symbol/comparison existed default to '' and 'le'.
        $legacy = $line->toArray();
        unset($legacy['symbol'], $legacy['comparison']);
        $old = CheckLine::fromArray($legacy);
        $this->assertSame('', $old->symbol);
        $this->assertSame(CheckLine::LE, $old->comparison);

        $result = new CheckResult(BellowsType::Unreinforced, [$line], []);
        $failed = new CheckLine('f', 'F', '4-30', 2.0, 1.0, 'x', LineStatus::Fail);
        $more = $result->withLines([$failed]);
        $this->assertCount(1, $result->lines);
        $this->assertCount(2, $more->lines);
        $this->assertSame('f', $more->firstFailure()->key);
    }

    public function test_unknown_comparison_is_a_programming_error(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CheckLine('k', 'L', '4-27', 1.0, 2.0, 'x', LineStatus::Pass, 'S1', 'lt');
    }

    public function test_ktheta_helper(): void
    {
        // Concurrent lateral deflection: Ctheta = Ktheta = 1.
        $this->assertSame(1.0, AngularPressureFactor::Ctheta(0.06, deg2rad(5)));
        $this->assertSame(1.0, AngularPressureFactor::Ktheta(0.09, 1.0, 1.0, 0.2));
        // No lateral deflection: Ctheta = 0.1157^theta_m.
        $Ct = AngularPressureFactor::Ctheta(0.0, deg2rad(5));
        $this->assertEqualsWithDelta(0.1157 ** deg2rad(5), $Ct, 1e-12);
        $this->assertSame(1.0, AngularPressureFactor::Ctheta(0.0, 0.0));
        $this->assertEqualsWithDelta(0.1 * $Ct / (0.1 * $Ct + 0.15 * 1.0 * 0.2), AngularPressureFactor::Ktheta(0.1, $Ct, 1.0, 0.2), 1e-12);
        // phi = P / limiting squirm pressure without Ctheta (benchmark-3 form, conservative; p023 text uses 2.25x).
        $this->assertEqualsWithDelta(40 * 144 / (0.34 * M_PI * 12000), AngularPressureFactor::phi(BellowsType::Unreinforced, 40, 12, 1, 12000), 1e-12);
        $this->assertEqualsWithDelta(100 * 64 / (0.3 * M_PI * 24000), AngularPressureFactor::phi(BellowsType::Reinforced, 100, 8, 1, 24000), 1e-12);
        $this->assertEqualsWithDelta(500 * 4 / (0.15 * M_PI * 15000), AngularPressureFactor::phi(BellowsType::Toroidal, 500, 2, 1, 15000), 1e-12);
    }

    private static function line(CheckResult $r, string $key): CheckLine
    {
        foreach ($r->lines as $l) {
            if ($l->key === $key) {
                return $l;
            }
        }
        self::fail("line $key missing");
    }

    private static function props(BellowsProperties $p, ?float $Sab = null, bool $withoutFatigue = false): BellowsProperties
    {
        return new BellowsProperties(
            EbRt: $p->EbRt, EbDt: $p->EbDt, EcDt: $p->EcDt, ErDt: $p->ErDt,
            Sab: $Sab ?? $p->Sab, Sac: $p->Sac, Sar: $p->Sar, Sy: $p->Sy, Cm: $p->Cm,
            fatigue: $withoutFatigue ? null : $p->fatigue,
        );
    }
}
