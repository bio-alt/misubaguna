<?php

namespace App\Ejma;

/**
 * JSON-safe float encoding: json_encode rejects INF, so ±INF is stored as "INF" / "-INF".
 *
 * @internal
 */
final class JsonFloat
{
    public static function encode(?float $v): float|string|null
    {
        if ($v === null || is_finite($v)) {
            return $v;
        }
        if (is_nan($v)) {
            throw new \InvalidArgumentException('NAN cannot be stored in a check result.');
        }

        return $v > 0 ? 'INF' : '-INF';
    }

    public static function decode(mixed $v): ?float
    {
        return match (true) {
            $v === null => null,
            $v === 'INF' => INF,
            $v === '-INF' => -INF,
            is_int($v) || is_float($v) => (float) $v,
            default => throw new \InvalidArgumentException('Invalid stored float: '.var_export($v, true)),
        };
    }
}
