<?php

namespace App\Ejma;

/**
 * Equivalent axial movement per convolution of each bellows in the assembly,
 * EJMA 9th Ed. §4.1–4.4 (p064–p068) and Kr per §1.3 (p019).
 *
 * As in the §4.14 benchmarks (p098 note 3), lateral deflection y and angular
 * rotation θ are taken as concurrent with the axial compression position only;
 * the extension position carries axial extension alone (Phase 1 ruling R4).
 *
 * Distribution per assembly (Phase 2 spec §3):
 *  - single: eqs. 4-1, 4-3, 4-7 (Ku = 1.50);
 *  - universal: eqs. 4-2, 4-4, 4-5/4-6 with Ku from Figure 4.1 (p067), plus the
 *    centre-spool dead-weight movements of eqs. 4-12/4-13 (p068) when Wcs is given;
 *  - tied universal: as universal, x = axial thermal growth of the pipe between the tie-rod
 *    plates (p065 note under eq. 4-6; Appendix J Example 4), compression only (ruling P2-R3);
 *    angular rotation is restrained by the tie rods, θ = 0 (ruling P2-R7);
 *  - hinged: angular only, eq. 4-3;
 *  - gimbal: angular only, eq. 4-3 with θ = √(θ1² + θ2²) (vector sum, §4.2 p065).
 */
