<?php

namespace App\Ejma\Data;

use App\Ejma\BellowsType;
use App\Ejma\EjmaInputException;
use App\Ejma\FatigueConstants;
use App\Ejma\MaterialFamily;
use App\Ejma\Units;

final readonly class Material
{
    /**
     * @param  list<string>  $roles
     * @param  array<int|string, float|int>  $allowableMpa  °C => MPa
     * @param  array<int|string, float|int>  $modulusGpa  °C => GPa
     * @param  array<int|string, float|int>  $yieldMpa  °C => MPa
     * @param  array<string, array{a: float|int, b: float|int, c: float|int}>  $fatigueConfig
     */
    public function __construct(
        public string $id,
        public string $name,
        public MaterialFamily $family,
        public array $roles,
        public ?float $creepLimitC,
        private array $allowableMpa = [],
        private array $modulusGpa = [],
        private array $yieldMpa = [],
        private array $fatigueConfig = [],
    ) {}

    public function hasData(): bool
    {
        return $this->allowableMpa !== [] && $this->modulusGpa !== [];
    }

    /** Whether the material can serve the role: bellows needs 20 �C modulus and yield rows, collar/reinforcement only Sa and E. */
    public function usableFor(string $role): bool
    {
        if (! in_array($role, $this->roles, true) || ! $this->hasData() || $this->creepLimitC === null) {
            return false;
        }

        return $role !== 'bellows'
            || (self::hasPoint($this->modulusGpa, 20.0) && self::hasPoint($this->yieldMpa, 20.0));
    }

    /** @param  array<int|string, float|int>  $table */
    private static function hasPoint(array $table, float $t): bool
    {
        foreach (array_keys($table) as $k) {
            if (abs((float) $k - $t) < 1e-9) {
                return true;
            }
        }

        return false;
    }

    public function hasYield(): bool
    {
        return $this->yieldMpa !== [];
    }

    public function saPsi(float $tC): float
    {
        return Units::mpaToPsi(self::interpolate($this->allowableMpa, $tC));
    }

    public function ePsi(float $tC): float
    {
        return Units::gpaToPsi(self::interpolate($this->modulusGpa, $tC));
    }

    public function yieldPsi(float $tC): float
    {
        return Units::mpaToPsi(self::interpolate($this->yieldMpa, $tC));
    }

    public function fatigue(BellowsType $type): ?FatigueConstants
    {
        $c = $this->fatigueConfig[$type->value] ?? null;
        if ($c !== null) {
            return new FatigueConstants((float) $c['a'], (float) $c['b'], (float) $c['c']);
        }

        return $this->family === MaterialFamily::Austenitic ? FatigueConstants::ejmaAustenitic($type) : null;
    }

    /** @param  array<int|string, float|int>  $table */
    private static function interpolate(array $table, float $t): float
    {
        $points = [];
        foreach ($table as $k => $v) {
            $points[] = [(float) $k, (float) $v];
        }
        usort($points, fn ($a, $b) => $a[0] <=> $b[0]);

        $n = count($points);
        $eps = 1e-9;
        if ($n === 0 || $t < $points[0][0] - $eps || $t > $points[$n - 1][0] + $eps) {
            throw new EjmaInputException('design_temperature', 'Design temperature is outside the temperature range of the material data');
        }

        for ($i = 0; $i < $n - 1; $i++) {
            [$t0, $v0] = $points[$i];
            [$t1, $v1] = $points[$i + 1];
            if ($t <= $t1 + $eps) {
                return $v0 + ($v1 - $v0) * (min(max($t, $t0), $t1) - $t0) / ($t1 - $t0);
            }
        }

        return $points[$n - 1][1];
    }
}
