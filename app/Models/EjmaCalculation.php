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

    /**
     * Next reference (EJ-YYYY-NNNN): highest existing number for the year + 1.
     * Callers should wrap creation in a transaction and retry once on a unique violation.
     */
    public static function nextReference(?CarbonInterface $now = null): string
    {
        $year = ($now ?? Carbon::now())->year;
        $prefix = "EJ-{$year}-";

        $max = 0;
        static::query()
            ->where('reference', 'like', $prefix.'%')
            ->pluck('reference')
            ->each(function (string $reference) use ($prefix, &$max) {
                $max = max($max, (int) substr($reference, strlen($prefix)));
            });

        return $prefix.str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
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