final readonly class Movement
{
    /** Single bellows expansion joint: Ku is always 1.50 (§4.1, eq. 4-7). */
    private const KU_SINGLE = 1.5;

    public function __construct(
        /** Eq. 4-1 / 4-2: axial compression per convolution (plus eq. 4-12 dead weight). */
        public float $exComp,
        /** Eq. 4-1 / 4-2: axial extension per convolution (plus eq. 4-12 dead weight). */
        public float $exExt,
        /** Lateral per convolution at the compression position: eq. 4-7 (single) or 4-6 (universal), plus eq. 4-13. */
        public float $ey,
        /** Lateral per convolution at the extension position: eq. 4-7 or 4-5 (informational). */
        public float $eyExt,
        /** Eq. 4-3 (single bellows) or 4-4 (dual bellows). */
        public float $etheta,
        /** Eq. 4-8: max(ey + eθ + |ex|, eθ/Kθ + |ex|) at the compression position. */
        public float $ec,
        /** Equivalent extension per convolution at the extension position (axial only). */
        public float $ee,
        /** §4.3 movement range: ec + ee (compressed and extended positions lie either side of neutral). */
        public float $e,
        /** Circumferential stress factor, §1.3 p019: greater of the extension/compression expressions, ≥ 1. */
        public float $Kr,
        /** Lateral distribution factor: 1.50 for a single bellows, Figure 4.1 (p067) for universal assemblies. */
        public float $Ku = self::KU_SINGLE,
        /**
         * Extension per convolution for the §4.2 ee ≤ 6rm − q limit only (ruling P2-R4): eq. 4-9 at the
         * compression position, max(ey + eθ − |ex|, eθ/Kθ − |ex|), or the axial extension if larger,
         * floored at 0. Lateral and angular movement extend one side of the convolutions even while
         * the joint is compressed. The fatigue range e keeps the R4 definition.
         */
        public float $eeLimitCheck = 0.0,
    ) {}

    /** Eq. 4-3: equivalent axial movement per convolution due to angular rotation, |θ|·Dm / (2N). */
    public static function etheta(float $theta, float $Dm, int $N): float
    {
        if ($N <= 0) {
            throw new EjmaInputException('N', 'Number of convolutions N must be at least 1.');
        }

        return abs($theta) * $Dm / (2 * $N);
    }

    /**
     * Angular rotation applied to each individual bellows, θm (§1.3 p023): θ/2 for universal
     * assemblies (each bellows takes half, eq. 4-4), the resultant √(θ1² + θ2²) for gimbal
     * (§4.2 p065), θ for single and hinged, 0 for tied universal (the tie rods restrain angular
     * rotation, ruling P2-R7; the form and request already drop θ, this is the second layer).
     */
    public static function bellowsRotation(DesignConditions $c): float
    {
        $a = $c->assembly;

        return match ($a->type) {
            AssemblyType::Universal => abs($c->theta) / 2,
            AssemblyType::TiedUniversal => 0.0,
            AssemblyType::Gimbal => hypot($c->theta, $a->theta2),
            default => abs($c->theta),
        };
    }

    /**
     * Design lateral deflection the assembly actually applies to its bellows: 0 for the
     * angular-only hinged and gimbal assemblies (hardware restrains lateral), else y.
     */
    public static function appliedLateral(DesignConditions $c): float
    {
        return $c->assembly->type->isAngularOnly() ? 0.0 : $c->y;
    }

    /**
     * Equivalent axial movement per convolution due to angular rotation for the assembly:
     * eq. 4-3 on the per-bellows rotation, i.e. θDm/(2N) single/hinged, θDm/(4N) universal
     * (eq. 4-4), resultant θ for gimbal. N is per bellows.
     */
    public static function assemblyEtheta(DesignConditions $c, float $Dm, int $N): float
    {
        return self::etheta(self::bellowsRotation($c), $Dm, $N);
    }

    /**
     * Figure 4.1 (p067): Ku = (3Lu² − 3LbLu) / (3Lu² − 6LbLu + 4Lb²); 1.50 at Lu = 2Lb.
     */
    public static function universalKu(float $Lu, float $Lb): float
    {
        return (3 * $Lu ** 2 - 3 * $Lb * $Lu) / (3 * $Lu ** 2 - 6 * $Lb * $Lu + 4 * $Lb ** 2);
    }

    /**
     * @param  float  $Ktheta  angular pressure effect factor Kθ (§1.3 p019), in (0, 1]
     * @param  float|null  $fi  theoretical axial spring rate per convolution (lbf/in per convolution);
     *                          required when a universal centre-spool weight Wcs is given (eqs. 4-12/4-13)
     */
    public static function of(BellowsGeometry $g, DesignConditions $c, Derived $d, float $Ktheta = 1.0, ?float $fi = null): self
    {
        // Kθ is computed internally (§1.3 p019), so an out-of-range value is a programming error.
        if ($Ktheta <= 0 || $Ktheta > 1) {
            throw new \InvalidArgumentException('Angular pressure effect factor Kθ must be in (0, 1].');
        }
        if ($g->N <= 0) {
            throw new EjmaInputException('N', 'Number of convolutions N must be at least 1.');
        }
        if ($g->q <= 0) {
            throw new EjmaInputException('q', 'Convolution pitch q must be greater than zero.');
        }

        return match ($c->assembly->type) {
            AssemblyType::Single => self::single($g, $c, $d, $Ktheta),
            AssemblyType::Universal, AssemblyType::TiedUniversal => self::universal($g, $c, $d, $Ktheta, $fi),
            // Hinged: eq. 4-3; gimbal: rotations in two planes added vectorially (§4.2, p065).
            AssemblyType::Hinged, AssemblyType::Gimbal => self::combine($g->q, 0.0, 0.0, 0.0, 0.0, self::assemblyEtheta($c, $d->Dm, $g->N), $Ktheta, self::KU_SINGLE),
        };
    }

    /** Single bellows expansion joint: eqs. 4-1, 4-3, 4-7 (Phase 1 behaviour, unchanged). */
    private static function single(BellowsGeometry $g, DesignConditions $c, Derived $d, float $Ktheta): self
    {
        $N = $g->N;
        $exComp = abs($c->xComp) / $N;                                   // eq. 4-1
        $exExt = abs($c->xExt) / $N;

        $ey = 0.0;
        $eyExt = 0.0;
        if ($c->y != 0.0) {
            $spanComp = $d->Lb - abs($c->xComp);
            if ($spanComp <= 0) {
                throw new EjmaInputException(
                    'x_comp',
                    'Axial compression must be less than the convoluted length Lb when lateral deflection is applied (EJMA eq. 4-7).'
                );
            }
            // Eq. 4-7: 3·Dm·y / (N(Lb ± x)), i.e. Ku = 1.50 in the eq. 4-5/4-6 form.
            $ey = 2 * self::KU_SINGLE * $d->Dm * abs($c->y) / ($N * $spanComp);
            $eyExt = 2 * self::KU_SINGLE * $d->Dm * abs($c->y) / ($N * ($d->Lb + abs($c->xExt)));
        }

        $etheta = self::etheta($c->theta, $d->Dm, $N);                   // eq. 4-3

        return self::combine($g->q, $exComp, $exExt, $ey, $eyExt, $etheta, $Ktheta, self::KU_SINGLE);
    }

    /** Universal (dual bellows) and tied universal: eqs. 4-2, 4-4, 4-5/4-6, Figure 4.1, 4-12/4-13. */
    private static function universal(BellowsGeometry $g, DesignConditions $c, Derived $d, float $Ktheta, ?float $fi): self
    {
        $a = $c->assembly;
        $tied = $a->type === AssemblyType::TiedUniversal;
        $N = $g->N;          // convolutions per bellows
        $Lb = $d->Lb;        // convoluted length of one bellows
        $Dm = $d->Dm;

        if ($tied && $a->tieRods < 2) {
            throw new EjmaInputException('tie_rods', 'A tied universal expansion joint needs at least 2 tie rods.');
        }
        if ($a->Lu === null || $a->Lu <= 2 * $Lb) {
            throw new EjmaInputException(
                'Lu',
                'Lu (distance between the outermost convolution ends) must exceed twice the convoluted length of one bellows (EJMA Figure 4.1).'
            );
        }
        $Lu = $a->Lu;

        // Tied universal (ruling P2-R3): the tie rods restrain the external axial movement, but the
        // pipe between the tie-rod plates still grows by x, compressing the bellows (p065 note under
        // eq. 4-6; Appendix J Example 4). Only compression applies; extension is ignored.
        $xComp = abs($c->xComp);
        $xExt = $tied ? 0.0 : abs($c->xExt);

        $spanComp = $Lu - $Lb - $xComp / 2;
        if ($spanComp <= 0) {
            throw new EjmaInputException('Lu', 'Lu − Lb − x/2 must be greater than zero (EJMA eq. 4-6).');
        }

        $Ku = self::universalKu($Lu, $Lb);                                // Figure 4.1 (p067)

        $exComp = $xComp / (2 * $N);                                      // eq. 4-2
        $exExt = $xExt / (2 * $N);
        $etheta = self::assemblyEtheta($c, $Dm, $N);                      // eq. 4-4: θDm/(4N)

        $ey = 0.0;
        $eyExt = 0.0;
        if ($c->y != 0.0) {
            $ey = $Ku * $Dm * abs($c->y) / (2 * $N * $spanComp);                         // eq. 4-6 (compression)
            $eyExt = $Ku * $Dm * abs($c->y) / (2 * $N * ($Lu - $Lb + $xExt / 2));        // eq. 4-5 (extension)
        }

        if ($a->Wcs !== null) {
            if ($fi === null || $fi <= 0) {
                throw new \InvalidArgumentException('Centre-spool dead weight (eqs. 4-12/4-13) needs the axial spring rate fi.');
            }
            if ($a->Wcs < 0) {
                throw new EjmaInputException('spool_weight', 'Centre-spool weight must not be negative.');
            }

            // §4.4 (p068): movements applied to EACH bellows by the unsupported centre-spool weight.
            // Eq. 4-12, per bellows x = Wcs·sinθu·N / (2fi); per convolution x/N.
            $exDw = $a->Wcs * abs(sin($a->thetaU)) / (2 * $fi);
            // One bellows is compressed and the other extended by this amount; the checks apply
            // to each bellows, so the representative bellows carries it in both positions (conservative).
            $exComp += $exDw;
            $exExt += $exDw;

            // Eq. 4-13, per bellows y = Wcs·cosθu·N(Lb ± x)² / (3fi·Dm²), x = axial movement of that bellows.
            // Each bellows deflects with parallel ends under the spool, so eq. 4-7 converts y to
            // per-convolution movement: 3·Dm·y / (N(Lb ± x)) = Wcs·cosθu·(Lb ± x) / (fi·Dm).
            $xbComp = $xComp / 2;
            if ($Lb - $xbComp <= 0) {
                throw new EjmaInputException('x_comp', 'Axial compression per bellows must be less than its convoluted length Lb (EJMA eq. 4-13).');
            }
            $wLat = $a->Wcs * abs(cos($a->thetaU)) / ($fi * $Dm);
            // Added arithmetically to the design lateral (the spool sags vertically; the design
            // lateral plane is not known, so the arithmetic sum is the conservative bound).
            $ey += $wLat * ($Lb - $xbComp);
            $eyExt += $wLat * ($Lb + $xExt / 2);
        }

        return self::combine($g->q, $exComp, $exExt, $ey, $eyExt, $etheta, $Ktheta, $Ku);
    }

    /** Eq. 4-8 at the compression position, axial-only extension (R4), §4.3 range and Kr (§1.3 p019). */
    private static function combine(float $q, float $exComp, float $exExt, float $ey, float $eyExt, float $etheta, float $Ktheta, float $Ku): self
    {
        // Eq. 4-8 (compression position, y and θ concurrent).
        $ec = max($ey + $etheta + $exComp, $etheta / $Ktheta + $exComp);
        // Extension position: axial extension only (benchmark note 3, p098).
        $ee = $exExt;
        // §4.3: range from the extended to the compressed position.
        $e = $ec + $ee;

        $Kr = max(
            1.0,
            (2 * ($q + $exExt)) / (2 * $q),
            (2 * ($q - $exComp) + $etheta / $Ktheta + $ey) / (2 * $q),
        );

        // §4.2 extension-limit value, eq. 4-9 at the compression position (ruling P2-R4).
        $eeLimitCheck = max(0.0, $ey + $etheta - $exComp, $etheta / $Ktheta - $exComp, $exExt);

        return new self($exComp, $exExt, $ey, $eyExt, $etheta, $ec, $ee, $e, $Kr, $Ku, $eeLimitCheck);
    }
}
