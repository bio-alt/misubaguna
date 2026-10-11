<?php

namespace App\Ejma;

final class Fatigue
{
    /**
     * Eq. 4-34: Nc = (c / (St − b))^a (EJMA 9th Ed. p092, constants per Figure 4.20).
     * Null when no fatigue constants are known; INF when St ≤ b (no fatigue limit).
     */
    public static function cycles(float $St, ?FatigueConstants $k): ?float
    {
        if ($k === null) {
            return null;
        }
        if ($St <= $k->b) {
            return INF;
        }

        return ($k->c / ($St - $k->b)) ** $k->a;
    }
}
