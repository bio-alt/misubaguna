<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\PublicSite\PublicProductCategory;
use App\Support\PublicSeo;
use Illuminate\Support\Facades\DB;

class ProductCategoryController extends Controller
{
    public function show(string $slug)
    {
        $category = PublicProductCategory::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();
        $seo = PublicSeo::buildSeoData($category, $siteSettings);
        $products = $category->products ?? collect();

        return view('public.pages.category', compact('category', 'seo', 'products', 'siteSettings'));
    }
}