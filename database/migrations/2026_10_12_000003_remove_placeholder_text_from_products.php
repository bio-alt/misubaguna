<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Five sealing pages (gland packing, gasket sheet, mechanical seal, oil seal, O-ring) showed an
 * import placeholder paragraph and internal editing notes ("verify before final import") to
 * visitors and search engines. Remove only that text; real content is untouched.
 */
return new class extends Migration
{
    private const NOTE = '/placeholder|verify before|needs final|final import|todo/i';

    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('public_products')) {
            return;
        }

        foreach (DB::table('public_products')->get(['id', 'content_html', 'technical_specs']) as $row) {
            $html = (string) $row->content_html;
            $html = preg_replace('/<p class="product-placeholder">.*?<\/p>/s', '', $html);
            // A "Technical Notes" section whose every item is an editing note.
            $html = preg_replace_callback('/<h2>Technical Notes<\/h2>\s*<ul>(.*?)<\/ul>/s', function ($m) {
                preg_match_all('/<li>(.*?)<\/li>/s', $m[1], $items);
                $real = array_filter($items[1], fn ($item) => ! preg_match(self::NOTE, $item));

                return $real ? $m[0] : '';
            }, $html);

            $specs = json_decode((string) $row->technical_specs, true);
            $cleanSpecs = is_array($specs)
                ? array_values(array_filter($specs, fn ($spec) => ! preg_match(self::NOTE, (string) $spec)))
                : $specs;

            if ($html !== (string) $row->content_html || $cleanSpecs !== $specs) {
                DB::table('public_products')->where('id', $row->id)->update([
                    'content_html' => $html,
                    'technical_specs' => is_array($cleanSpecs) ? json_encode($cleanSpecs, JSON_UNESCAPED_UNICODE) : $row->technical_specs,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Removes placeholder text only; nothing to restore.
    }
};
