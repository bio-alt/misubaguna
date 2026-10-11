<?php

namespace App\Ejma;

final class Units
{
    private const BAR_TO_PSI = 14.5037738;

    private const MPA_TO_PSI = 145.037738;

    private const GPA_TO_PSI = 145037.738;

    private const LBF_TO_N = 4.44822162;

    public static function mmToIn(float $mm): float
    {
        return $mm / 25.4;
    }

    public static function inToMm(float $in): float
    {
        return $in * 25.4;
    }

    public static function barToPsi(float $bar): float
    {
        return $bar * self::BAR_TO_PSI;
    }

    public static function psiToBar(float $psi): float
    {
        return $psi / self::BAR_TO_PSI;
    }

    public static function cToF(float $c): float
    {
        return $c * 9.0 / 5.0 + 32.0;
    }

    public static function fToC(float $f): float
    {
        return ($f - 32.0) * 5.0 / 9.0;
    }

    public static function mpaToPsi(float $mpa): float
    {
        return $mpa * self::MPA_TO_PSI;
    }

    public static function gpaToPsi(float $gpa): float
    {
        return $gpa * self::GPA_TO_PSI;
    }

    public static function degToRad(float $deg): float
    {
        return deg2rad($deg);
    }

    public static function radToDeg(float $rad): float
    {
        return rad2deg($rad);
    }

    public static function lbfToN(float $lbf): float
    {
        return $lbf * self::LBF_TO_N;
    }

    public static function nToLbf(float $n): float
    {
        return $n / self::LBF_TO_N;
    }
}
