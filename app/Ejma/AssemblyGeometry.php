<?php

namespace App\Ejma;

/**
 * Assembly description for joint-level movement distribution (Phase 2 spec §3).
 * Lengths in inches, weight in lbf, angles in radians.
 */
final readonly class AssemblyGeometry
{
    public function __construct(
        public AssemblyType $type = AssemblyType::Single,
        /** Universal: distance between the outermost convolution ends (Figure 4.1, p067). */
        public ?float $Lu = null,
        /** Tied universal: number of tie rods (at least 2). */
        public int $tieRods = 0,
        /** Gimbal: rotation in the second plane (rad), combined vectorially with θ (§4.2, p065). */
        public float $theta2 = 0.0,
        /** Universal: centre-spool dead weight Wcs (lbf), §4.4 eqs. 4-12/4-13 (p068); null = not considered. */
        public ?float $Wcs = null,
        /** Universal: centre-spool centreline angle θu from horizontal (rad), §4.4 (p068). */
        public float $thetaU = 0.0,
    ) {}

    public static function single(): self
    {
        return new self;
    }

    /** Hinge pins (hinged 2) or gimbal pins (4); other assemblies have none. */
    public function pins(): int
    {
        return match ($this->type) {
            AssemblyType::Hinged => 2,
            AssemblyType::Gimbal => 4,
            default => 0,
        };
    }
}
