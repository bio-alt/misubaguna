<?php

namespace Tests\Unit\Ejma;

use App\Ejma\BellowsType;
use App\Ejma\Data\MaterialRepository;
use App\Ejma\Derived;
use App\Ejma\FormingCondition;
use App\Ejma\FormingMethod;
use App\Ejma\MaterialFamily;
use App\Ejma\PropertyResolver;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Ejma\Fixtures\Benchmarks;

class PropertyResolverTest extends TestCase
{
    public function test_annealed_cm_is_1_5(): void
    {
        $cm = PropertyResolver::cm(FormingCondition::Annealed, FormingMethod::MandrelOrRoll, MaterialFamily::Austenitic, 20.0);
        $this->assertSame(1.5, $cm);
    }

    public function test_as_formed_cm_clamped_to_3(): void
    {
        $cm = PropertyResolver::cm(FormingCondition::AsFormed, FormingMethod::MandrelOrRoll, MaterialFamily::Austenitic, 20.0);
        $this->assertSame(3.0, $cm);
        $this->assertSame(1.5, PropertyResolver::cm(FormingCondition::AsFormed, FormingMethod::MandrelOrRoll, MaterialFamily::Austenitic, 0.0));
    }

    public function test_null_epsilon_gives_1_5(): void
    {
        $this->assertSame(1.5, PropertyResolver::cm(FormingCondition::AsFormed, FormingMethod::MandrelOrRoll, MaterialFamily::Nickel, null));
    }

    private function props(array $overrides)
    {
        $b = Benchmarks::case1();
        $d = Derived::of($b['geometry'], $b['conditions']);
        $m = (new MaterialRepository(MaterialRepositoryTest::config()))->find('aus');

        return PropertyResolver::resolve($m, null, null, FormingCondition::Annealed, FormingMethod::MandrelOrRoll, $d, 70.0, $overrides, 1.0, 1.0, 1.0, BellowsType::Unreinforced);
    }

    public function test_computed_sy(): void
    {
        $p = $this->props([]);
        $tC = (70 - 32) * 5 / 9;
        $syh = (205 + (150 - 205) * ($tC - 20) / 280) * 145.037738;
        $this->assertEqualsWithDelta(0.67 * 1.5 * 205 * 145.037738 * $syh / (205 * 145.037738), $p->Sy, 0.01);
    }

    public function test_overrides_win(): void
    {
        $p = $this->props(['Sab' => 11111.0, 'EbRt' => 2.0e7, 'EbDt' => 1.9e7, 'Sy' => 33333.0]);
        $this->assertSame(11111.0, $p->Sab);
        $this->assertSame(2.0e7, $p->EbRt);
        $this->assertSame(1.9e7, $p->EbDt);
        $this->assertSame(33333.0, $p->Sy);
    }
}
