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
            'title' => 'Industrial Engineering & Technical Services | PT Misuba Guna Indonesia',
            'headline' => 'Industrial Field Services & Technical Repair',
            'summary' => 'PT Misuba Guna Indonesia provides comprehensive industrial plant maintenance services including cooling tower repair, heat exchanger retubing, thermal spray coating, protective lining, expansion joint replacement, and rotating equipment overhaul.',
            'url_path' => '/services/',
            'canonical_url' => PublicSeo::canonicalUrl('/services/'),
            'robots_index' => true,
            'robots_follow' => true,
        ];

        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();
        $services = PublicService::where('is_published', true)->orderBy('sort_order')->get();
        $seo = PublicSeo::buildSeoData($page, $siteSettings);

        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Industrial Services', 'url' => '/services/'],
        ];

        $serviceItems = [];
        foreach ($services as $idx => $s) {
            $serviceItems[] = [
                '@type' => 'ListItem',
                'position' => $idx + 1,
                'name' => $s->name,
                'url' => PublicSeo::canonicalUrl('/services/' . $s->slug . '/'),
            ];
        }

        $collectionSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Industrial Engineering & Field Maintenance Services | PT Misuba Guna Indonesia',
            'description' => 'Comprehensive industrial plant maintenance and field repair services across Indonesia.',
            'url' => PublicSeo::canonicalUrl('/services/'),
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => count($serviceItems),
                'itemListElement' => $serviceItems,
            ],
        ];

        $seo['schema_json'] = [
            PublicSeo::organizationSchema($siteSettings),
            PublicSeo::breadcrumbSchema($breadcrumbs),
            $collectionSchema,
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
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Services', 'url' => '/services/'],
            ['name' => $service->name, 'url' => $service->url_path ?: ('/services/' . $service->slug . '/')],
        ];

        $seo['schema_json'] = [
            PublicSeo::serviceSchema($service, $siteSettings),
            PublicSeo::breadcrumbSchema($breadcrumbs),
        ];

        return view('public.services.show', compact('service', 'relatedProducts', 'seo', 'siteSettings', 'breadcrumbs'));
    }
}
