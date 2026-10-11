<?php

namespace App\Ejma;

enum FormingMethod: string
{
    case MandrelOrRoll = 'mandrel_roll';
    case HydraulicElastomericPneumatic = 'hydraulic';

    public function kf(): float
    {
        return match ($this) {
            self::MandrelOrRoll => 1.0,
            self::HydraulicElastomericPneumatic => 0.6,
        };
    }
}
