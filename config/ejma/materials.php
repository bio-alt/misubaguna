<?php

/*
 * Material property tables for the EJMA bellows calculator.
 *
 * The values come from ASME BPVC Section II Part D and are kept in the git-ignored file
 * resources/ejma-private/materials.php (the repository is public). Copy that file to every
 * machine and server by hand. Without it every table here is empty, so no material is usable
 * and the calculator page shows its "being prepared" notice.
 *
 * Shape of each entry (units: deg C, MPa for stress and yield, GPa for modulus):
 *   'id' => ['name', 'family' => 'austenitic'|'nickel'|'other', 'roles' => [...],
 *            'creep_limit_c' => float, 'allowable_mpa' => [T => S], 'modulus_gpa' => [T => E],
 *            'yield_mpa' => [T => Sy], optional 'fatigue' => ['unreinforced' => ['a', 'b', 'c'], ...]]
 * Bellows materials need a 20 deg C row in the modulus and yield tables. 'creep_limit_c' is
 * required: a material with a null creep_limit_c is hidden for every role. Interpolation is
 * linear and temperatures outside a table's range are rejected. Austenitic materials default to
 * the EJMA Figure 4.20 fatigue constants; b and c of the optional 'fatigue' key are in psi.
 */

$private = dirname(__DIR__, 2).'/resources/ejma-private/materials.php';
if (is_file($private)) {
    return require $private;
}

$all = ['bellows', 'collar', 'reinforcement'];

$material = fn (string $name, string $family, array $roles) => [
    'name' => $name,
    'family' => $family,
    'roles' => $roles,
    'creep_limit_c' => null,
    'allowable_mpa' => [],
    'modulus_gpa' => [],
    'yield_mpa' => [],
];

return [
    'materials' => [
        'ss304' => $material('Stainless steel 304', 'austenitic', $all),
        'ss304l' => $material('Stainless steel 304L', 'austenitic', $all),
        'ss316' => $material('Stainless steel 316', 'austenitic', $all),
        'ss316l' => $material('Stainless steel 316L', 'austenitic', $all),
        'ss321' => $material('Stainless steel 321', 'austenitic', $all),
        'inconel625' => $material('Inconel 625', 'nickel', $all),
        'incoloy800' => $material('Incoloy 800', 'nickel', $all),
        'incoloy825' => $material('Incoloy 825', 'nickel', $all),
        'sa516_70' => $material('Carbon steel SA-516 Gr 70', 'other', ['collar', 'reinforcement']),
    ],
];
