<?php

namespace App\Ejma;

use App\Ejma\Data\Material;

final class PropertyResolver
{
    /**
     * Cold-work factor Cm: 1.5 annealed; as-formed 1.5·Ysm clamped to [1.5, 3.0].
     * Ysm per EJMA 9th Ed. §1.3 p021 with x = Kf·εf (εf in %). With no εf (toroidal) Cm = 1.5.
     */
    public static function cm(FormingCondition $condition, FormingMethod $method, MaterialFamily $family, ?float $epsilonF): float
    {
        if ($condition === FormingCondition::Annealed || $epsilonF === null) {
            return 1.5;
        }

        $x = $method->kf() * $epsilonF;
        $ysm = match ($family) {
            MaterialFamily::Austenitic => 1 + 9.94e-2 * $x - 7.59e-4 * $x ** 2 - 2.4e-6 * $x ** 3 + 2.21e-8 * $x ** 4,
            MaterialFamily::Nickel => 1 + 6.8e-2 * $x - 9.11e-4 * $x ** 2 + 9.73e-6 * $x ** 3 - 6.43e-8 * $x ** 4,
            MaterialFamily::Other => 1.0,
        };

        return min(3.0, max(1.5, 1.5 * $ysm));
    }

    /** @param  array<string, float>  $overrides  keys Sab, EbRt, EbDt, Sy, Sym */
    public static function resolve(
        Material $bellows,
        ?Material $collar,
        ?Material $reinf,
        FormingCondition $condition,
        FormingMethod $method,
        Derived $derived,
        float $tF,
        array $overrides,
        float $Cwb,
        float $Cwc,
        float $Cwr,
        BellowsType $type,
    ): BellowsProperties {
        $tC = Units::fToC($tF);
        $Cm = self::cm($condition, $method, $bellows->family, $derived->epsilonF);

        $Sab = $overrides['Sab'] ?? $bellows->saPsi($tC);
        $EbRt = $overrides['EbRt'] ?? $bellows->ePsi(20.0);
        $EbDt = $overrides['EbDt'] ?? $bellows->ePsi($tC);

        if (isset($overrides['Sy'])) {
            $Sy = $overrides['Sy'];
        } else {
            if (! $bellows->hasYield()) {
                throw new EjmaInputException('bellows_material', 'The selected bellows material has no yield data');
            }
            $Syc = $bellows->yieldPsi(20.0);
            $Syh = $bellows->yieldPsi($tC);
            $Sym = $overrides['Sym'] ?? $Syc;
            $Sy = 0.67 * $Cm * $Sym * $Syh / $Syc;
        }

        // A missing collar/reinforcement is only allowed where its values are unused (callers enforce this).
        $collarM = $collar ?? $bellows;
        $reinfM = $reinf ?? $bellows;

        return new BellowsProperties(
            EbRt: $EbRt,
            EbDt: $EbDt,
            EcDt: $collarM->ePsi($tC),
            ErDt: $reinfM->ePsi($tC),
            Sab: $Sab,
            Sac: $collarM->saPsi($tC),
            Sar: $reinfM->saPsi($tC),
            Sy: $Sy,
            Cm: $Cm,
            Cwb: $Cwb,
            Cwc: $Cwc,
            Cwr: $Cwr,
            fatigue: $bellows->fatigue($type),
        );
    }
}
