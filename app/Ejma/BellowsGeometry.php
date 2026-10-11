<?php

namespace App\Ejma;

final readonly class BellowsGeometry
{
    public function __construct(
        public BellowsType $type,
        public float $Db,
        public float $t,
        public int $n,
        public int $N,
        public float $q,
        public ?float $w,
        public ?float $rm,
        public ?float $Lt,
        public float $tc = 0,
        public float $Lc = 0,
        public ?float $Ld = null,
        public float $Ar = 0,
        public float $Atc = 0,
        public float $Zc = 0,
        public int $ng = 0,
        public ?float $r = null,
        public ?float $Dr = null,
        public ?float $DmToroidal = null,
        public ?float $fw = null,
    ) {}
}
