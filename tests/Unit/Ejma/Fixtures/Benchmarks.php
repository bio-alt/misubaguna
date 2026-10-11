<?php

namespace Tests\Unit\Ejma\Fixtures;

use App\Ejma\AssemblyGeometry;
use App\Ejma\AssemblyType;
use App\Ejma\BellowsGeometry;
use App\Ejma\BellowsProperties;
use App\Ejma\BellowsType;
use App\Ejma\DesignConditions;
use App\Ejma\FatigueConstants;

/**
 * Inputs of the EJMA 9th Ed. §4.14 benchmark calculations (p097), used as realistic
 * geometry by the engine tests. The published results (p098) are private:
 * tests/Private/Ejma/Fixtures/BenchmarkResults.php (git-ignored).
 * w is derived from Db + 2(w + nt) = 27. Sab/Sac/Sar/Cm are not part of the
 * benchmark and are set to 20000/20000/20000/1.5.
 */
final class Benchmarks
{
    /** @return array{geometry: BellowsGeometry, conditions: DesignConditions, properties: BellowsProperties} */
    public static function case1(): array
    {
        return [
            'geometry' => new BellowsGeometry(
                type: BellowsType::Unreinforced,
                Db: 24, t: 0.03, n: 2, N: 12, q: 1, w: 1.44, rm: 0.25, Lt: 1,
            ),
            'conditions' => new DesignConditions(
                P: 40, xComp: 1, xExt: 0.5, y: 0.06, theta: deg2rad(5), requiredCycles: null,
            ),
            'properties' => self::properties(BellowsType::Unreinforced, 0.0, 0.0),
        ];
    }

    /**
     * Universal unreinforced (p097/p098 column 2); N is per bellows.
     *
     * @return array{geometry: BellowsGeometry, conditions: DesignConditions, properties: BellowsProperties}
     */
    public static function case2(): array
    {
        return [
            'geometry' => new BellowsGeometry(
                type: BellowsType::Unreinforced,
                Db: 24, t: 0.03, n: 2, N: 4, q: 1, w: 1.44, rm: 0.25, Lt: 1.25, tc: 0.25, Lc: 1,
            ),
            'conditions' => new DesignConditions(
                P: 100, xComp: 1, xExt: 0.5, y: 2, theta: 0, requiredCycles: null,
                assembly: new AssemblyGeometry(type: AssemblyType::Universal, Lu: 36),
            ),
            'properties' => self::properties(BellowsType::Unreinforced, 27.3e6, 0.0),
        ];
    }

    /** @return array{geometry: BellowsGeometry, conditions: DesignConditions, properties: BellowsProperties} */
    public static function case3(): array
    {
        return [
            'geometry' => new BellowsGeometry(
                type: BellowsType::Reinforced,
                Db: 24, t: 0.03, n: 2, N: 8, q: 1, w: 1.44, rm: 0.25, Lt: 1.25,
                tc: 0.375, Ar: 0.15, Atc: 0.43, Zc: 0.0872, ng: 8, fw: 14751,
            ),
            'conditions' => new DesignConditions(
                P: 100, xComp: 1, xExt: 0.5, y: 0, theta: deg2rad(5), requiredCycles: null,
            ),
            'properties' => self::properties(BellowsType::Reinforced, 27.3e6, 26.6e6),
        ];
    }

    /** @return array{geometry: BellowsGeometry, conditions: DesignConditions, properties: BellowsProperties} */
    public static function case4(): array
    {
        return [
            'geometry' => new BellowsGeometry(
                type: BellowsType::Toroidal,
                Db: 24, t: 0.05, n: 1, N: 2, q: 3.4625, w: null, rm: null, Lt: null,
                tc: 1, Ld: 2.106, Ar: 2.282, Atc: 1.27, Zc: 0.271, ng: 12,
                r: 1, Dr: 25.1, DmToroidal: 27.496, fw: 15714,
            ),
            'conditions' => new DesignConditions(
                P: 500, xComp: 0.75, xExt: 0, y: 0, theta: 0, requiredCycles: null,
            ),
            'properties' => self::properties(BellowsType::Toroidal, 27.3e6, 26.6e6),
        ];
    }

    private static function properties(BellowsType $type, float $EcDt, float $ErDt): BellowsProperties
    {
        return new BellowsProperties(
            EbRt: 28.3e6, EbDt: 25.8e6, EcDt: $EcDt, ErDt: $ErDt,
            Sab: 20000, Sac: 20000, Sar: 20000, Sy: 59726, Cm: 1.5,
            fatigue: FatigueConstants::ejmaAustenitic($type),
        );
    }
}
