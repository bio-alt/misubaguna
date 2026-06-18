<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\PublicSite\PublicPage;
use App\Models\PublicSite\PublicProduct;
use App\Models\PublicSite\PublicProductCategory;
use App\Support\PublicSeo;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $page = PublicPage::where('url_path', '/')->where('is_published', true)->firstOrFail();
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();
        $seo = PublicSeo::buildSeoData($page, $siteSettings);
        $products = PublicProduct::where('is_published', true)->orderBy('sort_order')->get();
        $categories = PublicProductCategory::where('is_published', true)->orderBy('sort_order')->get();

        return view('public.pages.home', compact('page', 'seo', 'products', 'categories', 'siteSettings'));
    }
}