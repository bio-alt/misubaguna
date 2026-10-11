<?php

namespace App\Ejma;

/**
 * Equivalent axial movement per convolution for a single bellows expansion joint,
 * EJMA 9th Ed. §4.1–4.3 (p064–p066) and Kr per §1.3 (p019).
 *
 * As in the §4.14 benchmarks (p098 note 3), lateral deflection y and angular
 * rotation θ are taken as concurrent with the axial compression position only;
 * the extension position carries axial extension alone.
 */
final readonly class Movement
{
    /** Single bellows expansion joint: Ku is always 1.50 (§4.1, eq. 4-7). */
    private const KU_SINGLE = 1.5;

    public function __construct(
        /** Eq. 4-1: xComp / N. */
        public float $exComp,
        /** Eq. 4-1: xExt / N. */
        public float $exExt,
        /** Eq. 4-7 at the compression position: 3·Dm·y / (N(Lb − xComp)). */
        public float $ey,
        /** Eq. 4-7 at the extension position: 3·Dm·y / (N(Lb + xExt)) (informational). */
        public float $eyExt,
        /** Eq. 4-3: θ·Dm / (2N). */
        public float $etheta,
        /** Eq. 4-8: max(ey + eθ + |ex|, eθ/Kθ + |ex|) at the compression position. */
        public float $ec,
        /** Equivalent extension per convolution at the extension position (axial only). */
        public float $ee,
        /** §4.3 movement range: ec + ee (compressed and extended positions lie either side of neutral). */
        public float $e,
        /** Circumferential stress factor, §1.3 p019: greater of the extension/compression expressions, ≥ 1. */
        public float $Kr,
    ) {}

    public static function of(BellowsGeometry $g, DesignConditions $c, Derived $d, float $Ktheta = 1.0): self
    {
        if ($Ktheta <= 0 || $Ktheta > 1) {
            throw new EjmaInputException('theta', 'Angular pressure effect factor Kθ must be in (0, 1].');
        }

        $N = $g->N;
        $exComp = abs($c->xComp) / $N;
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
            $ey = 2 * self::KU_SINGLE * $d->Dm * abs($c->y) / ($N * $spanComp);
            $eyExt = 2 * self::KU_SINGLE * $d->Dm * abs($c->y) / ($N * ($d->Lb + abs($c->xExt)));
        }

        $etheta = abs($c->theta) * $d->Dm / (2 * $N);

        // Eq. 4-8 (compression position, y and θ concurrent).
        $ec = max($ey + $etheta + $exComp, $etheta / $Ktheta + $exComp);
        // Extension position: axial extension only (benchmark note 3, p098).
        $ee = $exExt;
        // §4.3: range from the extended to the compressed position.
        $e = $ec + $ee;

        $q = $g->q;
        $Kr = max(
            1.0,
            (2 * ($q + $exExt)) / (2 * $q),
            (2 * ($q - $exComp) + $etheta / $Ktheta + $ey) / (2 * $q),
        );

        return new self($exComp, $exExt, $ey, $eyExt, $etheta, $ec, $ee, $e, $Kr);
    }
}
