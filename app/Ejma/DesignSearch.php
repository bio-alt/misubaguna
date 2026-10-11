<?php

namespace App\Ejma;

use App\Ejma\Data\FormingProfile;
use App\Ejma\Data\Tooling;

/**
 * Design mode (Phase 3 spec §3.2/§3.3): searches the company tooling for the smallest
 * unreinforced U-shaped bellows, without a reinforcing collar, that passes every EJMA check.
 * Every candidate is evaluated by EjmaCalculator::calculate; no physics is duplicated here.
 */
final class DesignSearch
{
    /**
     * EjmaInputException fields that describe the request itself (material, temperature,
     * pressure, assembly hardware), not the candidate geometry: every candidate would raise
     * them, so they are passed on to the caller as input errors instead of being skipped.
     */
    private const REQUEST_FIELDS = [
        'design_temperature', 'design_pressure', 'bellows_material', 'collar_material',
        'reinforcement_material', 'tie_rods', 'spool_weight',
    ];

    /** Squirm (column instability) pressure Psc ∝ Cθ/N², Cθ independent of N: a failure persists for every larger N. */
    private const COLUMN_SQUIRM = 'squirm_column';

    public function __construct(
        private readonly EjmaCalculator $calc,
        private readonly Tooling $tooling,
    ) {}

    /** @throws EjmaInputException for request-level input errors (see REQUEST_FIELDS) */
    public function search(DesignRequest $r): DesignResult
    {
        if (! $this->tooling->isAvailable()) {
            return new DesignResult(null, [], 0, 0, null);
        }

        $passing = [];
        $closest = null;
        $evaluated = 0;
        $Lt = $this->tooling->tangentLengthIn();

        foreach ($this->tooling->profilesFor($r->Db) as $profile) {
            foreach ($this->tooling->thicknessesIn() as $t) {
                for ($n = 1; $n <= $this->tooling->maxPlies(); $n++) {
                    // Increase N from 1 and stop at the first passing N (spec §3.2).
                    for ($N = 1; $N <= $this->tooling->maxConvolutions(); $N++) {
                        $geometry = self::geometry($profile, $r->Db, $t, $n, $N, $Lt);
                        try {
                            $result = $this->calc->calculate(new CalculationRequest(
                                $geometry, $r->conditions, $r->designTempF, $r->bellowsMaterial, null, null,
                                $r->condition, $r->method, $r->Cwb, 1.0, 1.0,
                            ));
                        } catch (EjmaInputException $e) {
                            if (in_array($e->field, self::REQUEST_FIELDS, true)) {
                                throw $e;
                            }
                            if ($e->field === 'Lu') {
                                break;  // universal: 2Lb (or Lb + x/2) no longer fits within Lu; it only grows with N
                            }

                            continue;   // chart range, Lb too short for the movement, ...: skip this candidate
                        }

                        $evaluated++;
                        $candidate = new DesignCandidate($profile, $t, $n, $N, $geometry, $result);
                        if ($result->verdict() === Verdict::Pass) {
                            $passing[] = $candidate;
                            break;
                        }
                        if ($closest === null || self::compareClosest($candidate, $closest) < 0) {
                            $closest = $candidate;
                        }
                        if (self::fails($result, self::COLUMN_SQUIRM)) {
                            break;
                        }
                    }
                }
            }
        }

        usort($passing, self::compare(...));

        return new DesignResult(
            $passing[0] ?? null,
            array_slice($passing, 1, 2),
            $evaluated,
            count($passing),
            $passing === [] ? $closest : null,
        );
    }

    /**
     * Spec §3.3 ordering, smallest first: (1) convolutions, (2) total metal thickness n·t,
     * (3) plies n, (4) utilisation, (5) profile id. Within one search the bellows count is
     * fixed, so comparing N per bellows orders N × bellows count identically.
     */
    public static function compare(DesignCandidate $a, DesignCandidate $b): int
    {
        if ($a->N !== $b->N) {
            return $a->N <=> $b->N;
        }
        $metal = $a->n * $a->t - $b->n * $b->t;
        if (abs($metal) > 1e-9) {
            return $metal <=> 0.0;
        }
        if ($a->n !== $b->n) {
            return $a->n <=> $b->n;
        }
        $util = $a->utilisation() <=> $b->utilisation();

        return $util !== 0 ? $util : strcmp($a->profile->id, $b->profile->id) <=> 0;
    }

    /** Closest failing candidate: lowest utilisation, ties in §3.3 order. */
    private static function compareClosest(DesignCandidate $a, DesignCandidate $b): int
    {
        return ($a->utilisation() <=> $b->utilisation()) ?: self::compare($a, $b);
    }

    private static function geometry(FormingProfile $p, float $Db, float $t, int $n, int $N, float $Lt): BellowsGeometry
    {
        return new BellowsGeometry(
            type: BellowsType::Unreinforced,
            Db: $Db, t: $t, n: $n, N: $N, q: $p->qIn, w: $p->wIn, rm: $p->rmIn, Lt: $Lt,
        );
    }

    private static function fails(CheckResult $result, string $key): bool
    {
        foreach ($result->lines as $line) {
            if ($line->key === $key) {
                return $line->status === LineStatus::Fail;
            }
        }

        return false;
    }
}
