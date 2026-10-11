<?php

namespace App\Ejma;

final readonly class CalculationRequest
{
    /** @param  array<string, float>  $overrides */
    public function __construct(
        public BellowsGeometry $geometry,
        public DesignConditions $conditions,
        public float $designTempF,
        public string $bellowsMaterial,
        public ?string $collarMaterial,
        public ?string $reinforcementMaterial,
        public FormingCondition $condition,
        public FormingMethod $method,
        public float $Cwb,
        public float $Cwc,
        public float $Cwr,
        public array $overrides = [],
    ) {}
}
