<?php

namespace App\Ejma\Data;

use App\Ejma\Units;

/** Company tooling for design mode (config/ejma/tooling.php, metric), exposed in inches. */
final class Tooling
{
    /** EJMA's bellows definition allows at most 5 plies. */
    private const PLY_LIMIT = 5;

    /** @var list<FormingProfile> */
    private array $profiles = [];

    /** @var list<float> ascending, inches */
    private array $thicknesses = [];

    private int $maxPlies;

    private int $maxConvolutions;

    private float $tangentLength;

    /** @param  array<string, mixed>  $config  the contents of config/ejma/tooling.php */
    public function __construct(array $config)
    {
        $thicknesses = $config['thicknesses_mm'] ?? [];
        if (! is_array($thicknesses)) {
            throw new \InvalidArgumentException('EJMA tooling "thicknesses_mm" must be a list of thicknesses in mm.');
        }
        foreach ($thicknesses as $i => $t) {
            if (! self::positive($t)) {
                throw new \InvalidArgumentException(sprintf('EJMA tooling "thicknesses_mm[%s]" must be a number greater than zero.', $i));
            }
            $this->thicknesses[] = Units::mmToIn((float) $t);
        }
        $this->thicknesses = array_values(array_unique($this->thicknesses, SORT_REGULAR));
        sort($this->thicknesses);

        $this->maxPlies = self::int($config, 'max_plies', 5, 1, self::PLY_LIMIT);
        $this->maxConvolutions = self::int($config, 'max_convolutions', 30, 1, PHP_INT_MAX);

        $lt = $config['tangent_length_mm'] ?? 25.4;
        if (! self::positive($lt)) {
            throw new \InvalidArgumentException('EJMA tooling "tangent_length_mm" must be a number greater than zero.');
        }
        $this->tangentLength = Units::mmToIn((float) $lt);

        $profiles = $config['profiles'] ?? [];
        if (! is_array($profiles)) {
            throw new \InvalidArgumentException('EJMA tooling "profiles" must be a list of forming profiles.');
        }
        $seen = [];
        foreach ($profiles as $i => $p) {
            $id = is_array($p) ? ($p['id'] ?? null) : null;
            if (! is_string($id) || trim($id) === '') {
                throw new \InvalidArgumentException(sprintf('EJMA tooling profile "profiles[%s]" needs a non-empty string "id".', $i));
            }
            if (isset($seen[$id])) {
                throw new \InvalidArgumentException(sprintf('EJMA tooling profile "%s" is listed more than once (ids must be unique).', $id));
            }
            $seen[$id] = true;

            foreach (['w_mm', 'q_mm', 'rm_mm', 'db_min_mm', 'db_max_mm'] as $k) {
                if (! self::positive($p[$k] ?? null)) {
                    throw new \InvalidArgumentException(sprintf('EJMA tooling profile "%s" needs "%s" as a number greater than zero.', $id, $k));
                }
            }
            if ($p['db_min_mm'] > $p['db_max_mm']) {
                throw new \InvalidArgumentException(sprintf('EJMA tooling profile "%s" has db_min_mm greater than db_max_mm.', $id));
            }
            if (isset($p['name']) && ! is_string($p['name'])) {
                throw new \InvalidArgumentException(sprintf('EJMA tooling profile "%s" has a non-string "name".', $id));
            }

            $this->profiles[] = new FormingProfile(
                id: $id,
                name: $p['name'] ?? $id,
                wIn: Units::mmToIn((float) $p['w_mm']),
                qIn: Units::mmToIn((float) $p['q_mm']),
                rmIn: Units::mmToIn((float) $p['rm_mm']),
                dbMinIn: Units::mmToIn((float) $p['db_min_mm']),
                dbMaxIn: Units::mmToIn((float) $p['db_max_mm']),
            );
        }
    }

    /** Design mode needs at least one forming profile and one sheet thickness. */
    public function isAvailable(): bool
    {
        return $this->profiles !== [] && $this->thicknesses !== [];
    }

    /** @return list<FormingProfile> profiles whose Db range contains Db (in), in config order */
    public function profilesFor(float $DbIn): array
    {
        return array_values(array_filter($this->profiles, fn (FormingProfile $p) => $p->fits($DbIn)));
    }

    /** @return list<float> available ply thicknesses (in), ascending */
    public function thicknessesIn(): array
    {
        return $this->thicknesses;
    }

    public function maxPlies(): int
    {
        return $this->maxPlies;
    }

    public function maxConvolutions(): int
    {
        return $this->maxConvolutions;
    }

    public function tangentLengthIn(): float
    {
        return $this->tangentLength;
    }

    private static function positive(mixed $v): bool
    {
        return (is_int($v) || is_float($v)) && is_finite((float) $v) && $v > 0;
    }

    /** @param  array<string, mixed>  $config */
    private static function int(array $config, string $key, int $default, int $min, int $max): int
    {
        $v = $config[$key] ?? $default;
        if (! is_int($v) || $v < $min || $v > $max) {
            throw new \InvalidArgumentException($max === PHP_INT_MAX
                ? sprintf('EJMA tooling "%s" must be an integer of at least %d.', $key, $min)
                : sprintf('EJMA tooling "%s" must be an integer from %d to %d.', $key, $min, $max));
        }

        return $v;
    }
}
