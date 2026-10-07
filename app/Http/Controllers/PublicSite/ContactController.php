<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\PublicSite\PublicPage;
use App\Support\PublicSeo;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    public function index()
    {
        $page = PublicPage::where('url_path', '/contact-us/')->where('is_published', true)->firstOrFail();
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();
        $seo = PublicSeo::buildSeoData($page, $siteSettings);

        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Contact Us', 'url' => '/contact-us/'],
        ];

        $contactPageSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'ContactPage',
            'name' => 'Contact PT Misuba Guna Indonesia',
            'description' => 'Get in touch with PT Misuba Guna Indonesia for industrial engineering inquiries, expansion joint sizing, quotes, and plant maintenance services.',
            'url' => PublicSeo::canonicalUrl('/contact-us/'),
            'mainEntity' => PublicSeo::localBusinessSchema($siteSettings),
        ];

        $seo['schema_json'] = [
            PublicSeo::organizationSchema($siteSettings),
            $contactPageSchema,
            PublicSeo::breadcrumbSchema($breadcrumbs),
        ];

        return view('public.pages.contact', compact('page', 'seo', 'siteSettings', 'breadcrumbs'));
    }
}