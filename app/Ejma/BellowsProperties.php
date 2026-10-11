<?php

namespace App\Ejma;

final readonly class BellowsProperties
{
    public function __construct(
        public float $EbRt,
        public float $EbDt,
        public float $EcDt,
        public float $ErDt,
        public float $Sab,
        public float $Sac,
        public float $Sar,
        public float $Sy,
        public float $Cm,
        public float $Cwb = 1.0,
        public float $Cwc = 1.0,
        public float $Cwr = 1.0,
        public ?FatigueConstants $fatigue = null,
    ) {}
}
