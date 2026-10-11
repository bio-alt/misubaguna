<?php

namespace App\Ejma\Data;

/** A forming die from config/ejma/tooling.php, converted to inches. */
final readonly class FormingProfile
{
    public function __construct(
        public string $id,
        public string $name,
        /** Convolution height w (in). */
        public float $wIn,
        /** Convolution pitch q (in). */
        public float $qIn,
        /** Mean convolution radius rm (in). */
        public float $rmIn,
        /** Smallest bellows inside diameter Db the die can form (in). */
        public float $dbMinIn,
        /** Largest bellows inside diameter Db the die can form (in). */
        public float $dbMaxIn,
    ) {}

    /** Whether the die can form a bellows of inside diameter Db (in); bounds inclusive. */
    public function fits(float $DbIn): bool
    {
        $eps = 1e-9 * max(1.0, abs($DbIn));

        return $DbIn >= $this->dbMinIn - $eps && $DbIn <= $this->dbMaxIn + $eps;
    }
}
