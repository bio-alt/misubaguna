<?php

namespace App\Models;

use App\Ejma\CheckResult;
use App\Ejma\Verdict;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class EjmaCalculation extends Model
{
    protected $fillable = [
        'reference',
        'bellows_type',
        'assembly_type',
        'mode',
        'verdict',
        'inputs',
        'results',
        'design',
        'contact',
        'ip_address',
        'emailed_at',
    ];

    protected function casts(): array
    {
        return [
            'inputs' => 'array',
            'results' => 'array',
            'design' => 'array',
            'contact' => 'array',
            'emailed_at' => 'datetime',
        ];
    }

    private const REFERENCE_ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    /**
     * Random reference (EJ-YYYY-XXXXXX) so the number reveals nothing about volume.
     * Callers should still wrap creation in a transaction and retry once on a unique violation.
     */
    public static function nextReference(?CarbonInterface $now = null): string
    {
        $year = ($now ?? Carbon::now())->year;

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $reference = "EJ-{$year}-".static::randomSuffix();

            if (! static::query()->where('reference', $reference)->exists()) {
                return $reference;
            }
        }

        throw new \RuntimeException('Could not generate a unique EJMA reference.');
    }

    protected static function randomSuffix(): string
    {
        $max = strlen(self::REFERENCE_ALPHABET) - 1;
        $suffix = '';
        for ($i = 0; $i < 6; $i++) {
            $suffix .= self::REFERENCE_ALPHABET[random_int(0, $max)];
        }

        return $suffix;
    }

    public function result(): CheckResult
    {
        return CheckResult::fromArray($this->results);
    }

    public function isDesign(): bool
    {
        return $this->mode === 'design';
    }

    public function verdictEnum(): Verdict
    {
        return Verdict::from($this->verdict);
    }
}
