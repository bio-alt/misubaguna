<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Support\IndonesianPages;
use App\Support\ProductContent;
use App\Support\PublicSeo;
use App\Support\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The quote request page (/request-a-quote/ and /id/request-a-quote/): a form that knows
 * the product, asks its quote checklist and takes drawings.
 */
class QuoteRequestController extends Controller
{
    public function create(Request $request)
    {
        $locale = $this->useLocale($request);
        $product = QuoteRequest::product($request->query('product'));
        $checklist = QuoteRequest::checklist($product);
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();

        $prefill = [
            'item' => Str::limit((string) $request->query('item', $product?->name ?? ''), 160, ''),
            'message' => Str::limit((string) $request->query('message', ''), 2000, ''),
        ];

        $breadcrumbs = $this->breadcrumbs($locale, $product);
        $path = QuoteRequest::url();

        $seo = [
            'meta_title' => PublicSeo::title($product
                ? __('Request a quote: :name', ['name' => $product->name])
                : __('Request a quote')),
            'meta_description' => __('Send your sizes, operating conditions and drawings. PT Misuba Guna Indonesia will check the details and quote.'),
            'canonical_url' => $path,
            'robots_index' => false,
            'robots_follow' => true,
            'og_locale' => $locale === 'id' ? 'id_ID' : 'en_US',
            'alternates' => ['en' => QuoteRequest::PATH, 'id' => IndonesianPages::PREFIX.QuoteRequest::PATH],
            'schema_json' => [
                PublicSeo::organizationSchema($siteSettings),
                PublicSeo::breadcrumbSchema($breadcrumbs),
            ],
        ];

        return view('public.pages.quote', [
            'seo' => $seo,
            'siteSettings' => $siteSettings,
            'breadcrumbs' => $breadcrumbs,
            'product' => $product,
            'productUrl' => $product ? $this->productUrl($locale, $product->slug, $product->url_path) : null,
            'checklist' => $checklist,
            'prefill' => $prefill,
            'formAction' => $path,
            'sourceUrl' => $this->sameHostReferrerPath($request),
            'waUrl' => ProductContent::whatsappUrl($product?->name ?? __('your products')),
        ]);
    }

    /** /id/... routes render in Indonesian; everything else in English. */
    private function useLocale(Request $request): string
    {
        $locale = $request->is('id/*') ? 'id' : 'en';
        app()->setLocale($locale);

        return $locale;
    }

    /** The page the buyer came from, as a path, when it is on this site. */
    private function sameHostReferrerPath(Request $request): string
    {
        $referrer = (string) $request->headers->get('referer', '');

        if (parse_url($referrer, PHP_URL_HOST) !== $request->getHost()) {
            return '';
        }

        return Str::limit((string) parse_url($referrer, PHP_URL_PATH), 500, '');
    }

    private function breadcrumbs(string $locale, $product): array
    {
        $crumbs = [['name' => $locale === 'id' ? 'Beranda' : 'Home', 'url' => '/']];

        if ($product) {
            $crumbs[] = ['name' => $product->name, 'url' => $this->productUrl($locale, $product->slug, $product->url_path)];
        }

        $crumbs[] = ['name' => __('Request a quote'), 'url' => QuoteRequest::url()];

        return $crumbs;
    }

    private function productUrl(string $locale, string $slug, ?string $urlPath): string
    {
        if ($locale === 'id' && IndonesianPages::has($slug)) {
            return IndonesianPages::url($slug);
        }

        return PublicSeo::path($urlPath ?: '/product/'.$slug.'/');
    }
}
