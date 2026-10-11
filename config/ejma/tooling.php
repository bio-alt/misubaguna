<?php

/*
 * Company tooling for the EJMA calculator's "Design it for me" mode.
 *
 * The design search proposes the smallest unreinforced U-shaped bellows (no reinforcing
 * collar) that can be made with this tooling and passes every EJMA check.
 * Design mode is offered only when at least one profile and one thickness are listed.
 *
 * The lists are intentionally empty: the company engineers fill them in.
 * Units: millimetres.
 *
 * - thicknesses_mm:    available sheet thicknesses (one ply), e.g. [0.3, 0.5, 0.8].
 * - max_plies:         most plies per bellows (1 to 5; EJMA bellows have at most 5).
 * - max_convolutions:  most convolutions per bellows.
 * - tangent_length_mm: straight tangent length Lt used for every proposed design.
 * - profiles:          forming dies, each
 *                      ['id' => 'u36', 'name' => 'U-36 die', 'w_mm' => 36.6, 'q_mm' => 25.4,
 *                       'rm_mm' => 6.35, 'db_min_mm' => 500, 'db_max_mm' => 700]
 *                      w = convolution height, q = pitch, rm = mean convolution radius,
 *                      db_min/db_max = bellows inside-diameter range the die can form.
 *                      Ids must be unique; ties between equal designs go to the lower id.
 */

return [
    'thicknesses_mm' => [],
    'max_plies' => 5,
    'max_convolutions' => 30,
    'tangent_length_mm' => 25.4,
    'profiles' => [],
];
