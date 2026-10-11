<?php

namespace App\Ejma;

/** Outcome of a design search (Phase 3 spec §3.4). */
final readonly class DesignResult
{
    /** @param  list<DesignCandidate>  $alternatives  up to two runner-up (profile, t, n) combinations, best first */
    public function __construct(
        public ?DesignCandidate $best,
        public array $alternatives,
        /** Candidates the calculator returned a result for (candidates skipped on input errors are not counted). */
        public int $evaluated,
        public int $passing,
        /** Lowest-utilisation candidate that did not pass, set only when nothing passed. */
        public ?DesignCandidate $closestFailing,
    ) {}

    /** Pass when a design was found, otherwise Review (needs engineering review). */
    public function verdict(): Verdict
    {
        return $this->best !== null ? Verdict::Pass : Verdict::Review;
    }

    /** Explanation for a Review result; null when a design was found. */
    public function reviewNote(): ?string
    {
        if ($this->best !== null) {
            return null;
        }
        if ($this->closestFailing === null) {
            return sprintf('No tooling candidate could be evaluated for these conditions (%d evaluated); a custom design is needed.', $this->evaluated);
        }

        $c = $this->closestFailing;
        $line = $c->governingLine();

        return sprintf(
            'No tooling candidate passed all EJMA checks (%d evaluated). Closest: profile %s, t = %.4f in, %d %s, N = %d%s.',
            $this->evaluated, $c->profile->name, $c->t, $c->n, $c->n === 1 ? 'ply' : 'plies', $c->N,
            $line !== null ? sprintf('; governing check: %s (eq. %s)', $line->label, $line->equation) : '',
        );
    }

    /** @return array<string, mixed> JSON-safe summary (inches); check results are not included. */
    public function toArray(): array
    {
        $closest = null;
        if ($this->closestFailing !== null) {
            $line = $this->closestFailing->governingLine();
            $closest = $this->closestFailing->toArray() + [
                'verdict' => $this->closestFailing->result->verdict()->value,
                'governing' => $line?->toArray(),
            ];
        }

        return [
            'verdict' => $this->verdict()->value,
            'evaluated' => $this->evaluated,
            'passing' => $this->passing,
            'best' => $this->best?->toArray(),
            'alternatives' => array_map(fn (DesignCandidate $c) => $c->toArray(), $this->alternatives),
            'closest_failing' => $closest,
        ];
    }
}
