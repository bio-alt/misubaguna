<?php

namespace App\Ejma;

/**
 * Derived bellows geometry per EJMA 9th Ed. §1.3 nomenclature (p017–p023).
 * All lengths in inches, pressure in psi.
 */
final readonly class Derived
{
    public function __construct(
        /** Mean diameter of convolutions, §1.3 p018: Db + w + nt ("U"); given Dm for toroidal (Fig. 4.15). */
        public float $Dm,
        /** Ply thickness corrected for thinning, §1.3 p022: t·√(Db/Dm). */
        public float $tp,
        /** Metal cross-section of one convolution, §1.3 p017 (round bellows). */
        public float $Ac,
        /** Convoluted length, §1.3 p020: N·q. */
        public float $Lb,
        /** Mean diameter of tangent collar, §1.3 p018: Db + 2nt + tc. */
        public float $Dc,
        /** Attachment weld to first convolution centre, §1.3 p020: given, else Lt + q/2. */
        public float $Ld,
        /** Tangent stiffening factor, §1.3 p022: Lt/(1.5√(Db·t)), capped at 1. */
        public float $k,
        /** Forming strain in %, §1.3 p023; null for toroidal (formula is defined for U profile only). */
        public ?float $epsilonF,
        /** Collar curvature factor, §1.3 p017: −0.2431 + 0.0168ng + 0.3024ng². */
        public float $Cc,
        /** Convolution height factor, §1.3 p017: 0.3 − (100/(0.6P^1.5 + 320))². */
        public float $Cr,
    ) {}

    public static function of(BellowsGeometry $g, DesignConditions $c): self
    {
        $toroidal = $g->type === BellowsType::Toroidal;

        if ($toroidal) {
            if ($g->DmToroidal === null || $g->DmToroidal <= $g->Db) {
                throw new EjmaInputException('dm', 'Toroidal mean diameter Dm must be given and exceed Db.');
            }
            if ($g->r === null || $g->r <= 0) {
                throw new EjmaInputException('r', 'Toroidal convolution radius r must be given.');
            }
            $Dm = $g->DmToroidal;
        } else {
            if ($g->w === null || $g->w <= 0) {
                throw new EjmaInputException('w', 'Convolution height w must be given.');
            }
            if ($g->rm === null || $g->rm <= 0) {
                throw new EjmaInputException('rm', 'Convolution mean radius rm must be given.');
            }
            $Dm = $g->Db + $g->w + $g->n * $g->t;
        }

        $tp = $g->t * sqrt($g->Db / $Dm);

        if ($toroidal) {
            // §1.3 gives Ac only for U-shaped convolutions and no §4.13.3 equation uses it;
            // a toroidal convolution is approximated as a full circle of radius r.
            $Ac = 2 * M_PI * $g->r * $tp * $g->n;
            $epsilonF = null;
        } else {
            $Ac = (2 * M_PI * $g->rm
                + 2 * sqrt(($g->q / 2 - 2 * $g->rm) ** 2 + ($g->w - 2 * $g->rm) ** 2)) * $tp * $g->n;
            $epsilonF = 100 * sqrt(
                log(1 + 2 * $g->w / $g->Db) ** 2 + log(1 + $g->n * $tp / (2 * $g->rm)) ** 2
            );
        }

        if ($g->Ld !== null) {
            $Ld = $g->Ld;
        } elseif ($g->Lt !== null) {
            $Ld = $g->Lt + $g->q / 2;
        } else {
            throw new EjmaInputException('ld', 'Ld (or tangent length Lt) must be given.');
        }

        $k = $g->Lt === null ? 1.0 : min(1.0, $g->Lt / (1.5 * sqrt($g->Db * $g->t)));

        return new self(
            Dm: $Dm,
            tp: $tp,
            Ac: $Ac,
            Lb: $g->N * $g->q,
            Dc: $g->Db + 2 * $g->n * $g->t + $g->tc,
            Ld: $Ld,
            k: $k,
            epsilonF: $epsilonF,
            Cc: -0.2431 + 0.0168 * $g->ng + 0.3024 * $g->ng ** 2,
            Cr: 0.3 - (100 / (0.6 * $c->P ** 1.5 + 320)) ** 2,
        );
    }
}
