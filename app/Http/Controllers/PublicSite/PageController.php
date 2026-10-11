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

        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'About Us', 'url' => '/about-us/'],
        ];

        $aboutPageSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'AboutPage',
            'name' => 'About PT Misuba Guna Indonesia',
            'description' => 'Learn about PT Misuba Guna Indonesia, leading industrial engineering supplier and maintenance contractor in Indonesia since 2011.',
            'url' => PublicSeo::canonicalUrl('/about-us/'),
            'mainEntity' => [
                '@id' => PublicSeo::DEFAULT_DOMAIN . '/#organization',
            ],
        ];

        $seo['schema_json'] = [
            PublicSeo::organizationSchema($siteSettings),
            $aboutPageSchema,
            PublicSeo::breadcrumbSchema($breadcrumbs),
        ];

        return view('public.pages.about', compact('page', 'seo', 'siteSettings', 'breadcrumbs'));
    }
}