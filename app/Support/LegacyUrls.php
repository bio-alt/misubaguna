<?php

namespace App\Support;

use App\Models\PublicSite\PublicProduct;

/**
 * Resolves URLs left over from the old WordPress site that Google still crawls.
 * Only consulted when no real route matches, so it never shadows a live page.
 */
class LegacyUrls
{
    /** Old paths whose new home is not derivable from the slug. */
    private const MAP = [
        '/new-home/' => '/',
        '/flexible-joint/' => '/product/rubber-expansion-joint/',
        '/product-category/flexible-joint/' => '/product/rubber-expansion-joint/',
        '/sparepart-fabrication/' => '/services/',
        '/safety-equipment/' => '/catalog/',
        '/metform-form/inquiries/' => '/request-a-quote/',
    ];

    /** WordPress system paths with no replacement: tell crawlers they are gone for good. */
    private const GONE_PREFIXES = ['/wp-json/', '/wp-content/', '/wp-admin/', '/wp-includes/', '/metform-form/'];

    /**
     * @return array{0: int, 1: ?string}|null [status, absolute target] or null when the path is unknown
     */
    public static function resolve(string $path): ?array
    {
        $path = '/' . trim($path, '/') . '/';

        // WordPress feeds (/x/feed/, /x/y/feed/) point at the page they belong to.
        if (preg_match('#^(.*/)feed/$#', $path, $m)) {
            if ($m[1] === '/') {
                return [301, PublicSeo::canonicalUrl('/')];
            }

            return self::resolve($m[1]) ?? [410, null];
        }

        if (isset(self::MAP[$path])) {
            return [301, PublicSeo::canonicalUrl(self::MAP[$path])];
        }

        // /fabric-expansion-joint/, /products/x/ and retired /product-category/x/ all become /product/x/.
        if (preg_match('#^/(?:products/|product-category/)?([a-z0-9-]+)/$#', $path, $m) && self::productExists($m[1])) {
            return [301, PublicSeo::canonicalUrl('/product/' . $m[1] . '/')];
        }

        if (preg_match('#^/product-category/[a-z0-9-]+/$#', $path)) {
            return [301, PublicSeo::canonicalUrl('/catalog/')];
        }

        if (preg_match('#^/wp-[a-z-]+\.php/$#', $path)) {
            return [410, null];
        }

        foreach (self::GONE_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return [410, null];
            }
        }

        return null;
    }

    public static function productExists(string $slug): bool
    {
        return PublicProduct::where('slug', $slug)->where('is_published', true)->exists();
    }
}
