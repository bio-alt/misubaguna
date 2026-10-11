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
        /** Checked quantity symbol, e.g. "S1", "S1'", "S3+S4", "Nc", "Psc". */
        public string $symbol = '',
        /** 'le': value ≤ limit; 'ge': value ≥ limit. */
        public string $comparison = self::LE,
    ) {
        if ($comparison !== self::LE && $comparison !== self::GE) {
            throw new \InvalidArgumentException("Unknown check comparison '$comparison'.");
        }
    }

    public const LE = 'le';

    public const GE = 'ge';

    /**
     * Relative margin, positive when passing: (limit − value)/limit for 'le', (value − limit)/limit for 'ge'.
     * Null when value or limit is missing or non-finite, or the limit is zero or negative
     * (a negative limit, e.g. 6rm − q < 0, would flip the sign of the margin).
     */
    public function margin(): ?float
    {
        if ($this->value === null || $this->limit === null
            || ! is_finite($this->value) || ! is_finite($this->limit) || $this->limit <= 0.0) {
            return null;
        }

        $diff = $this->comparison === self::GE ? $this->value - $this->limit : $this->limit - $this->value;

        return $diff / $this->limit;
    }

    /** @return array{key: string, label: string, equation: string, value: float|string|null, limit: float|string|null, limit_expression: string, status: string, symbol: string, comparison: string} */
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
            'symbol' => $this->symbol,
            'comparison' => $this->comparison,
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
            symbol: (string) ($data['symbol'] ?? ''),
            comparison: (string) ($data['comparison'] ?? self::LE),
        );
    }
}
