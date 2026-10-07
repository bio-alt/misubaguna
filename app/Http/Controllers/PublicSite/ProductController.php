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
        if ($slug === 'membrane-filter-press') {
            return redirect('/product/filter-plate-press/', 301);
        }

        $product = PublicProduct::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();
        $seo = PublicSeo::buildSeoData($product, $siteSettings);

        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Catalog', 'url' => '/catalog/'],
        ];

        if ($product->product_group) {
            $cat = PublicProductCategory::where('name', $product->product_group)->first();
            if ($cat) {
                $breadcrumbs[] = ['name' => $cat->name, 'url' => '/product-category/' . $cat->slug . '/'];
            }
        }

        $breadcrumbs[] = ['name' => $product->title ?? $product->name, 'url' => $product->url_path ?: ('/product/' . $product->slug . '/')];

        $seo['schema_json'] = [
            PublicSeo::productSchema($product),
            PublicSeo::breadcrumbSchema($breadcrumbs),
        ];

        return view('public.pages.product', compact('product', 'seo', 'siteSettings', 'breadcrumbs'));
    }
}