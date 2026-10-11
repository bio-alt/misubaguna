<?php

namespace App\Ejma;

final class Loads
{
    /** @return array{Ksr: float, thrust: float, spring_force: float} lbf/in, lbf, lbf */
    public static function of(BellowsGeometry $g, DesignConditions $c, Derived $d, CheckResult $result): array
    {
        $Ksr = $result->values['fi'] / $g->N;

        return [
            'Ksr' => $Ksr,
            'thrust' => $c->P * M_PI * $d->Dm ** 2 / 4,
            'spring_force' => $Ksr * max($c->xComp, $c->xExt),
        ];
    }
}
