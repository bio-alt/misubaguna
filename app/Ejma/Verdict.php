<?php

namespace App\Ejma;

enum Verdict: string
{
    case Pass = 'pass';
    case Fail = 'fail';
    case Review = 'review';

    public function label(): string
    {
        return match ($this) {
            self::Pass => 'Your design passes all EJMA checks',
            self::Fail => 'Your design does not pass EJMA checks',
            self::Review => 'Your design needs engineering review',
        };
    }
}
