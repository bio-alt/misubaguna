<?php

namespace App\Support;

use App\Models\PublicSite\PublicProduct;

/**
 * Indonesian versions of the most-searched product pages.
 *
 * Copy lives in resources/content/id/products/<slug>.php (one file per page); the English
 * product row still supplies photos, group and publish state. A page exists in Indonesian
 * only when its content file exists, so adding a file is all it takes to add a page.
 */
class IndonesianPages
{
    public const PREFIX = '/id';

    /** @return array<int, string> */
    public static function slugs(): array
    {
        static $slugs = null;

        if ($slugs === null) {
            $slugs = array_map(fn ($file) => basename($file, '.php'), glob(self::dir() . '/*.php') ?: []);
            sort($slugs);
        }

        return $slugs;
    }

    public static function has(?string $slug): bool
    {
        return $slug !== null && in_array($slug, self::slugs(), true);
    }

    public static function content(string $slug): array
    {
        return self::has($slug) ? require self::dir() . '/' . $slug . '.php' : [];
    }

    public static function url(string $slug): string
    {
        return self::PREFIX . '/product/' . $slug . '/';
    }

    /**
     * The English product with its copy swapped for the Indonesian text. Not saved: the
     * clone only feeds the shared product template.
     */
    public static function localize(PublicProduct $product): PublicProduct
    {
        $c = self::content($product->slug);
        $local = clone $product;

        $local->name = $c['name'];
        $local->summary = $c['summary'];
        $local->meta_description = $c['meta_description'];
        $local->content_html = $c['description_html'] ?? '';
        $local->key_features = $c['key_features'] ?? [];
        $local->applications = $c['applications'] ?? [];
        $local->materials = $c['materials'] ?? [];
        $local->technical_specs = $c['technical_specs'] ?? [];
        $local->product_subgroup = $c['kicker'] ?? $product->product_subgroup;
        $local->url_path = self::url($product->slug);
        $local->canonical_url = self::url($product->slug);

        return $local;
    }

    /** Published English products that have an Indonesian page, in content-file order. */
    public static function products()
    {
        return PublicProduct::where('is_published', true)
            ->whereIn('slug', self::slugs())
            ->orderBy('product_group')
            ->orderBy('sort_order')
            ->get();
    }

    private static function dir(): string
    {
        return resource_path('content/id/products');
    }
}
