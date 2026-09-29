<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\PublicSite\PublicPage;
use App\Models\PublicSite\PublicProduct;
use App\Models\PublicSite\PublicService;
use App\Support\PublicSeo;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index()
    {
        $page = PublicPage::where('url_path', '/services/')->first() ?? (object) [
            'title' => 'Industrial Engineering Services | PT Misuba Guna Indonesia',
            'headline' => 'Industrial Field Services & Technical Repair',
            'summary' => 'PT Misuba Guna Indonesia provides comprehensive industrial services including cooling tower repair, heat exchanger service, thermal spray coating, protective lining, expansion joint installation, and on-site mechanical maintenance.',
            'url_path' => '/services/',
            'canonical_url' => url('/services/'),
            'robots_index' => true,
            'robots_follow' => true,
        ];

        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();
        $services = PublicService::where('is_published', true)->orderBy('sort_order')->get();
        $seo = PublicSeo::buildSeoData($page, $siteSettings);

        $breadcrumbs = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Services', 'url' => url('/services/')],
        ];

        $seo['schema_json'] = [
            PublicSeo::organizationSchema($siteSettings),
            PublicSeo::breadcrumbSchema($breadcrumbs),
        ];

        return view('public.services.index', compact('page', 'services', 'seo', 'siteSettings', 'breadcrumbs'));
    }

    public function show(string $slug)
    {
        $service = PublicService::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();

        $relatedProducts = collect();
        if (!empty($service->related_products)) {
            $relatedProducts = PublicProduct::whereIn('slug', $service->related_products)
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->get();
        }

        $seo = PublicSeo::buildSeoData($service, $siteSettings);

        $breadcrumbs = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Services', 'url' => url('/services/')],
            ['name' => $service->name, 'url' => url($service->url_path)],
        ];

        $seo['schema_json'] = [
            PublicSeo::serviceSchema($service, $siteSettings),
            PublicSeo::breadcrumbSchema($breadcrumbs),
        ];

        return view('public.services.show', compact('service', 'relatedProducts', 'seo', 'siteSettings', 'breadcrumbs'));
    }
}
