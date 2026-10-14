<?php

namespace App\Support;

use App\Models\PublicSite\PublicProduct;

/**
 * Links to the quote request page and the product/checklist it asks about.
 *
 * The form and its submit handler both resolve the checklist here, so the labels stored
 * with an answer are always the server's own, never whatever the browser posted.
 */
class QuoteRequest
{
    public const PATH = '/request-a-quote/';

    /** Quote page link for the current locale, e.g. /request-a-quote/?product=rubber-expansion-joint. */
    public static function url(?string $slug = null, ?string $item = null, ?string $message = null): string
    {
        $query = http_build_query(array_filter([
            'product' => $slug,
            'item' => $item,
            'message' => $message,
        ], fn ($value) => $value !== null && $value !== ''));

        return self::base(app()->getLocale()) . ($query !== '' ? '?' . $query : '');
    }

    public static function thanksUrl(string $reference, string $locale): string
    {
        return self::base($locale) . 'thanks/' . rawurlencode($reference) . '/';
    }

    /** The published product for a slug, in Indonesian on /id/ when it has a translation. */
    public static function product(?string $slug): ?PublicProduct
    {
        if ($slug === null || $slug === '') {
            return null;
        }

        $product = PublicProduct::where('slug', $slug)->where('is_published', true)->first();

        if ($product && app()->getLocale() === 'id' && IndonesianPages::has($slug)) {
            return IndonesianPages::localize($product);
        }

        return $product;
    }

    /** @return array<int, string> */
    public static function checklist(?PublicProduct $product): array
    {
        if ($product && app()->getLocale() === 'id' && IndonesianPages::has($product->slug)) {
            $local = IndonesianPages::content($product->slug)['quote_checklist'] ?? null;
            if (! empty($local)) {
                return array_values($local);
            }
        }

        return array_values(ProductContent::quoteChecklist($product?->slug, $product?->product_group));
    }

    private static function base(string $locale): string
    {
        return ($locale === 'id' ? IndonesianPages::PREFIX : '') . self::PATH;
    }
}
