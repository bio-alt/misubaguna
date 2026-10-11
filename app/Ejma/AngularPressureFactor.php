<?php

namespace App\Ejma;

/**
 * Angular-rotation pressure factors, EJMA 9th Ed. §1.3 nomenclature:
 * Cθ (p018), Kθ (p019), φ (p023).
 */
final readonly class AngularPressureFactor
{
    private function __construct(
        /** Column instability pressure reduction factor Cθ (p018). */
        public float $Ctheta,
        /** Pressure ratio φ used in Kθ (see phi()). */
        public float $phi,
        /** Equivalent axial movement per convolution due to angular rotation fed to Kθ. */
        public float $etheta,
        /** Angular pressure effect factor Kθ (p019). */
        public float $Ktheta,
    ) {}

    /**
     * Cθ, φ, eθ and Kθ for each bellows of the assembly (controller ruling P2-R2), shared by
     * the three bellows checkers:
     *  - eθ is the assembly's per-convolution eθ, the same one Movement uses (eq. 4-3 single /
     *    hinged, eq. 4-4 universal, eq. 4-3 on the resultant θ for gimbal);
     *  - Cθ uses θm = rotation of the individual bellows (θ/2 universal, resultant gimbal) and
     *    judges "concurrent lateral deflection" on the design lateral the assembly applies
     *    (none for hinged/gimbal). The universal centre-spool sag (eq. 4-13) is not counted as
     *    lateral deflection here: Cθ < 1 lowers both Psc and Kθ, so this is the conservative side;
     *  - φ uses the total convolutions N × bellowsCount, consistent with the universal Psc
     *    (notes to eqs. 4-35 / 4-49 / 4-60).
     *
     * @param  int  $N  convolutions per bellows
     * @param  float  $q  convolution pitch (Kθ, p019)
     * @param  float  $qOrR  q (U-shaped) or r (toroidal) for φ
     * @param  float  $fi  theoretical axial spring rate per convolution for the bellows type
     */
    public static function forAssembly(BellowsType $type, DesignConditions $c, float $Dm, int $N, float $q, float $qOrR, float $fi): self
    {
        $Ctheta = self::Ctheta(Movement::appliedLateral($c), Movement::bellowsRotation($c));
        $phi = self::phi($type, $c->P, $N * $c->assembly->type->bellowsCount(), $qOrR, $fi);
        $etheta = Movement::assemblyEtheta($c, $Dm, $N);

        return new self($Ctheta, $phi, $etheta, self::Ktheta($etheta, $Ctheta, $q, $phi));
    }

    /**
     * Column instability pressure reduction factor Cθ (p018):
     * 0.1157^θm if no concurrent lateral deflection, 1 if lateral deflection is present.
     * θm = maximum angular rotation from straight position (radians, p023).
     */
    public static function Ctheta(float $y, float $thetaM): float
    {
        return $y != 0.0 ? 1.0 : 0.1157 ** abs($thetaM);
    }

    /** Kθ (p019): eθCθ / (eθCθ + 0.15qφ) if Cθ < 1, else 1. */
    public static function Ktheta(float $etheta, float $Ctheta, float $q, float $phi): float
    {
        if ($Ctheta >= 1.0) {
            return 1.0;
        }

        return $etheta * $Ctheta / ($etheta * $Ctheta + 0.15 * $q * $phi);
    }

    /**
     * φ, pressure ratio used in Kθ (p019).
     *
     * p023 defines φ as design pressure over the *critical* pressure, with denominators
     * 0.764π fiu / 0.675π fir / 0.338π fit, i.e. 2.25× the column-squirm limiting pressure
     * of eqs. 4-35 / 4-49 / 4-60 without Cθ (2.25 = critical/limiting ratio, §4.12.1.6 p089).
     * The only benchmark that exercises Kθ (§4.14 benchmark 3, p098: θ = 5°, y = 0) is
     * reproduced only with φ = P / limiting pressure without Cθ: e = 0.3767 vs published 0.3756,
     * against e = 0.3488 with the p023 form. That form gives a smaller Kθ, a larger e, higher
     * deflection stresses and lower fatigue life, so it is deliberately adopted (conservative)
     * for all three bellows types: PN²q/(0.34π fiu), PN²q/(0.3π fir), PN²r/(0.15π fit).
     *
     * @param  float  $qOrR  pitch q (U-shaped) or convolution radius r (toroidal)
     * @param  float  $fi  theoretical axial spring rate per convolution for that bellows type
     */
    public static function phi(BellowsType $type, float $P, int $N, float $qOrR, float $fi): float
    {
        $k = match ($type) {
            BellowsType::Unreinforced => 0.34,   // eq. 4-35 (p092); p023 text: 0.764
            BellowsType::Reinforced => 0.3,      // eq. 4-49 (p094); p023 text: 0.675
            BellowsType::Toroidal => 0.15,       // eq. 4-60;        p023 text: 0.338
        };

        return $P * $N ** 2 * $qOrR / ($k * M_PI * $fi);
    }
}
