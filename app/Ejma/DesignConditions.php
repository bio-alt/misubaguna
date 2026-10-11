<?php

namespace App\Ejma;

final readonly class DesignConditions
{
    public function __construct(
        public float $P,
        public float $xComp,
        public float $xExt,
        public float $y,
        public float $theta,
        public ?int $requiredCycles,
        /** Joint-level assembly; defaults to a single expansion joint (Phase 1 behaviour). */
        public AssemblyGeometry $assembly = new AssemblyGeometry,
    ) {}
}
