<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\PublicSite\PublicProduct;
use App\Models\PublicSite\PublicProductCategory;
use App\Support\PublicSeo;
use Illuminate\Support\Facades\DB;

class ProductCategoryController extends Controller
{
    public function show(string $slug)
    {
        $category = PublicProductCategory::where('slug', $slug)->where('is_published', true)->firstOrFail();

        $products = $category->productsQuery()->orderBy('sort_order')->get();

        // A category with nothing in it is a dead end for visitors and search engines.
        if ($products->isEmpty()) {
            return redirect('/catalog/', 301);
        }

        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();
        $seo = PublicSeo::buildSeoData($category, $siteSettings);

        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Catalog', 'url' => '/catalog/'],
            ['name' => $category->name, 'url' => '/product-category/' . $category->slug . '/'],
        ];

        $itemListElements = [];
        foreach ($products as $idx => $prod) {
            $itemListElements[] = [
                '@type' => 'ListItem',
                'position' => $idx + 1,
                'name' => $prod->name,
                'url' => PublicSeo::canonicalUrl($prod->url_path ?: ('/product/' . $prod->slug . '/')),
            ];
        }

        $collectionSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $category->name . ' | PT Misuba Guna Indonesia',
            'description' => $category->description ?: ($category->name . ' industrial products and engineering solutions.'),
            'url' => PublicSeo::canonicalUrl('/product-category/' . $category->slug . '/'),
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => count($itemListElements),
                'itemListElement' => $itemListElements,
            ],
        ];

        $seo['schema_json'] = [
            $collectionSchema,
            PublicSeo::breadcrumbSchema($breadcrumbs),
        ];

        return view('public.pages.category', compact('category', 'seo', 'products', 'siteSettings', 'breadcrumbs'));
    }
}