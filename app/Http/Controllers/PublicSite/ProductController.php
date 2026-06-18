<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\PublicSite\PublicProduct;
use App\Support\PublicSeo;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $product = PublicProduct::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();
        $seo = PublicSeo::buildSeoData($product, $siteSettings);
        $seo['schema_json'] = PublicSeo::productSchema($product);

        return view('public.pages.product', compact('product', 'seo', 'siteSettings'));
    }
}