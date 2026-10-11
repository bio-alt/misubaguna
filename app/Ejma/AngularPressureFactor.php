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
     * φ, ratio of design pressure to critical pressure (p023):
     * PN²q/(0.764π fiu) unreinforced, PN²q/(0.675π fir) reinforced, PN²r/(0.338π fit) toroidal.
     *
     * @param  float  $qOrR  pitch q (U-shaped) or convolution radius r (toroidal)
     * @param  float  $fi  theoretical axial spring rate per convolution for that bellows type
     */
    public static function phi(BellowsType $type, float $P, int $N, float $qOrR, float $fi): float
    {
        $k = match ($type) {
            BellowsType::Unreinforced => 0.764,
            BellowsType::Reinforced => 0.675,
            BellowsType::Toroidal => 0.338,
        };

        return $P * $N ** 2 * $qOrR / ($k * M_PI * $fi);
    }
}
