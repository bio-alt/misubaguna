<?php

namespace App\Ejma;

use App\Ejma\Data\Material;
use App\Ejma\Data\MaterialRepository;

final class EjmaCalculator
{
    public function __construct(private readonly MaterialRepository $materials) {}

    public function calculate(CalculationRequest $r): CheckResult
    {
        $type = $r->geometry->type;
        $bellows = $this->material($r->bellowsMaterial, 'bellows_material', 'bellows');
        $needsCollar = $type === BellowsType::Toroidal || $r->geometry->tc > 0;
        $collar = $needsCollar || $r->collarMaterial !== null
            ? $this->material((string) $r->collarMaterial, 'collar_material', 'collar')
            : null;
        $needsReinf = $type !== BellowsType::Unreinforced;
        $reinf = $needsReinf
            ? $this->material((string) $r->reinforcementMaterial, 'reinforcement_material', 'reinforcement')
            : null;

        $tC = Units::fToC($r->designTempF);
        foreach ([$bellows, $collar, $reinf] as $m) {
            if ($m !== null && $m->creepLimitC !== null && $tC > $m->creepLimitC) {
                throw new EjmaInputException('design_temperature', 'Design temperature is in the creep range; contact us for a creep-range design');
            }
        }

        $derived = Derived::of($r->geometry, $r->conditions);
        $props = PropertyResolver::resolve(
            $bellows, $collar, $reinf, $r->condition, $r->method, $derived, $r->designTempF,
            $r->overrides, $r->Cwb, $r->Cwc, $r->Cwr, $type,
        );

        $checker = match ($type) {
            BellowsType::Unreinforced => new UnreinforcedBellows,
            BellowsType::Reinforced => new ReinforcedBellows,
            BellowsType::Toroidal => new ToroidalBellows,
        };
        $result = $checker->check($r->geometry, $r->conditions, $props);

        $notes = [...$result->notes, 'Lateral and angular movements are assumed concurrent with axial compression (EJMA benchmark convention).'];
        if ($derived->epsilonF === null && $r->condition === FormingCondition::AsFormed) {
            $notes[] = 'Forming strain is not defined for toroidal bellows; Cm = 1.5 was used.';
        }
        $used = array_keys(array_filter($r->overrides, fn ($v) => $v !== null));
        if (in_array('Sy', $used, true)) {
            $used = array_values(array_diff($used, ['Sym']));
        }
        if ($used !== []) {
            $notes[] = 'Overrides used: '.implode(', ', $used);
        }

        return new CheckResult(
            $result->type,
            $result->lines,
            [...$result->values, ...Loads::of($r->geometry, $r->conditions, $derived, $result), ...self::propertyValues($props, $collar !== null, $reinf !== null)],
            $notes,
        );
    }

    /**
     * Resolved material properties actually used (psi / dimensionless), stored so reports can print them.
     *
     * @return array<string, float>
     */
    private static function propertyValues(BellowsProperties $p, bool $collar, bool $reinf): array
    {
        $out = ['prop_Sab' => $p->Sab, 'prop_EbRt' => $p->EbRt, 'prop_EbDt' => $p->EbDt, 'prop_Sy' => $p->Sy, 'prop_Cm' => $p->Cm, 'prop_Cwb' => $p->Cwb];
        if ($collar) {
            $out += ['prop_Sac' => $p->Sac, 'prop_EcDt' => $p->EcDt, 'prop_Cwc' => $p->Cwc];
        }
        if ($reinf) {
            $out += ['prop_Sar' => $p->Sar, 'prop_ErDt' => $p->ErDt, 'prop_Cwr' => $p->Cwr];
        }

        return $out;
    }

    private function material(string $id, string $field, string $role): Material
    {
        $m = $this->materials->find($id);
        if ($m === null || ! $m->usableFor($role)) {
            throw new EjmaInputException($field, 'Selected material is not available');
        }

        return $m;
    }
}
