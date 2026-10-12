<?php

/*
 * Company tooling for the EJMA calculator's "Design it for me" mode.
 *
 * The design search proposes the smallest unreinforced U-shaped bellows (no reinforcing
 * collar) that can be made with this tooling and passes every EJMA check.
 * Design mode is offered only when at least one profile and one thickness are listed.
 *
 * PLACEHOLDER DEFAULTS (2026-10-11): typical industry U-bellows dies and sheet gauges,
 * entered for testing only. The company engineers must replace them with the real
 * tooling before relying on proposed designs.
 *
 * Units: millimetres.
 *
 * - thicknesses_mm:    available sheet thicknesses (one ply), e.g. [0.3, 0.5, 0.8].
 * - max_plies:         most plies per bellows (1 to 5; EJMA bellows have at most 5).
 * - max_convolutions:  most convolutions per bellows (1 to 200).
 * - tangent_length_mm: straight tangent length Lt used for every proposed design.
 * - profiles:          forming dies, each
 *                      ['id' => 'u36', 'name' => 'U-36 die', 'w_mm' => 36.6, 'q_mm' => 25.4,
 *                       'rm_mm' => 6.35, 'db_min_mm' => 500, 'db_max_mm' => 700]
 *                      w = convolution height, q = pitch, rm = mean convolution radius,
 *                      db_min/db_max = bellows inside-diameter range the die can form.
 *                      Ids must be unique; ties between equal designs go to the lower id.
 */

return [
    'thicknesses_mm' => [0.3, 0.4, 0.5, 0.6, 0.8, 1.0, 1.2, 1.5],
    'max_plies' => 2,
    'max_convolutions' => 30,
    'tangent_length_mm' => 25.4,
    'profiles' => [
        ['id' => 'u10', 'name' => 'U-10 die (DN40–DN125)', 'w_mm' => 10.0, 'q_mm' => 10.0, 'rm_mm' => 2.5, 'db_min_mm' => 40, 'db_max_mm' => 150],
        ['id' => 'u15', 'name' => 'U-15 die (DN100–DN300)', 'w_mm' => 15.0, 'q_mm' => 14.0, 'rm_mm' => 3.5, 'db_min_mm' => 100, 'db_max_mm' => 320],
        ['id' => 'u25', 'name' => 'U-25 die (DN250–DN600)', 'w_mm' => 25.0, 'q_mm' => 22.0, 'rm_mm' => 5.5, 'db_min_mm' => 250, 'db_max_mm' => 620],
        ['id' => 'u36', 'name' => 'U-36 die (DN500–DN1000)', 'w_mm' => 36.6, 'q_mm' => 25.4, 'rm_mm' => 6.35, 'db_min_mm' => 500, 'db_max_mm' => 1020],
        ['id' => 'u45', 'name' => 'U-45 die (DN800–DN1600)', 'w_mm' => 45.0, 'q_mm' => 36.0, 'rm_mm' => 9.0, 'db_min_mm' => 800, 'db_max_mm' => 1620],
        ['id' => 'u55', 'name' => 'U-55 die (DN1200–DN2500)', 'w_mm' => 55.0, 'q_mm' => 45.0, 'rm_mm' => 11.0, 'db_min_mm' => 1200, 'db_max_mm' => 2520],
    ],
];
