<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Three category pages still showed the import placeholder as their lede (and in llms.txt).
 * Only rows that still hold the placeholder are touched, so editor changes are kept.
 */
return new class extends Migration
{
    private const PLACEHOLDER = 'High-quality industrial product designed for reliability and efficiency in demanding operational environments.';

    private const SUMMARIES = [
        'corrosion-protection' => 'PTFE, rubber, ceramic and FRP linings plus ceramic and PTFE coatings that protect tanks, pipes, chutes and process equipment from acid attack, abrasion and erosion.',
        'coating' => 'Ceramic and PTFE (Xylan) coatings for metal parts: protection against heat, erosion and corrosion, with non-stick, low-friction surfaces where needed.',
        'sealing-system' => 'Gland packing, oil seals, O-rings, gasket sheet and mechanical seals for pumps, valves, flanges and rotating equipment.',
    ];

    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('public_product_categories')) {
            return;
        }

        foreach (self::SUMMARIES as $slug => $summary) {
            DB::table('public_product_categories')
                ->where('slug', $slug)
                ->where('summary', self::PLACEHOLDER)
                ->update(['summary' => $summary, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // Content fix; nothing to undo.
    }
};
