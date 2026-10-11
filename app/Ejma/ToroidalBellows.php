<?php

namespace App\Ejma;

/**
 * Toroidal bellows with integral reinforcing members and tangent collar (no fasteners),
 * EJMA 9th Ed. §4.13.3 eqs. 4-51…4-61 (p095–p096), below the creep range.
 * Verified against §4.14 benchmark 4 (p097–p098).
 *
 * Dm is the given toroidal mean diameter (Figure 4.15); B1/B2/B3 from Table I4 at
 * x = 6.61r²/(Dm·tp). Eqs. 4-51/4-52 have no tangent-length term, so the collar
 * (tc, Atc, Zc, ng) is required. There is no Kr and no in-plane squirm check for
 * toroidal bellows. Angular rotation enters e through Kθ with
 * φ = PN²r/(0.15π·fit) (AngularPressureFactor, ruling R7).
 */
final class ToroidalBellows implements Checker
{
    use BuildsCheckLines;

    public function check(BellowsGeometry $g, DesignConditions $c, BellowsProperties $p): CheckResult
    {
        if ($c->P < 0) {
            throw new EjmaInputException('design_pressure', 'Design pressure must not be negative.');
        }
        if ($g->r === null || $g->r <= 0) {
            throw new EjmaInputException('r', 'Toroidal convolution radius r must be given.');
        }
        if ($g->Dr === null || $g->Dr <= 0) {
            throw new EjmaInputException('Dr', 'Reinforcing member mean diameter Dr must be given.');
        }
        if ($g->Ar <= 0) {
            throw new EjmaInputException('Ar', 'Reinforcing member area Ar must be greater than zero.');
        }
        if ($g->tc <= 0) {
            throw new EjmaInputException('tc', 'Collar thickness tc must be given for a toroidal bellows.');
        }
        if ($g->Atc <= 0) {
            throw new EjmaInputException('Atc', 'Collar area Atc must be greater than zero.');
        }
        if ($g->Zc <= 0) {
            throw new EjmaInputException('Zc', 'Collar section modulus Zc must be greater than zero.');
        }
        if ($g->ng < 1) {
            throw new EjmaInputException('ng', 'Number of collar gussets ng must be at least 1.');
        }
        // Moduli come from the material data, not the form.
        if ($p->EcDt <= 0) {
            throw new \InvalidArgumentException('Collar modulus must be positive.');
        }

        $d = Derived::of($g, $c);
        $P = $c->P;
        $n = $g->n;
        $N = $g->N;
        $r = $g->r;
        $Dm = $d->Dm;
        $tp = $d->tp;
        $Db = $g->Db;

        if ($Dm - 2 * $r <= 0) {
            throw new EjmaInputException('r', 'Convolution radius r is too large for the mean diameter Dm (Dm − 2r ≤ 0).');
        }

        // Table I4 coefficients (Appendix I, linear interpolation).
        $x = 6.61 * $r ** 2 / ($Dm * $tp);
        $B1 = Coefficients::b1($x);
        $B2 = Coefficients::b2($x);
        $B3 = Coefficients::b3($x);

        // Eq. 4-61 at design temperature; reproduces benchmark fi = 15714.
        $fit = $Dm * $p->EbDt * $tp ** 3 * $n / (10.92 * $r ** 3) * $B3;

        $apf = AngularPressureFactor::forAssembly(BellowsType::Toroidal, $c, $Dm, $N, $g->q, $r, $fit);
        $Ctheta = $apf->Ctheta;
        $Ktheta = $apf->Ktheta;
        $m = Movement::of($g, $c, $d, $Ktheta, $fit);
        $e = $m->e;

        // Eqs. 4-51/4-52 (Eb at design temperature, as for the reinforced eq. 4-38).
        $S1 = $P * $Db ** 2 * $d->Ld * $p->EbDt / (2 * $d->Dc * $p->EcDt * $g->Atc);
        $S1p = $P * $d->Dc * $d->Ld / (2 * $g->Atc);
        // Fg (§1.3 p018, below the creep range) with fw when given, else the theoretical fit.
        $fSpring = $g->fw ?? $fit;
        $Fg = (0.25 * M_PI * ($Dm ** 2 - $Db ** 2) * $P + $m->ec * $fSpring) / $g->ng;
        $S1pp = $Fg * $g->ng * $d->Dc / (4 * M_PI * $d->Cc * $g->Zc);       // eq. 4-53

        $S2 = $P * $r / (2 * $n * $tp);                                       // eq. 4-54
        $S2p = $P * $g->Dr * $g->q / (2 * $g->Ar);                            // eq. 4-55
        $S3 = $P * $r / ($n * $tp) * (($Dm - $r) / ($Dm - 2 * $r));           // eq. 4-56
        // Eqs. 4-57/4-58: Eb at room temperature (note p095).
        $S5 = $p->EbRt * $tp ** 2 * $e / (34.3 * $r ** 3) * $B1;
        $S6 = $p->EbRt * $tp * $e / (5.72 * $r ** 2) * $B2;
        $St = 3 * $S3 + $S5 + $S6;                                            // eq. 4-59 note
        $Nc = Fatigue::cycles($St, $p->fatigue);                              // eq. 4-59

        // Eq. 4-60; N = total convolutions in both bellows for universal (note to 4-60).
        $Psc = 0.15 * M_PI * $Ctheta * $fit / (self::totalConvolutions($g, $c) ** 2 * $r);

        $lines = [
            self::le('hoop_tangent', 'Tangent circumferential membrane stress (pressure)', '4-51', 'S1', $S1, $p->Cwb * $p->Sab, 'Cwb·Sab'),
            self::le('hoop_collar', 'Collar circumferential membrane stress (pressure)', '4-52', "S1'", $S1p, $p->Cwc * $p->Sac, 'Cwc·Sac'),
            self::le('collar_bending', 'Collar circumferential membrane + bending stress (pressure)', '4-53', "S1'+S1''", $S1p + $S1pp, ReinforcedBellows::KS_COLLAR * $p->Cwc * $p->Sac, 'Ks·Cwc·Sac'),
            self::le('hoop_bellows', 'Bellows circumferential membrane stress (pressure)', '4-54', 'S2', $S2, $p->Cwb * $p->Sab, 'Cwb·Sab'),
            self::le('hoop_reinforcement', 'Reinforcing member circumferential membrane stress (pressure)', '4-55', "S2'", $S2p, $p->Cwr * $p->Sar, 'Cwr·Sar'),
            self::le('meridional', 'Meridional membrane stress (pressure)', '4-56', 'S3', $S3, $p->Sab, 'Sab'),
            self::fatigueLine('4-59', $Nc, $c->requiredCycles),
            self::le('squirm_column', 'Column instability (squirm) pressure', '4-60', 'Psc', $P, $Psc, 'Psc'),
        ];
        // No ec/ee limits: §4.2 states them for U-shaped convolutions (q − 2rm − nt, 6rm − q).
        $lines = [...$lines, ...self::movementLimitLines($g, $c, $d, $m, false)];

        $values = [
            'S1' => $S1, 'S1p' => $S1p, 'S1pp' => $S1pp, 'S2' => $S2, 'S2p' => $S2p,
            'S3' => $S3, 'S5' => $S5, 'S6' => $S6, 'St' => $St,
        ];
        if ($Nc !== null) {
            $values['Nc'] = $Nc;
        }
        $values += [
            'Psc' => $Psc, 'fi' => $fit, 'B1' => $B1, 'B2' => $B2, 'B3' => $B3, 'x' => $x, 'e' => $e,
            'Ctheta' => $Ctheta, 'Ktheta' => $Ktheta, 'phi' => $apf->phi, 'fspring' => $fSpring, 'Fg' => $Fg,
            'Ku' => $m->Ku,
        ];

        return new CheckResult(BellowsType::Toroidal, $lines, $values, self::assemblyNotes($c));
    }
}
