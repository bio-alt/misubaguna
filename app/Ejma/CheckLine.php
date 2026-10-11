<?php

namespace App\Ejma;

/**
 * One checked condition: a computed value against an EJMA limit.
 * Non-finite floats are encoded as strings in toArray() so the result survives json_encode.
 */
final readonly class CheckLine
{
    public function __construct(
        public string $key,
        public string $label,
        /** EJMA 9th Ed. equation number, e.g. "4-27". */
        public string $equation,
        public ?float $value,
        public ?float $limit,
        public string $limitExpression,
        public LineStatus $status,
    ) {}

    /** (limit − value) / limit; null when either side is missing or the limit is zero. */
    public function margin(): ?float
    {
        if ($this->value === null || $this->limit === null || $this->limit == 0.0) {
            return null;
        }

        return ($this->limit - $this->value) / $this->limit;
    }

    /** @return array{key: string, label: string, equation: string, value: float|string|null, limit: float|string|null, limit_expression: string, status: string} */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'equation' => $this->equation,
            'value' => JsonFloat::encode($this->value),
            'limit' => JsonFloat::encode($this->limit),
            'limit_expression' => $this->limitExpression,
            'status' => $this->status->value,
        ];
    }

    /** @param  array<string, mixed>  $data */
    public static function fromArray(array $data): self
    {
        return new self(
            key: (string) $data['key'],
            label: (string) $data['label'],
            equation: (string) $data['equation'],
            value: JsonFloat::decode($data['value'] ?? null),
            limit: JsonFloat::decode($data['limit'] ?? null),
            limitExpression: (string) $data['limit_expression'],
            status: LineStatus::from($data['status']),
        );
    }
}
