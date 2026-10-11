<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\PublicSite\PublicProduct;
use App\Models\PublicSite\PublicProductCategory;
use App\Support\CouplingCatalog;
use App\Support\IndonesianPages;
use App\Support\ProductContent;
use App\Support\PublicSeo;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $product = PublicProduct::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();
        $seo = PublicSeo::buildSeoData($product, $siteSettings);

        // Buyers search "<product> supplier Indonesia"; lead with that unless an editor wrote a custom title.
        $supplierTitle = PublicSeo::title($product->name . ' Supplier in Indonesia');
        if (PublicSeo::title($product->meta_title) === PublicSeo::title($product->name) && mb_strlen($supplierTitle) <= PublicSeo::TITLE_MAX) {
            $seo['meta_title'] = $supplierTitle;
            $seo['og_title'] = $seo['meta_title'];
        }
        $seo['og_type'] = 'product';
        if (empty($product->og_image_path) && ! empty($product->gallery[0] ?? null)) {
            $seo['og_image_path'] = $product->gallery[0];
        }

        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Catalog', 'url' => '/catalog/'],
        ];

        $category = null;
        $related = collect();

        if ($product->product_group) {
            $category = PublicProductCategory::where('name', $product->product_group)->where('is_published', true)->first();
            if ($category) {
                $breadcrumbs[] = ['name' => $category->name, 'url' => '/product-category/' . $category->slug . '/'];
            }

            // Same group, closest subgroup first, so the buyer keeps browsing.
            $related = PublicProduct::where('is_published', true)
                ->where('product_group', $product->product_group)
                ->where('id', '!=', $product->id)
                ->orderByRaw('CASE WHEN product_subgroup = ? THEN 0 ELSE 1 END', [(string) $product->product_subgroup])
                ->orderBy('sort_order')
                ->limit(4)
                ->get();
        }

        $categoryCount = $category ? $category->productsQuery()->count() : 0;

        $breadcrumbs[] = ['name' => $product->title ?? $product->name, 'url' => $product->url_path ?: ('/product/' . $product->slug . '/')];

        $faq = PublicSeo::productFaq($product, ProductContent::quoteChecklist($product->slug, $product->product_group));

        $seo['schema_json'] = [
            PublicSeo::productSchema($product),
            PublicSeo::breadcrumbSchema($breadcrumbs),
            PublicSeo::faqSchema($faq),
        ];

        // Coupling hub and family pages: expose the families / series as a list machines can read.
        if (CouplingCatalog::isHub($product->slug)) {
            $seo['schema_json'][] = PublicSeo::itemListSchema($product->name . ' families', array_map(fn ($family) => [
                'name' => $family['name'],
                'url' => $family['url'],
                'description' => $family['tile'],
            ], CouplingCatalog::families()));
        } elseif ($family = CouplingCatalog::family($product->slug)) {
            $seo['schema_json'][] = PublicSeo::itemListSchema($family['name'] . ' series', array_map(fn ($series) => [
                'name' => $series['code'] . ' ' . $series['name'],
                'description' => trim($series['line'] . ' ' . implode('; ', $series['facts'])),
                'image' => $series['image'],
            ], $family['series']));
        }

        $languageLink = null;
        if (IndonesianPages::has($product->slug)) {
            $seo['alternates'] = ['en' => $product->url_path ?: '/product/' . $product->slug . '/', 'id' => IndonesianPages::url($product->slug)];
            $languageLink = ['url' => IndonesianPages::url($product->slug), 'label' => 'Baca dalam Bahasa Indonesia', 'lang' => 'id'];
        }

        return view('public.pages.product', compact('product', 'seo', 'siteSettings', 'breadcrumbs', 'category', 'categoryCount', 'related', 'faq', 'languageLink'));
    }
}