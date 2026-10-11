<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\PublicSite\PublicProduct;
use App\Support\CouplingCatalog;
use App\Support\IndonesianPages;
use App\Support\ProductContent;
use App\Support\PublicSeo;
use Illuminate\Support\Facades\DB;

/**
 * Indonesian-language product pages (/id/...) for buyers who search in Bahasa Indonesia.
 */
class IndonesianProductController extends Controller
{
    public function index()
    {
        app()->setLocale('id');

        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();
        $products = IndonesianPages::products()->map(fn ($p) => IndonesianPages::localize($p));
        $groups = $products->groupBy(fn ($p) => $p->getOriginal('product_group') ?: 'Lainnya');

        $breadcrumbs = [
            ['name' => 'Beranda', 'url' => '/'],
            ['name' => 'Produk Industri', 'url' => IndonesianPages::PREFIX . '/'],
        ];

        $seo = [
            'meta_title' => PublicSeo::title('Supplier Produk Industri di Indonesia'),
            'meta_description' => 'Supplier expansion joint, gland packing, gasket, mechanical seal, lining PTFE dan karet, cooling tower, heat exchanger dan coupling untuk industri di Indonesia.',
            'canonical_url' => IndonesianPages::PREFIX . '/',
            'og_locale' => 'id_ID',
            'alternates' => ['en' => '/catalog/', 'id' => IndonesianPages::PREFIX . '/'],
            'schema_json' => [
                PublicSeo::organizationSchema($siteSettings),
                PublicSeo::breadcrumbSchema($breadcrumbs),
                PublicSeo::itemListSchema('Produk industri PT Misuba Guna Indonesia', $products->map(fn ($p) => [
                    'name' => $p->name,
                    'url' => $p->url_path,
                    'description' => $p->summary,
                ])->all()),
            ],
        ];

        return view('public.pages.id-index', compact('seo', 'siteSettings', 'breadcrumbs', 'groups'));
    }

    public function show(string $slug)
    {
        abort_unless(IndonesianPages::has($slug), 404);
        app()->setLocale('id');

        $english = PublicProduct::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $content = IndonesianPages::content($slug);
        $product = IndonesianPages::localize($english);
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();
        $idUrl = IndonesianPages::url($slug);
        $enUrl = PublicSeo::path($english->url_path ?: '/product/' . $slug . '/');

        $breadcrumbs = [
            ['name' => 'Beranda', 'url' => '/'],
            ['name' => 'Produk Industri', 'url' => IndonesianPages::PREFIX . '/'],
            ['name' => $product->name, 'url' => $idUrl],
        ];

        // Other Indonesian pages from the same product group, so the reader stays in Indonesian.
        $related = IndonesianPages::products()
            ->filter(fn ($p) => $p->slug !== $slug && $p->product_group === $english->product_group)
            ->take(4)
            ->map(fn ($p) => IndonesianPages::localize($p))
            ->values();

        $quoteChecklist = $content['quote_checklist'] ?? ProductContent::quoteChecklist($slug, $english->product_group);
        $faq = PublicSeo::productFaq($product, $quoteChecklist);

        $seo = [
            'meta_title' => PublicSeo::title($content['meta_title'] ?? $product->name),
            'meta_description' => $content['meta_description'],
            'canonical_url' => $idUrl,
            'og_type' => 'product',
            'og_locale' => 'id_ID',
            'og_image_path' => $english->gallery[0] ?? $english->og_image_path,
            'alternates' => ['en' => $enUrl, 'id' => $idUrl],
            'schema_json' => [
                PublicSeo::productSchema($product),
                PublicSeo::breadcrumbSchema($breadcrumbs),
                PublicSeo::faqSchema($faq),
            ],
        ];

        if (CouplingCatalog::isHub($slug)) {
            $seo['schema_json'][] = PublicSeo::itemListSchema('Jenis coupling', array_map(fn ($family) => [
                'name' => $family['name'],
                'url' => $family['url'],
                'description' => $content['families'][$family['slug']] ?? $family['tile'],
            ], CouplingCatalog::families()));
        }

        return view('public.pages.product', [
            'product' => $product,
            'seo' => $seo,
            'siteSettings' => $siteSettings,
            'breadcrumbs' => $breadcrumbs,
            'category' => null,
            'categoryCount' => 0,
            'related' => $related,
            'faq' => $faq,
            'quoteChecklist' => $quoteChecklist,
            'alsoKnownAs' => $content['also_known_as'] ?? [],
            'familyTiles' => $content['families'] ?? [],
            'languageLink' => ['url' => $enUrl, 'label' => 'Read in English', 'lang' => 'en'],
        ]);
    }
}
