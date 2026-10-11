<?php

namespace App\Ejma;

/** Design-mode input (Phase 3 spec §3.2): service conditions only, no convolution geometry. */
final readonly class DesignRequest
{
    public function __construct(
        /** Bellows inside diameter Db (in). */
        public float $Db,
        public DesignConditions $conditions,
        public float $designTempF,
        public string $bellowsMaterial,
        public FormingCondition $condition,
        public FormingMethod $method,
        public float $Cwb = 1.0,
    ) {}
}
