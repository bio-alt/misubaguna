<?php

namespace App\Ejma;

final readonly class FatigueConstants
{
    public function __construct(
        public float $a,
        public float $b,
        public float $c,
    ) {}

    /** EJMA 9th Ed. Figure 4.20 (p104), austenitic stainless steel. */
    public static function ejmaAustenitic(BellowsType $type): self
    {
        return match ($type) {
            BellowsType::Unreinforced => new self(3.4, 54000, 1.86e6),
            BellowsType::Reinforced => new self(2.9, 41800, 5.18e6),
            BellowsType::Toroidal => new self(3.25, 41800, 2.30e6),
        };
    }
}
