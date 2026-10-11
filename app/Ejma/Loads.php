<?php

namespace App\Ejma;

/**
 * Joint forces, moments and pressure thrust, EJMA 9th Ed. §4.6.1 eqs. 4-14…4-18 (p069),
 * with the working spring rate fw of §1.3 (p022). All forces lbf, moments in·lbf.
 *
 * Movements per convolution come from the assembly-aware Movement distribution. The lateral
 * shear Vl is taken at the compression position (Lb − x, Lu − x), consistent with ruling R4
 * (lateral concurrent with compression) and with ey of eqs. 4-6/4-7; this is larger than the
 * neutral-length value used in the Appendix J examples (conservative).
 */
final class Loads
{
    /**
     * Phase 1 keys Ksr (lbf/in), thrust (lbf), spring_force (lbf), plus fw (lbf/in per convolution),
     * per-bellows movements ex, ey, etheta (in),
     * Fa, V_lateral (lbf), M_lateral, M_theta (in·lbf) and, for tied universal / hinged / gimbal,
     * hardware_total and hardware_per_component (lbf). Tied universal: anchor Fa = 0 and
     * tie_rod_axial = fw·ex (the bellows spring force carried by the tie rods).
     *
     * @param  float  $Sy  bellows yield strength used for the fw rule (psi)
     * @return array<string, float>
     */
    public static function of(BellowsGeometry $g, DesignConditions $c, Derived $d, CheckResult $result, float $Sy): array
    {
        $fi = $result->values['fi'];
        // Kθ only affects ec/Kr, not ex/ey/eθ, so the distribution is taken with Kθ = 1.
        $m = Movement::of($g, $c, $d, 1.0, $fi);
        $fw = self::workingSpringRate($g->fw, $fi, $result->values['St'], $Sy);
        $thrust = self::pressureThrust($c->P, $d->Dm);
        // Axial movement per convolution at the larger of the two positions (eq. 4-1 / 4-2).
        $ex = max($m->exComp, $m->exExt);
        $tied = $c->assembly->type === AssemblyType::TiedUniversal;
        // Tied universal (ruling P2-R3): the bellows force fw·ex is reacted by the tie rods, not the anchors.
        $tieRodAxial = $tied ? self::axialForce($fw, $ex) : 0.0;

        $out = [
            'Ksr' => $fi / $g->N,
            'thrust' => $thrust,
            // Theoretical-rate force at the larger axial position: fi·ex = (fi/N)·x for a single
            // bellows (Phase 1), fi·x/(2N) for two bellows in series, 0 when axial is restrained.
            'spring_force' => $fi * $ex,
            'fw' => $fw,
            // Per-bellows movements (in) for the report: larger axial position, compression-position lateral, eθ.
            'ex' => $ex,
            'ey' => $m->ey,
            'etheta' => $m->etheta,
            'Fa' => $tied ? 0.0 : self::axialForce($fw, $ex),
            'M_lateral' => self::lateralMoment($fw, $d->Dm, $m->ey),
            'M_theta' => self::angularMoment($fw, $d->Dm, $m->etheta),
            'V_lateral' => $m->ey == 0.0 ? 0.0 : self::lateralShear($fw, $d->Dm, $m->ey, self::lateralSpan($c, $d)),
        ];
        if ($tied) {
            $out['tie_rod_axial'] = $tieRodAxial;
        }

        return $out + self::hardware($c->assembly, $thrust, $tieRodAxial);
    }

    /** §1.3 p022: the given fw, else fi for St ≤ 1.5·Sy, else 0.67·fi. */
    public static function workingSpringRate(?float $given, float $fi, float $St, float $Sy): float
    {
        if ($given !== null) {
            return $given;
        }

        return $St <= 1.5 * $Sy ? $fi : 0.67 * $fi;
    }

    /** Which branch of the §1.3 fw rule applies, for the report note. */
    public static function workingSpringRateBasis(?float $given, float $St, float $Sy): string
    {
        return match (true) {
            $given !== null => 'fw as given',
            $St <= 1.5 * $Sy => 'fw = fi (St ≤ 1.5·Sy)',
            default => 'fw = 0.67·fi (St > 1.5·Sy)',
        };
    }

    /** Eq. 4-14: Fa = fw·ex. */
    public static function axialForce(float $fw, float $ex): float
    {
        return $fw * $ex;
    }

    /** Eq. 4-15: Ml = fw·Dm·ey / 4. */
    public static function lateralMoment(float $fw, float $Dm, float $ey): float
    {
        return $fw * $Dm * $ey / 4;
    }

    /** Eq. 4-16: Mθ = fw·Dm·eθ / 4. */
    public static function angularMoment(float $fw, float $Dm, float $etheta): float
    {
        return $fw * $Dm * $etheta / 4;
    }

    /** Eqs. 4-17/4-18: Vl = fw·Dm·ey / (2·span), span = Lb ± x (single) or Lu ± x (universal). */
    public static function lateralShear(float $fw, float $Dm, float $ey, float $span): float
    {
        return $fw * $Dm * $ey / (2 * $span);
    }

    /**
     * Span of eqs. 4-17/4-18 at the compression position: Lb − x for a single bellows,
     * Lu − x for a universal, Lu for a tied universal (the tie rods hold the overall length;
     * Appendix J Example 4). Angular-only assemblies carry
     * no lateral, so Lb is returned for completeness.
     */
    public static function lateralSpan(DesignConditions $c, Derived $d): float
    {
        $a = $c->assembly;
        $span = match ($a->type) {
            AssemblyType::Single => $d->Lb - abs($c->xComp),
            AssemblyType::Universal => (float) $a->Lu - abs($c->xComp),
            AssemblyType::TiedUniversal => (float) $a->Lu,
            AssemblyType::Hinged, AssemblyType::Gimbal => $d->Lb,
        };
        if ($span <= 0) {
            throw new EjmaInputException('x_comp', 'Axial compression must be less than the joint length carrying the lateral shear (EJMA eqs. 4-17/4-18).');
        }

        return $span;
    }

    /** Pressure thrust P·π·Dm²/4 (effective area on the mean diameter). */
    public static function pressureThrust(float $P, float $Dm): float
    {
        return $P * M_PI * $Dm ** 2 / 4;
    }

    /**
     * Restraint hardware load (Phase 2 spec §3): tie rods of a tied universal carry the thrust plus
     * the bellows spring force fw·ex (ruling P2-R3; per rod = total / rods, tieRods ≥ 2 is
     * validated by Movement); hinge pins and gimbal pins carry it with T/2 per pin (two pins per
     * hinge plane / ring axis). Untied single and universal joints have no hardware: the thrust
     * goes to the main anchors.
     *
     * @return array{hardware_total?: float, hardware_per_component?: float}
     */
    public static function hardware(AssemblyGeometry $a, float $thrust, float $tieRodAxial = 0.0): array
    {
        return match ($a->type) {
            AssemblyType::TiedUniversal => [
                'hardware_total' => $thrust + $tieRodAxial,
                'hardware_per_component' => ($thrust + $tieRodAxial) / $a->tieRods,
            ],
            AssemblyType::Hinged, AssemblyType::Gimbal => ['hardware_total' => $thrust, 'hardware_per_component' => $thrust / 2],
            default => [],
        };
    }
}
