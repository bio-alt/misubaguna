<?php

namespace App\Ejma;

/**
 * EJMA 9th Ed. (2008) Appendix I: tabulated Cp, Cf, Cd (Tables I1-I3, from
 * Figures 4.16-4.18) and B1, B2, B3 (Table I4, from Figure 4.19), with the
 * linear interpolation of eqs. I-1 to I-5.
 *
 * The tables themselves are copyrighted EJMA data and are not in the public repository:
 * they are loaded lazily from resources/ejma-private/coefficients.php (git-ignored, copied to
 * the server by hand). When that file is missing every lookup throws EjmaDataUnavailable;
 * check available() first.
 *
 * Cp/Cf/Cd tables: rows keyed by M = 1.82 rm / sqrt(Dm tp) (eq. I-2), each row
 * listing the values for the T = 2 rm / w columns of T_AXIS (eq. I-1).
 * B table: rows keyed by x = 6.61 r^2 / (Dm tp), columns [B1, B2, B3].
 */
final class Coefficients
{
    private const T_AXIS = [0.0, 0.05, 0.10, 0.15, 0.20, 0.25, 0.30, 0.35, 0.40, 0.45, 0.50, 0.55, 0.60, 0.65, 0.70, 0.75, 0.80, 0.85, 0.90, 0.95, 1.0];

    private const TABLES = ['cp', 'cf', 'cd', 'b'];

    /** Data file override (tests); null means the default private location. */
    private static ?string $dataFile = null;

    /** @var array{cp: array<string, list<float>>, cf: array<string, list<float>>, cd: array<string, list<float>>, b: array<int, list<float>>}|null */
    private static ?array $tables = null;

    /** Default location of the private tables, resolved without Laravel helpers. */
    public static function defaultDataFile(): string
    {
        return dirname(__DIR__, 2).'/resources/ejma-private/coefficients.php';
    }

    public static function dataFile(): string
    {
        return self::$dataFile ?? self::defaultDataFile();
    }

    /** Test hook: read the tables from $path (a missing path simulates absent data); null restores the default. */
    public static function useDataFile(?string $path): void
    {
        self::$dataFile = $path;
        self::$tables = null;
    }

    /** Whether the private coefficient tables can be loaded. */
    public static function available(): bool
    {
        try {
            self::tables();

            return true;
        } catch (EjmaDataUnavailable) {
            return false;
        }
    }

    /** Rounding slack so values computed exactly on a table edge are not rejected. */
    private const EDGE_EPS = 1e-9;

    /** Eq. I-1: T = 2 rm / w. */
    public static function T(float $rm, float $w): float
    {
        return 2 * $rm / $w;
    }

    /** Eq. I-2: M = 1.82 rm / sqrt(Dm tp). */
    public static function M(float $rm, float $Dm, float $tp): float
    {
        return 1.82 * $rm / sqrt($Dm * $tp);
    }

    public static function cp(float $T, float $M): float
    {
        return self::bilinear(self::tables()['cp'], $T, $M);
    }

    public static function cf(float $T, float $M): float
    {
        return self::bilinear(self::tables()['cf'], $T, $M);
    }

    public static function cd(float $T, float $M): float
    {
        return self::bilinear(self::tables()['cd'], $T, $M);
    }

    public static function b1(float $x): float
    {
        return self::linearB($x, 0);
    }

    public static function b2(float $x): float
    {
        return self::linearB($x, 1);
    }

    public static function b3(float $x): float
    {
        return self::linearB($x, 2);
    }

    /**
     * @return array{cp: array<string, list<float>>, cf: array<string, list<float>>, cd: array<string, list<float>>, b: array<int, list<float>>}
     *
     * @throws EjmaDataUnavailable
     */
    private static function tables(): array
    {
        if (self::$tables !== null) {
            return self::$tables;
        }

        $file = self::dataFile();
        if (! is_file($file)) {
            throw EjmaDataUnavailable::missing($file);
        }
        $data = require $file;
        foreach (self::TABLES as $key) {
            if (! is_array($data) || ! isset($data[$key]) || ! is_array($data[$key]) || count($data[$key]) < 2) {
                throw EjmaDataUnavailable::invalid($file, $key);
            }
        }

        return self::$tables = $data;
    }

    /**
     * Eqs. I-3 to I-5: interpolate along T within the two bracketing M rows
     * (A and B), then along M between A and B.
     *
     * @param  array<string, list<float>>  $table
     */
    private static function bilinear(array $table, float $T, float $M): float
    {
        $mAxis = array_map('floatval', array_keys($table));
        [$it, $ft] = self::bracket(self::T_AXIS, $T);
        [$im, $fm] = self::bracket($mAxis, $M);

        $rows = array_values($table);
        $a = self::lerp($rows[$im][$it], $rows[$im][$it + 1], $ft);
        $b = self::lerp($rows[$im + 1][$it], $rows[$im + 1][$it + 1], $ft);

        return self::lerp($a, $b, $fm);
    }

    private static function linearB(float $x, int $col): float
    {
        $table = self::tables()['b'];
        $axis = array_map('floatval', array_keys($table));
        [$i, $f] = self::bracket($axis, $x);
        $rows = array_values($table);

        return self::lerp($rows[$i][$col], $rows[$i + 1][$col], $f);
    }

    /**
     * Find the lower index i of the interval [axis[i], axis[i+1]] holding $v
     * and the fraction of the way across it.
     *
     * @param  list<float>  $axis  ascending
     * @return array{0: int, 1: float}
     */
    private static function bracket(array $axis, float $v): array
    {
        $last = count($axis) - 1;
        if (! is_finite($v) || $v < $axis[0] - self::EDGE_EPS || $v > $axis[$last] + self::EDGE_EPS) {
            throw new EjmaInputException('geometry', 'Bellows geometry is outside EJMA chart range (Appendix I).');
        }
        $v = min(max($v, $axis[0]), $axis[$last]);

        for ($i = 0; $i < $last - 1; $i++) {
            if ($v <= $axis[$i + 1]) {
                break;
            }
        }

        return [$i, ($v - $axis[$i]) / ($axis[$i + 1] - $axis[$i])];
    }

    private static function lerp(float $lo, float $hi, float $f): float
    {
        return $f === 0.0 ? $lo : ($f === 1.0 ? $hi : $lo + $f * ($hi - $lo));
    }
}
