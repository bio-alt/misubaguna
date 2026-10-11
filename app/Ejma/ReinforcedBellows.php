<?php

namespace App\Ejma;

/**
 * Reinforced U-shaped bellows with integral reinforcing members (R = R1, no fasteners),
 * EJMA 9th Ed. §4.13.2 eqs. 4-38…4-50 (p093–p094), below the creep range.
 * Verified against §4.14 benchmark 3 (p097–p098).
 *
 * Angular rotation (benchmark 3: θ = 5°, y = 0):
 *   Cθ = 0.1157^θm (p018), θm = θ in radians                          → 0.8284
 *   φ  = PN²q / (0.3π·fir) (AngularPressureFactor: benchmark form, not p023's 0.675π) → 0.2766
 *   Kθ = eθCθ / (eθCθ + 0.15qφ) (p019), eθ = θDm/(2N) (eq. 4-3)         → 0.7352
 * giving e = 0.3767 (published 0.3756). With p023's 0.675π form e would be 0.3488, and the
 * published S1'', S5, S6, St, Nc are all reproduced only at e ≈ 0.3756.
 * Known deviation: eq. 4-49 gives Psc = 299.5 against the published 307 (~2.5 %); no reading
 * of θm, eθ, fir or fw reproduces 307, so the equation is kept as written (conservative).
 */
final class ReinforcedBellows implements Checker
{
    use BuildsCheckLines;

    /**
     * Shape factor Ks for the collar bending limit S1' + S1'' ≤ Ks·Cwc·Sac (p094).
     * §1.3 p019 refers to Table III (§9, p153, printed 9-8): a tangent collar is a band of
     * rectangular cross-section bent through its thickness, "Solid Rectangle" Ks = 1.5.
     */
    public const KS_COLLAR = 1.5;

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
        if ($g->Ar <= 0) {
            throw new EjmaInputException('Ar', 'Reinforcing member area Ar must be greater than zero.');
        }
        $hasCollar = $g->tc > 0;
        if ($hasCollar) {
            if ($g->Atc <= 0) {
                throw new EjmaInputException('Atc', 'Collar area Atc must be greater than zero when a collar is given.');
            }
            if ($g->Zc <= 0) {
                throw new EjmaInputException('Zc', 'Collar section modulus Zc must be greater than zero when a collar is given.');
            }
            if ($g->ng < 1) {
                throw new EjmaInputException('ng', 'Number of collar gussets ng must be at least 1 when a collar is given.');
            }
        }
        // Moduli come from the material data, not the form.
        if ($p->ErDt <= 0 || ($hasCollar && $p->EcDt <= 0)) {
            throw new \InvalidArgumentException('Reinforcing member and collar moduli must be positive.');
        }

        $d = Derived::of($g, $c);
        $P = $c->P;
        $n = $g->n;
        $t = $g->t;
        $w = $g->w;
        $q = $g->q;
        $N = $g->N;
        $tp = $d->tp;
        $Db = $g->Db;

        // §4.13.2 replaces w by the effective height w − 4Cr·rm (Cr per §1.3 p017).
        $wr = $w - 4 * $d->Cr * $g->rm;
        if ($wr <= 0) {
            throw new EjmaInputException('geometry', 'Convolution height w is too small for the mean radius rm (w − 4Cr·rm ≤ 0).');
        }

        // Cp/Cf/Cd from T and M on w (benchmark 3 publishes the benchmark 1 coefficients).
        $T = Coefficients::T($g->rm, $w);
        $M = Coefficients::M($g->rm, $d->Dm, $tp);
        $Cp = Coefficients::cp($T, $M);
        $Cf = Coefficients::cf($T, $M);
        $Cd = Coefficients::cd($T, $M);

        // Eq. 4-50 at design temperature; reproduces benchmark fi = 24548.
        $fir = 1.7 * $d->Dm * $p->EbDt * $tp ** 3 * $n / ($wr ** 3 * $Cf);

        $apf = AngularPressureFactor::forAssembly(BellowsType::Reinforced, $c, $d->Dm, $N, $q, $q, $fir);
        $Ctheta = $apf->Ctheta;
        $Ktheta = $apf->Ktheta;
        $m = Movement::of($g, $c, $d, $Ktheta, $fir);
        $e = $m->e;
        $Kr = $m->Kr;

        // Eqs. 4-38/4-39 at design temperature.
        $Eb = $p->EbDt;
        $collarTerm = $hasCollar ? $g->Atc * $p->EcDt * $d->Dc : 0.0;
        $den = 2 * (($n * $t * $g->Lt + $d->Ac / 2) * $Eb * ($Db + $n * $t) + $collarTerm);
        $S1 = $P * ($Db + $n * $t) ** 2 * $d->Ld * $Eb / $den;

