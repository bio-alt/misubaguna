<?php

namespace Tests\Unit\Ejma;

trait EjmaAssertions
{
    /** Stresses, pressures, cycles: |actual - expected| <= max(0.5 % of expected, 1). */
    protected function assertEjmaValue(float $expected, float $actual, string $label): void
    {
        $tol = max(0.005 * abs($expected), 1.0);
        $this->assertEqualsWithDelta($expected, $actual, $tol, $label);
    }

    /** Dimensionless coefficients: +/-0.001. */
    protected function assertCoefficient(float $expected, float $actual, string $label): void
    {
        $this->assertEqualsWithDelta($expected, $actual, 0.001, $label);
    }

    /** Ratios (e, Kr): within 0.5 % relative. */
    protected function assertRatio(float $expected, float $actual, string $label): void
    {
        $this->assertEqualsWithDelta($expected, $actual, 0.005 * abs($expected), $label);
    }
}
