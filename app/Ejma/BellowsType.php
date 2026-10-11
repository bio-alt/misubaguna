<?php

namespace App\Ejma;

enum BellowsType: string
{
    case Unreinforced = 'unreinforced';
    case Reinforced = 'reinforced';
    case Toroidal = 'toroidal';
}
