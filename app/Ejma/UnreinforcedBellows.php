<?php

namespace App\Ejma;

/**
 * Unreinforced U-shaped bellows, EJMA 9th Ed. §4.13.1 eqs. 4-27…4-37 (p091–p092).
 * Verified against §4.14 benchmark 1 (p097–p098).
 */
final class UnreinforcedBellows implements Checker
{
    use BuildsCheckLines;

    public function check(BellowsGeometry $g, DesignConditions $c, BellowsProperties $p): CheckResult
    {
        if ($c->P < 0) {
            throw new EjmaInputException('design_pressure', 'Design pressure must not be negative.');
        }
        if ($g->w === null || $g->w <= 0) {
            throw new EjmaInputException('w', 'Convolution height w must be given.');
        }
        if ($g->rm === null || $g->rm <= 0) {
            throw new EjmaInputException('rm', 'Convolution mean radius rm must be given.');
        }
        if ($g->Lt === null || $g->Lt <= 0) {
            throw new EjmaInputException('Lt', 'Tangent length Lt must be greater than zero.');
        }
        $hasCollar = $g->tc > 0;
        if ($hasCollar && $g->Lc <= 0) {
            throw new EjmaInputException('Lc', 'Collar length Lc must be greater than zero when a collar is given.');
        }

        $d = Derived::of($g, $c);
        $P = $c->P;
        $n = $g->n;
        $t = $g->t;
        $w = $g->w;
        $q = $g->q;
        $tp = $d->tp;
        $Db = $g->Db;

        $T = Coefficients::T($g->rm, $w);
        $M = Coefficients::M($g->rm, $d->Dm, $tp);
        $Cp = Coefficients::cp($T, $M);
        $Cf = Coefficients::cf($T, $M);
        $Cd = Coefficients::cd($T, $M);

        // Eq. 4-37 at design temperature (§1.3: E is at design temperature unless specified); reproduces benchmark fi = 12562.
        $fi = 1.7 * $d->Dm * $p->EbDt * $tp ** 3 * $n / ($w ** 3 * $Cf);

        $Ctheta = AngularPressureFactor::Ctheta($c->y, $c->theta);
        $phi = AngularPressureFactor::phi(BellowsType::Unreinforced, $P, $g->N, $q, $fi);
        $etheta = Movement::etheta($c->theta, $d->Dm, $g->N);
        $Ktheta = AngularPressureFactor::Ktheta($etheta, $Ctheta, $q, $phi);
        $m = Movement::of($g, $c, $d, $Ktheta);
        $e = $m->e;

        // Eqs. 4-27/4-28 at design temperature; with no collar the modulus cancels (benchmark S1 = 6301 either way).
        $Eb = $p->EbDt;
        $collarTerm = $hasCollar ? $g->tc * $d->k * $p->EcDt * $g->Lc * $d->Dc : 0.0;
        $den = 2 * ($n * $t * $Eb * $g->Lt * ($Db + $n * $t) + $collarTerm);
        $S1 = $P * ($Db + $n * $t) ** 2 * $g->Lt * $Eb * $d->k / $den;
        $S1p = $hasCollar ? $P * $d->Dc ** 2 * $g->Lt * $p->EcDt * $d->k / $den : null;

        $K2 = $d->Dm * $m->Kr * $q / (2 * $d->Ac);            // §1.3 p018: K2 = S2/P
        $K4 = $Cp / (2 * $n) * ($w / $tp) ** 2;              // §1.3 p019
        $S2 = $P * $K2;                                      // eq. 4-29
        $S3 = $P * $w / (2 * $n * $tp);                      // eq. 4-30
        $S4 = $P * $K4;                                      // eq. 4-31
        // Eqs. 4-32/4-33: Eb at room temperature (note p091).
        $S5 = $p->EbRt * $tp ** 2 * $e / (2 * $w ** 3 * $Cf);
        $S6 = 5 * $p->EbRt * $tp * $e / (3 * $w ** 2 * $Cd);
        $St = 0.7 * ($S3 + $S4) + ($S5 + $S6);               // p092
        $Nc = Fatigue::cycles($St, $p->fatigue);              // eq. 4-34

        $Psc = 0.34 * M_PI * $Ctheta * $fi / ($g->N ** 2 * $q);  // eq. 4-35
        $delta = $K4 / (3 * $K2);                              // §1.3 p023
        $alpha = 1 + 2 * $delta ** 2 + sqrt(1 - 2 * $delta ** 2 + 4 * $delta ** 4);
        $Psi = 1.3 * $d->Ac * $p->Sy / ($m->Kr * $d->Dm * $q * sqrt($alpha));  // eq. 4-36

        $lines = [self::le('hoop_tangent', 'Tangent circumferential membrane stress (pressure)', '4-27', 'S1', $S1, $p->Cwb * $p->Sab, 'Cwb·Sab')];
        if ($hasCollar) {
            $lines[] = self::le('hoop_collar', 'Collar circumferential membrane stress (pressure)', '4-28', "S1'", $S1p, $p->Cwc * $p->Sac, 'Cwc·Sac');
        }
        $lines[] = self::le('hoop_bellows', 'Bellows circumferential membrane stress (pressure)', '4-29', 'S2', $S2, $p->Cwb * $p->Sab, 'Cwb·Sab');
        $lines[] = self::le('meridional', 'Meridional membrane + bending stress (pressure)', '4-30/4-31', 'S3+S4', $S3 + $S4, $p->Cm * $p->Sab, 'Cm·Sab');
        $lines[] = self::fatigueLine('4-34', $Nc, $c->requiredCycles);
        $lines[] = self::le('squirm_column', 'Column instability (squirm) pressure', '4-35', 'Psc', $P, $Psc, 'Psc');
        $lines[] = self::le('squirm_inplane', 'In-plane instability (squirm) pressure', '4-36', 'Psi', $P, $Psi, 'Psi');

        $values = ['S1' => $S1];
        if ($S1p !== null) {
            $values['S1p'] = $S1p;
        }
        $values += [
            'S2' => $S2, 'S3' => $S3, 'S4' => $S4, 'S5' => $S5, 'S6' => $S6, 'St' => $St,
        ];
        if ($Nc !== null) {
            $values['Nc'] = $Nc;
        }
        $values += [
            'Psc' => $Psc, 'Psi' => $Psi, 'fi' => $fi, 'Cp' => $Cp, 'Cf' => $Cf, 'Cd' => $Cd,
            'Kr' => $m->Kr, 'e' => $e, 'Ctheta' => $Ctheta, 'Ktheta' => $Ktheta, 'phi' => $phi, 'alpha' => $alpha,
        ];

        return new CheckResult(BellowsType::Unreinforced, $lines, $values);
    }
}
