<?php

namespace App\Ejma;

use App\Ejma\Data\FormingProfile;

/** One evaluated tooling combination: forming profile × ply thickness t (in) × plies n × convolutions N per bellows. */
final readonly class DesignCandidate
{
    public function __construct(
        public FormingProfile $profile,
        public float $t,
        public int $n,
        public int $N,
        public BellowsGeometry $geometry,
        public CheckResult $result,
    ) {}

    /** Largest value/limit over the 'le' check lines with a finite positive limit (0 when there is none). */
    public function utilisation(): float
    {
        $max = 0.0;
        foreach ($this->result->lines as $line) {
            if ($line->comparison !== CheckLine::LE || $line->value === null || $line->limit === null
                || ! is_finite($line->value) || ! is_finite($line->limit) || $line->limit <= 0.0) {
                continue;
            }
            $max = max($max, $line->value / $line->limit);
        }

        return $max;
    }

    /**
     * How far a candidate is from passing (ruling P3-R5), 1 or less when every line passes:
     * the largest ratio over all check lines, where 'le' lines give value/limit (only a finite
     * positive limit counts), 'ge' lines give limit/value (an infinite value gives 0, a value
     * of 0 or less gives INF) and a line that was not evaluated gives INF.
     */
    public function closeness(): float
    {
        $max = 0.0;
        foreach ($this->result->lines as $line) {
            if ($line->status === LineStatus::NotEvaluated) {
                return INF;
            }
            if ($line->value === null || $line->limit === null || is_nan($line->value)
                || ! is_finite($line->limit) || $line->limit <= 0.0) {
                continue;
            }
            $ratio = match ($line->comparison) {
                CheckLine::GE => match (true) {
                    is_infinite($line->value) && $line->value > 0 => 0.0,
                    $line->value <= 0.0 => INF,
                    default => $line->limit / $line->value,
                },
                default => $line->value / $line->limit,
            };
            $max = max($max, $ratio);
        }

        return $max;
    }

    /** The check that governs a non-passing candidate: the first failing line, else the first line not evaluated. */
    public function governingLine(): ?CheckLine
    {
        $failure = $this->result->firstFailure();
        if ($failure !== null) {
            return $failure;
        }
        foreach ($this->result->lines as $line) {
            if ($line->status === LineStatus::NotEvaluated) {
                return $line;
            }
        }

        return null;
    }

    /** @return array<string, mixed> JSON-safe; inches, without the check result */
    public function toArray(): array
    {
        $g = $this->geometry;

        return [
            'profile' => [
                'id' => $this->profile->id,
                'name' => $this->profile->name,
                'w' => $this->profile->wIn,
                'q' => $this->profile->qIn,
                'rm' => $this->profile->rmIn,
            ],
            't' => $this->t,
            'n' => $this->n,
            'N' => $this->N,
            'utilisation' => $this->utilisation(),
            'geometry' => [
                'type' => $g->type->value,
                'Db' => $g->Db,
                't' => $g->t,
                'n' => $g->n,
                'N' => $g->N,
                'q' => $g->q,
                'w' => $g->w,
                'rm' => $g->rm,
                'Lt' => $g->Lt,
                'tc' => $g->tc,
                'Lc' => $g->Lc,
            ],
        ];
    }
}
