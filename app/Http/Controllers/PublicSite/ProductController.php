<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\PublicSite\PublicProduct;
use App\Models\PublicSite\PublicProductCategory;
use App\Support\PublicSeo;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $product = PublicProduct::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();
        $seo = PublicSeo::buildSeoData($product, $siteSettings);

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

        $seo['schema_json'] = [
            PublicSeo::productSchema($product),
            PublicSeo::breadcrumbSchema($breadcrumbs),
        ];

        return view('public.pages.product', compact('product', 'seo', 'siteSettings', 'breadcrumbs', 'category', 'categoryCount', 'related'));
    }
}