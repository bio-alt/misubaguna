<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\PublicSite\PublicPage;
use App\Models\PublicSite\PublicProductCategory;
use App\Support\PublicSeo;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    public function index()
    {
        $page = PublicPage::where('url_path', '/catalog/')->where('is_published', true)->firstOrFail();
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();
        $seo = PublicSeo::buildSeoData($page, $siteSettings);

        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Product Catalog', 'url' => '/catalog/'],
        ];

        $categories = PublicProductCategory::publishedWithProducts();
        $catItems = [];
        foreach ($categories as $i => $cat) {
            $catItems[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $cat->name,
                'url' => PublicSeo::canonicalUrl('/product-category/' . $cat->slug . '/'),
            ];
        }

        $seo['schema_json'] = [
            PublicSeo::organizationSchema($siteSettings),
            PublicSeo::breadcrumbSchema($breadcrumbs),
            [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => 'Industrial Product Catalog | PT Misuba Guna Indonesia',
                'description' => 'Comprehensive catalog of industrial engineering products: expansion joints, sealing systems, linings, hoses, and related equipment.',
                'url' => PublicSeo::canonicalUrl('/catalog/'),
                'mainEntity' => [
                    '@type' => 'ItemList',
                    'numberOfItems' => count($catItems),
                    'itemListElement' => $catItems,
                ],
            ],
        ];

        return view('public.pages.catalog', compact('page', 'seo', 'siteSettings', 'breadcrumbs'));
    }
}