<?php

namespace App\Ejma;

/** CheckLine factories shared by the bellows checkers. */
trait BuildsCheckLines
{
    /** value ≤ limit. */
    private static function le(string $key, string $label, string $eq, string $symbol, float $value, float $limit, string $expr): CheckLine
    {
        return new CheckLine($key, $label, $eq, $value, $limit, $expr, $value <= $limit ? LineStatus::Pass : LineStatus::Fail, $symbol);
    }

    /**
     * Movement limits per convolution, §4.2 (p066), U-shaped bellows only:
     * ec ≤ q − 2rm − nt (equalizing-ring spacing does not apply: integral reinforcement only)
     * and ee ≤ 6rm − q (ee per eq. 4-9 at the compression position, ruling P2-R4); and maximum axial compression from instability, §4.7 (p073), all types:
     * ex ≤ 1.25·Dm² / (N²·q), N = total convolutions in all unguided interconnected bellows.
     *
     * @return list<CheckLine>
     */
    private static function movementLimitLines(BellowsGeometry $g, DesignConditions $c, Derived $d, Movement $m, bool $uShaped): array
    {
        $lines = [];
        if ($uShaped) {
            $lines[] = self::le('movement_ec_max', 'Maximum compression per convolution', '§4.2', 'ec', $m->ec, $g->q - 2 * $g->rm - $g->n * $g->t, 'q − 2rm − nt');
            $lines[] = self::le('movement_ee_max', 'Maximum extension per convolution', '§4.2', 'ee', $m->eeLimitCheck, 6 * $g->rm - $g->q, '6rm − q');
        }
        $Ntotal = self::totalConvolutions($g, $c);
        $lines[] = self::le('instability_axial', 'Maximum axial compression per convolution (instability)', '§4.7', 'ex', $m->exComp, 1.25 * $d->Dm ** 2 / ($Ntotal ** 2 * $g->q), '1.25·Dm²/(N²·q)');

        return $lines;
    }

    /** @return list<string> note when the §4.2 extension limit 6rm − q leaves a U-shaped convolution no extension */
    private static function extensionCapacityNotes(BellowsGeometry $g): array
    {
        return 6 * $g->rm - $g->q <= 0 ? ['6rm − q ≤ 0: this convolution geometry admits no extension (EJMA §4.2)'] : [];
    }

    /**
     * §4.4 (p068), ruling P2-R6: when the unsupported centre-spool dead weight is considered,
     * the total stress range St must not exceed 1.5·Cm·Sab. No line when Wcs is not given.
     *
     * @return list<CheckLine>
     */
    private static function deadWeightLines(DesignConditions $c, BellowsProperties $p, float $St): array
    {
        if ($c->assembly->Wcs === null) {
            return [];
        }

        return [self::le('deadweight_St', 'Total stress range with spool dead weight', '§4.4', 'St', $St, 1.5 * $p->Cm * $p->Sab, '1.5·Cm·Sab')];
    }

    /** Total convolutions in the assembly: N per bellows × number of bellows (2 for universal). */
    private static function totalConvolutions(BellowsGeometry $g, DesignConditions $c): int
    {
        return $g->N * $c->assembly->type->bellowsCount();
    }

    /** @return list<string> assembly note for joints other than a single expansion joint */
    private static function assemblyNotes(DesignConditions $c): array
    {
        $type = $c->assembly->type;
        if ($type === AssemblyType::Single) {
            return [];
        }
        $count = $type->bellowsCount();

        return [sprintf('Assembly: %s (%d bellows%s)', $type->label(), $count, $count > 1 ? '; checks apply to each bellows' : '')];
    }

    /** Nc ≥ required cycles; not evaluated without fatigue constants or a required cycle count. */
    private static function fatigueLine(string $eq, ?float $Nc, ?int $required): CheckLine
    {
        $status = match (true) {
            $Nc === null, $required === null => LineStatus::NotEvaluated,
            $Nc >= $required => LineStatus::Pass,
            default => LineStatus::Fail,
        };

        return new CheckLine('fatigue', 'Fatigue life', $eq, $Nc, $required === null ? null : (float) $required, 'required cycles', $status, 'Nc', CheckLine::GE);
    }
}