        // Fg (§1.3 p018, below the creep range) with fw when given, else the theoretical fir.
        $fSpring = $g->fw ?? $fir;
        $S1p = $S1pp = $Fg = null;
        if ($hasCollar) {
            $S1p = $P * $d->Dc ** 2 * $d->Ld * $p->EcDt / $den;                                 // eq. 4-39
            $Fg = (0.25 * M_PI * ($d->Dm ** 2 - $Db ** 2) * $P + $m->ec * $fSpring) / $g->ng;
            $S1pp = $Fg * $g->ng * $d->Dc / (4 * M_PI * $d->Cc * $g->Zc);                        // eq. 4-40
        }

        $H = $P * $d->Dm * $q;                                  // §1.3 p018
        $R = $d->Ac * $Eb / ($g->Ar * $p->ErDt);                // R1, §1.3 p021
        $S2 = $H / (2 * $d->Ac) * ($R / ($R + 1)) * $Kr;        // eq. 4-41
        $S2p = $H / (2 * $g->Ar) * (1 / ($R + 1)) * $Kr;        // eq. 4-42
        $S3 = 0.85 * $P * $wr / (2 * $n * $tp);                 // eq. 4-44
        $S4 = 0.85 * $P / (2 * $n) * ($wr / $tp) ** 2 * $Cp;    // eq. 4-45
        // Eqs. 4-46/4-47: Eb at room temperature (note p094).
        $S5 = $p->EbRt * $tp ** 2 * $e / (2 * $wr ** 3 * $Cf);
        $S6 = 5 * $p->EbRt * $tp * $e / (3 * $wr ** 2 * $Cd);
        $St = 0.7 * ($S3 + $S4) + ($S5 + $S6);                  // p094
        $Nc = Fatigue::cycles($St, $p->fatigue);                 // eq. 4-48

        // Eq. 4-49; N = total convolutions in both bellows for universal (note to 4-49).
        $Psc = 0.3 * M_PI * $Ctheta * $fir / (self::totalConvolutions($g, $c) ** 2 * $q);

        $lines = [self::le('hoop_tangent', 'Tangent circumferential membrane stress (pressure)', '4-38', 'S1', $S1, $p->Cwb * $p->Sab, 'Cwb·Sab')];
        if ($hasCollar) {
            $lines[] = self::le('hoop_collar', 'Collar circumferential membrane stress (pressure)', '4-39', "S1'", $S1p, $p->Cwc * $p->Sac, 'Cwc·Sac');
            $lines[] = self::le('collar_bending', 'Collar circumferential membrane + bending stress (pressure)', '4-40', "S1'+S1''", $S1p + $S1pp, self::KS_COLLAR * $p->Cwc * $p->Sac, 'Ks·Cwc·Sac');
        }
        $lines[] = self::le('hoop_bellows', 'Bellows circumferential membrane stress (pressure)', '4-41', 'S2', $S2, $p->Cwb * $p->Sab, 'Cwb·Sab');
        $lines[] = self::le('hoop_reinforcement', 'Reinforcing member circumferential membrane stress (pressure)', '4-42', "S2'", $S2p, $p->Cwr * $p->Sar, 'Cwr·Sar');
        $lines[] = self::le('meridional', 'Meridional membrane + bending stress (pressure)', '4-44/4-45', 'S3+S4', $S3 + $S4, $p->Cm * $p->Sab, 'Cm·Sab');
        $lines[] = self::fatigueLine('4-48', $Nc, $c->requiredCycles);
        $lines[] = self::le('squirm_column', 'Column instability (squirm) pressure', '4-49', 'Psc', $P, $Psc, 'Psc');
        $lines = [...$lines, ...self::movementLimitLines($g, $c, $d, $m, true)];

        $values = ['S1' => $S1];
        if ($hasCollar) {
            $values += ['S1p' => $S1p, 'S1pp' => $S1pp];
        }
        $values += ['S2' => $S2, 'S2p' => $S2p, 'S3' => $S3, 'S4' => $S4, 'S5' => $S5, 'S6' => $S6, 'St' => $St];
        if ($Nc !== null) {
            $values['Nc'] = $Nc;
        }
        $values += [
            'Psc' => $Psc, 'fi' => $fir, 'Cp' => $Cp, 'Cf' => $Cf, 'Cd' => $Cd, 'Kr' => $Kr, 'e' => $e,
            'Ctheta' => $Ctheta, 'Ktheta' => $Ktheta, 'phi' => $apf->phi, 'Cr' => $d->Cr, 'R' => $R, 'fspring' => $fSpring,
            'Ku' => $m->Ku,
        ];
        if ($Fg !== null) {
            $values['Fg'] = $Fg;
        }

        return new CheckResult(BellowsType::Reinforced, $lines, $values, self::assemblyNotes($c));
    }
}
