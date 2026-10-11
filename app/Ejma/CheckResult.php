<?php

namespace App\Ejma;

final class CheckResult
{
    /**
     * @param  list<CheckLine>  $lines
     * @param  array<string, float>  $values
     * @param  list<string>  $notes
     */
    public function __construct(
        public readonly BellowsType $type,
        public readonly array $lines,
        public readonly array $values,
        public readonly array $notes = [],
    ) {}

    /** Fail if any line fails; otherwise Review if any line was not evaluated; otherwise Pass. */
    public function verdict(): Verdict
    {
        $review = false;
        foreach ($this->lines as $line) {
            if ($line->status === LineStatus::Fail) {
                return Verdict::Fail;
            }
            $review = $review || $line->status === LineStatus::NotEvaluated;
        }

        return $review ? Verdict::Review : Verdict::Pass;
    }

    public function firstFailure(): ?CheckLine
    {
        foreach ($this->lines as $line) {
            if ($line->status === LineStatus::Fail) {
                return $line;
            }
        }

        return null;
    }

    /** @param  list<CheckLine>  $extra */
    public function withLines(array $extra): self
    {
        return new self($this->type, [...$this->lines, ...$extra], $this->values, $this->notes);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'verdict' => $this->verdict()->value,
            'lines' => array_map(fn (CheckLine $l) => $l->toArray(), $this->lines),
            'values' => array_map(JsonFloat::encode(...), $this->values),
            'notes' => $this->notes,
        ];
    }

    /** @param  array<string, mixed>  $data  as produced by toArray(); 'verdict' is recomputed, not read. */
    public static function fromArray(array $data): self
    {
        return new self(
            BellowsType::from($data['type']),
            array_map(CheckLine::fromArray(...), array_values($data['lines'] ?? [])),
            array_map(JsonFloat::decode(...), $data['values'] ?? []),
            array_values($data['notes'] ?? []),
        );
    }
}
