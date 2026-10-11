<?php

/*
 * Material property tables for the EJMA bellows calculator.
 *
 * Source: ASME Boiler and Pressure Vessel Code, Section II Part D (allowable
 * stress, modulus of elasticity, yield strength by temperature).
 *
 * The tables are intentionally empty: the company engineers fill them in.
 * A material with empty tables is hidden from the calculator.
 * Units: temperature in deg C, stress in MPa, modulus in GPa.
 * Bellows materials need a 20 deg C row in the modulus and yield tables (room-temperature
 * reference). Also add a row at or below the lowest expected design temperature
 * (for example 0 deg C or -29 deg C). Interpolation is linear and temperatures outside
 * the table range are rejected.
 *
 * Optional per-material key 'fatigue' => ['unreinforced' => ['a', 'b', 'c'], 'reinforced' => [...],
 * 'toroidal' => [...]] with b and c in psi; austenitic materials default to EJMA Figure 4.20.
 */

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
