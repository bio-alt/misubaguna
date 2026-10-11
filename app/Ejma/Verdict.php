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

    /** Result wording for design mode ("Design it for me"): a design was found, or none passed. */
    public function designLabel(): string
    {
        return match ($this) {
            self::Pass => 'A suitable bellows design was found',
            self::Fail, self::Review => 'Your conditions need a custom design by our engineers',
        };
    }
}
