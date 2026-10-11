<?php

/*
 * Material property tables for the EJMA bellows calculator.
 *
 * Source: ASME Boiler and Pressure Vessel Code, Section II Part D (allowable
 * stress, modulus of elasticity, yield strength by temperature).
 *
 * The company engineers maintain these tables.
 * A material with empty tables is hidden from the calculator.
 * 'creep_limit_c' is required: set it to the temperature (deg C) above which the material
 * is in the creep range. A material with a null creep_limit_c is hidden for every role.
 * Units: temperature in deg C, stress in MPa, modulus in GPa.
 * Bellows materials need a 20 deg C row in the modulus and yield tables (room-temperature
 * reference). Also add a row at or below the lowest expected design temperature
 * (for example 0 deg C or -29 deg C). Interpolation is linear and temperatures outside
 * the table range are rejected.
 *
 * Optional per-material key 'fatigue' => ['unreinforced' => ['a', 'b', 'c'], 'reinforced' => [...],
 * 'toroidal' => [...]] with b and c in psi; austenitic materials default to EJMA Figure 4.20.
 */

/*
 * PLACEHOLDER DEFAULTS (2026-10-11): approximate typical ASME II-D values, entered for
 * testing only. They are rounded and NOT verified against the Code edition the company
 * designs to. The company engineers must check every row before results are relied on.
 */

$all = ['bellows', 'collar', 'reinforcement'];

$material = fn (string $name, string $family, array $roles, float $creepC, array $sa, array $e, array $sy) => [
    'name' => $name,
    'family' => $family,
    'roles' => $roles,
    'creep_limit_c' => $creepC,
    'allowable_mpa' => $sa,
    'modulus_gpa' => $e,
    'yield_mpa' => $sy,
];

// Elastic modulus tables (GPa), shared by family.
$eAustenitic = [-29 => 198, 20 => 195, 100 => 189, 200 => 183, 300 => 176, 400 => 169, 450 => 165];
$eNickel = [-29 => 210, 20 => 207, 100 => 203, 200 => 198, 300 => 192, 400 => 186, 500 => 180, 550 => 177];
$eIncoloy = [-29 => 200, 20 => 197, 100 => 192, 200 => 187, 300 => 181, 400 => 175, 500 => 169, 550 => 166];
$eCarbon = [-29 => 205, 20 => 202, 100 => 198, 200 => 192, 300 => 185, 375 => 179];

return [
    'materials' => [
        'ss304' => $material('Stainless steel 304', 'austenitic', $all, 425,
            [-29 => 138, 20 => 138, 100 => 138, 150 => 128, 200 => 122, 250 => 117, 300 => 114, 350 => 111, 400 => 108, 450 => 105],
            $eAustenitic,
            [-29 => 207, 20 => 207, 100 => 170, 200 => 144, 300 => 130, 400 => 121, 450 => 118]),
        'ss304l' => $material('Stainless steel 304L', 'austenitic', $all, 425,
            [-29 => 115, 20 => 115, 100 => 115, 150 => 108, 200 => 101, 250 => 96, 300 => 92, 350 => 89, 400 => 86, 450 => 84],
            $eAustenitic,
            [-29 => 172, 20 => 172, 100 => 145, 200 => 122, 300 => 110, 400 => 102, 450 => 99]),
        'ss316' => $material('Stainless steel 316', 'austenitic', $all, 425,
            [-29 => 138, 20 => 138, 100 => 138, 150 => 134, 200 => 129, 250 => 125, 300 => 122, 350 => 119, 400 => 117, 450 => 115],
            $eAustenitic,
            [-29 => 207, 20 => 207, 100 => 177, 200 => 153, 300 => 138, 400 => 128, 450 => 125]),
        'ss316l' => $material('Stainless steel 316L', 'austenitic', $all, 425,
            [-29 => 115, 20 => 115, 100 => 115, 150 => 107, 200 => 100, 250 => 95, 300 => 91, 350 => 88, 400 => 86, 450 => 84],
            $eAustenitic,
            [-29 => 172, 20 => 172, 100 => 145, 200 => 124, 300 => 111, 400 => 103, 450 => 100]),
        'ss321' => $material('Stainless steel 321', 'austenitic', $all, 425,
            [-29 => 138, 20 => 138, 100 => 138, 150 => 136, 200 => 133, 250 => 129, 300 => 126, 350 => 123, 400 => 120, 450 => 118],
            $eAustenitic,
            [-29 => 207, 20 => 207, 100 => 182, 200 => 160, 300 => 145, 400 => 135, 450 => 132]),
        'inconel625' => $material('Inconel 625', 'nickel', $all, 540,
            [-29 => 241, 20 => 241, 100 => 241, 200 => 236, 300 => 229, 400 => 222, 500 => 216, 550 => 213],
            $eNickel,
            [-29 => 414, 20 => 414, 100 => 390, 200 => 370, 300 => 355, 400 => 345, 500 => 338, 550 => 335]),
        'incoloy800' => $material('Incoloy 800', 'nickel', $all, 540,
            [-29 => 133, 20 => 133, 100 => 126, 200 => 117, 300 => 112, 400 => 107, 500 => 103, 550 => 101],
            $eIncoloy,
            [-29 => 207, 20 => 207, 100 => 185, 200 => 168, 300 => 158, 400 => 150, 500 => 144, 550 => 141]),
        'incoloy825' => $material('Incoloy 825', 'nickel', $all, 540,
            [-29 => 161, 20 => 161, 100 => 161, 200 => 158, 300 => 153, 400 => 148, 500 => 143, 550 => 141],
            $eIncoloy,
            [-29 => 241, 20 => 241, 100 => 220, 200 => 203, 300 => 192, 400 => 184, 500 => 178, 550 => 175]),
        'sa516_70' => $material('Carbon steel SA-516 Gr 70', 'other', ['collar', 'reinforcement'], 370,
            [-29 => 138, 20 => 138, 100 => 138, 200 => 138, 300 => 131, 350 => 125, 375 => 118],
            $eCarbon,
            [-29 => 260, 20 => 260, 100 => 238, 200 => 219, 300 => 196, 375 => 184]),
    ],
];
