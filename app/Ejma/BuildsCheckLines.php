<?php

namespace App\Ejma;

/** CheckLine factories shared by the bellows checkers. */
trait BuildsCheckLines
{
    /** value ≤ limit. */
    private static function le(string $key, string $label, string $eq, string $symbol, float $value, float $limit, string $expr): CheckLine
    {
        return new CheckLine($key, $label, $eq, $value, $limit, $expr, $value <= $limit ? LineStatus::Pass : LineStatus::Fail, $symbol);
    }

    /** Nc ≥ required cycles; not evaluated without fatigue constants or a required cycle count. */
    private static function fatigueLine(string $eq, ?float $Nc, ?int $required): CheckLine
    {
        $status = match (true) {
            $Nc === null, $required === null => LineStatus::NotEvaluated,
            $Nc >= $required => LineStatus::Pass,
            default => LineStatus::Fail,
        };

        return new CheckLine('fatigue', 'Fatigue life', $eq, $Nc, $required === null ? null : (float) $required, 'required cycles', $status, 'Nc', CheckLine::GE);
    }
}
