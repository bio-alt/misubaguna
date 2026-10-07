<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Support\PublicSeo;
use Illuminate\Support\Facades\DB;

class ToolkitController extends Controller
{
    public function index()
    {
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();

        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Engineering Toolkit', 'url' => '/toolkit/'],
        ];

        $seo = [
            'meta_title' => 'Engineering Toolkit & Sizing Tools | PT Misuba Guna Indonesia',
            'meta_description' => 'Access free engineering calculation tools, flange dimensions (DIN, JIS, ASME), scientific calculator, and industrial material specifications library.',
            'canonical_url' => '/toolkit/',
            'og_title' => 'Engineering Toolkit & Sizing Tools | PT Misuba Guna Indonesia',
            'og_description' => 'Interactive industrial engineering tools, flange standards, and material properties library.',
            'robots_index' => true,
            'robots_follow' => true,
            'schema_json' => [
                PublicSeo::organizationSchema($siteSettings),
                PublicSeo::breadcrumbSchema($breadcrumbs),
                PublicSeo::webApplicationSchema(
                    'PT Misuba Guna Indonesia Engineering Toolkit',
                    'Interactive engineering tools suite including flange standards, scientific calculator, and material properties library.',
                    '/toolkit/'
                ),
            ],
        ];

        return view('public.toolkit.index', compact('seo', 'siteSettings', 'breadcrumbs'));
    }

    public function flangeStandards()
    {
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();

        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Engineering Toolkit', 'url' => '/toolkit/'],
            ['name' => 'Flange Standards', 'url' => '/toolkit/flange-standards/'],
        ];

        $seo = [
            'meta_title' => 'Flange Dimensions & Standards (DIN, JIS, ASME B16.5) | PT Misuba Guna Indonesia',
            'meta_description' => 'Comprehensive industrial flange dimension reference tables covering DIN, JIS (5K, 10K, 16K, 20K), and ASME B16.5 (Class 150 to 2500) bolt holes and pipe sizing.',
            'canonical_url' => '/toolkit/flange-standards/',
            'og_title' => 'Flange Standards & Dimensions Reference (DIN, JIS, ASME) | Misuba Guna',
            'og_description' => 'Interactive dimension tables for DIN, JIS, and ASME B16.5 flange pressure classes and pipe standards.',
            'robots_index' => true,
            'robots_follow' => true,
            'schema_json' => [
                PublicSeo::organizationSchema($siteSettings),
                PublicSeo::breadcrumbSchema($breadcrumbs),
                PublicSeo::webApplicationSchema(
                    'Flange Standards & Dimensions Reference Table',
                    'Engineering dimensional reference for DIN, JIS, and ASME B16.5 industrial pipe flanges.',
                    '/toolkit/flange-standards/'
                ),
            ],
        ];

        return view('public.toolkit.flange-standards', compact('seo', 'siteSettings', 'breadcrumbs'));
    }

    public function calculator()
    {
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();

        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Engineering Toolkit', 'url' => '/toolkit/'],
            ['name' => 'Scientific Calculator', 'url' => '/toolkit/calculator/'],
        ];

        $seo = [
            'meta_title' => 'Scientific & Engineering Calculator | PT Misuba Guna Indonesia',
            'meta_description' => 'Advanced online engineering and scientific calculator with trigonometric, logarithmic, memory functions, history log, and unit conversion tools.',
            'canonical_url' => '/toolkit/calculator/',
            'og_title' => 'Online Scientific & Engineering Calculator | Misuba Guna',
            'og_description' => 'Precision online scientific and engineering calculator for piping, plant maintenance, and thermal calculations.',
            'robots_index' => true,
            'robots_follow' => true,
            'schema_json' => [
                PublicSeo::organizationSchema($siteSettings),
                PublicSeo::breadcrumbSchema($breadcrumbs),
                PublicSeo::webApplicationSchema(
                    'Misuba Engineering Scientific Calculator',
                    'Advanced web-based scientific calculator with engineering functions and unit conversions.',
                    '/toolkit/calculator/'
                ),
            ],
        ];

        return view('public.toolkit.calculator', compact('seo', 'siteSettings', 'breadcrumbs'));
    }

    public function materialSpecs()
    {
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();

        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Engineering Toolkit', 'url' => '/toolkit/'],
            ['name' => 'Material Specs Library', 'url' => '/toolkit/material-specs/'],
        ];

        $seo = [
            'meta_title' => 'Material Property Library & Engineering Database | PT Misuba Guna Indonesia',
            'meta_description' => 'Searchable engineering material specification database for 45+ industrial alloys, polymers, elastomers, and composites with mechanical and thermal properties.',
            'canonical_url' => '/toolkit/material-specs/',
            'og_title' => 'Industrial Material Properties & Specifications Database | Misuba Guna',
            'og_description' => 'Searchable material property database for industrial piping, gaskets, linings, and expansion joints.',
            'robots_index' => true,
            'robots_follow' => true,
            'schema_json' => [
                PublicSeo::organizationSchema($siteSettings),
                PublicSeo::breadcrumbSchema($breadcrumbs),
                PublicSeo::webApplicationSchema(
                    'Industrial Material Properties Database',
                    'Interactive engineering database for 45+ industrial materials including metals, polymers, and elastomers.',
                    '/toolkit/material-specs/'
                ),
            ],
        ];

        return view('public.toolkit.material-specs', compact('seo', 'siteSettings', 'breadcrumbs'));
    }
}
