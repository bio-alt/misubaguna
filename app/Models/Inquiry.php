<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A quote request or contact-form inquiry, stored before it is emailed so no lead is lost.
 */
class Inquiry extends Model
{
    public const KIND_QUOTE = 'quote';

    public const KIND_CONTACT = 'contact';

    private const REFERENCE_ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    protected $fillable = [
        'kind', 'reference', 'product_slug', 'product_name', 'item',
        'name', 'company', 'email', 'phone', 'location', 'category',
        'answers', 'message', 'files', 'locale', 'source_url', 'ip_address',
        'emailed_at', 'confirmed_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'files' => 'array',
        'emailed_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    /**
     * Random reference (Q-YYYY-XXXXXX) so the number reveals nothing about volume.
     */
    public static function nextReference(): string
    {
        $year = Carbon::now()->year;
        $max = strlen(self::REFERENCE_ALPHABET) - 1;

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $suffix = '';
            for ($i = 0; $i < 6; $i++) {
                $suffix .= self::REFERENCE_ALPHABET[random_int(0, $max)];
            }
            $reference = "Q-{$year}-{$suffix}";

            if (! static::query()->where('reference', $reference)->exists()) {
                return $reference;
            }
        }

        throw new \RuntimeException('Could not generate a unique quote reference.');
    }

    /** Total size in bytes of the stored attachments. */
    public function filesTotalSize(): int
    {
        return (int) array_sum(array_column($this->files ?? [], 'size'));
    }
}
