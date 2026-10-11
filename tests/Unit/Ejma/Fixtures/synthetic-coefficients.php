<?php

/*
 * Synthetic stand-in for resources/ejma-private/coefficients.php with the same shape but
 * made-up, exactly linear values (NOT EJMA data), so the interpolation logic can be tested
 * without the private tables:
 *   cp = T + M, cf = 2T + M, cd = T + 2M on the M rows 0.2 … 4.0 and T columns 0.0 … 1.0;
 *   b = [x, 2x, 3x] for x = 0 … 20.
 */

$mRows = ['0.2', '0.4', '0.6', '0.8', '1.0', '1.2', '1.4', '1.6', '2.0', '2.5', '3.0', '3.5', '4.0'];
$tAxis = array_map(fn (int $i) => $i / 20, range(0, 20));
$table = function (float $a, float $b) use ($mRows, $tAxis): array {
    $rows = [];
    foreach ($mRows as $m) {
        $rows[$m] = array_map(fn (float $t) => $a * $t + $b * (float) $m, $tAxis);
    }

    return $rows;
};

return [
    'cp' => $table(1.0, 1.0),
    'cf' => $table(2.0, 1.0),
    'cd' => $table(1.0, 2.0),
    'b' => array_map(fn (int $x) => [(float) $x, 2.0 * $x, 3.0 * $x], range(0, 20)),
];
