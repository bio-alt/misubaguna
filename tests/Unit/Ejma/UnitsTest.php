<?php

namespace Tests\Unit\Ejma;

use App\Ejma\Units;
use PHPUnit\Framework\TestCase;

class UnitsTest extends TestCase
{
    public function test_pressure_round_trip(): void
    {
        $psi = Units::barToPsi(10);
        $this->assertEqualsWithDelta(145.037738, $psi, 1e-9);
        $this->assertEqualsWithDelta(10, Units::psiToBar($psi), 1e-9);
    }

    public function test_temperature(): void
    {
        $this->assertSame(752.0, Units::cToF(400));
        $this->assertSame(400.0, Units::fToC(752));
    }

    public function test_modulus(): void
    {
        $this->assertEqualsWithDelta(28282359, Units::gpaToPsi(195), 1);
    }

    public function test_angle(): void
    {
        $this->assertEqualsWithDelta(0.0872665, Units::degToRad(5), 1e-7);
        $this->assertEqualsWithDelta(5, Units::radToDeg(Units::degToRad(5)), 1e-9);
    }
}
