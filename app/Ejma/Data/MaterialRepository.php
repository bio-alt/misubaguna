<?php

namespace App\Ejma\Data;

use App\Ejma\MaterialFamily;

final class MaterialRepository
{
    /** @var array<string, Material> */
    private array $materials = [];

    /** @param  array<string, mixed>  $config  the contents of config/ejma/materials.php */
    public function __construct(array $config)
    {
        foreach ($config['materials'] ?? [] as $id => $m) {
            $family = MaterialFamily::tryFrom((string) ($m['family'] ?? 'other'))
                ?? throw new \InvalidArgumentException(sprintf(
                    'EJMA material "%s" has an invalid family "%s" (expected one of: %s).',
                    $id, (string) $m['family'], implode(', ', array_column(MaterialFamily::cases(), 'value')),
                ));

            $this->materials[(string) $id] = new Material(
                id: (string) $id,
                name: $m['name'] ?? (string) $id,
                family: $family,
                roles: array_values($m['roles'] ?? []),
                creepLimitC: isset($m['creep_limit_c']) ? (float) $m['creep_limit_c'] : null,
                allowableMpa: $m['allowable_mpa'] ?? [],
                modulusGpa: $m['modulus_gpa'] ?? [],
                yieldMpa: $m['yield_mpa'] ?? [],
                fatigueConfig: $m['fatigue'] ?? [],
            );
        }
    }

    public function find(string $id): ?Material
    {
        return $this->materials[$id] ?? null;
    }

    /** @return list<Material> materials with data that can serve the role */
    public function availableFor(string $role): array
    {
        return array_values(array_filter(
            $this->materials,
            fn (Material $m) => $m->usableFor($role),
        ));
    }
}
