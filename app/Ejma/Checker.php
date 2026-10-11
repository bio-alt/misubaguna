<?php

namespace App\Ejma;

interface Checker
{
    public function check(BellowsGeometry $g, DesignConditions $c, BellowsProperties $p): CheckResult;
}
