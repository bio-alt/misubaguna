<?php

namespace App\Ejma;

/**
 * Angular-rotation pressure factors, EJMA 9th Ed. §1.3 nomenclature:
 * Cθ (p018), Kθ (p019), φ (p023).
 */
final class AngularPressureFactor
{
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
