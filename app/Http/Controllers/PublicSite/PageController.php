<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\PublicSite\PublicPage;
use App\Support\PublicSeo;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function about()
    {
        $page = PublicPage::where('url_path', '/about-us/')->where('is_published', true)->firstOrFail();
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();
        $seo = PublicSeo::buildSeoData($page, $siteSettings);

        return view('public.pages.about', compact('page', 'seo', 'siteSettings'));
    }
}