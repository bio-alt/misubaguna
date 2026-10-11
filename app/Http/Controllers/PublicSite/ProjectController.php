<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\PublicSite\PublicPage;
use App\Support\PublicSeo;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index()
    {
        $page = PublicPage::where('url_path', '/project-list/')->where('is_published', true)->firstOrFail();
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();
        $seo = PublicSeo::buildSeoData($page, $siteSettings);

        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Project Experience & References', 'url' => '/project-list/'],
        ];

        $seo['schema_json'] = [
            PublicSeo::organizationSchema($siteSettings),
            PublicSeo::breadcrumbSchema($breadcrumbs),
            [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => 'Project References & Track Record | PT Misuba Guna Indonesia',
                'description' => 'Industrial project reference list and completed maintenance track records by PT Misuba Guna Indonesia across power plants, paper mills, and petrochemical facilities.',
                'url' => PublicSeo::canonicalUrl('/project-list/'),
            ],
        ];

        return view('public.pages.projects', compact('page', 'seo', 'siteSettings', 'breadcrumbs'));
    }
}