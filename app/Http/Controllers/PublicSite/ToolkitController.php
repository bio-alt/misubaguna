<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Support\MaterialLibrary;
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
            'meta_title' => 'Free Engineering Toolkit & Sizing Tools',
            'meta_description' => 'Access free engineering calculation tools, flange dimensions (DIN, JIS, ASME), scientific calculator, and industrial material specifications library.',
            'canonical_url' => '/toolkit/',
            'og_title' => 'Free Engineering Toolkit & Sizing Tools',
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
            'meta_title' => 'Flange Dimensions: DIN, JIS & ASME B16.5',
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
            'meta_title' => 'Online Scientific & Engineering Calculator',
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
        $materials = MaterialLibrary::all();
        $families = MaterialLibrary::FAMILIES;
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();

        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Engineering Toolkit', 'url' => '/toolkit/'],
            ['name' => 'Material Specs Library', 'url' => '/toolkit/material-specs/'],
        ];

        $seo = [
            'meta_title' => 'Engineering Material Property Library',
            'meta_description' => 'Mechanical, thermal and chemical properties of '.count($materials).' industrial materials: carbon and stainless steels, nickel alloys, elastomers, PTFE, ceramics and gaskets.',
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
                    'Searchable property database for '.count($materials).' industrial materials including metals, polymers, elastomers, and ceramics.',
                    '/toolkit/material-specs/'
                ),
            ],
        ];

        return view('public.toolkit.material-specs', compact('seo', 'siteSettings', 'breadcrumbs', 'materials', 'families'));
    }

    public function expansionJointSelector()
    {
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();

        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Engineering Toolkit', 'url' => '/toolkit/'],
            ['name' => 'Expansion Joint Selector', 'url' => '/toolkit/expansion-joint-selector/'],
        ];

        $seo = [
            'meta_title' => 'Thermal Expansion Calculator & Joint Selector',
            'meta_description' => 'Calculate pipe thermal expansion and anchor pressure thrust, then get a rubber, PTFE, metal or fabric expansion joint recommendation for your conditions.',
            'canonical_url' => '/toolkit/expansion-joint-selector/',
            'og_title' => 'Pipe Thermal Expansion Calculator & Expansion Joint Selector | Misuba Guna',
            'og_description' => 'Free pipe thermal expansion calculator with expansion joint type selection by temperature, pressure, and media.',
            'robots_index' => true,
            'robots_follow' => true,
            'schema_json' => [
                PublicSeo::organizationSchema($siteSettings),
                PublicSeo::breadcrumbSchema($breadcrumbs),
                PublicSeo::webApplicationSchema(
                    'Pipe Thermal Expansion Calculator & Expansion Joint Selector',
                    'Calculates axial pipe growth between anchors and recommends an expansion joint type by temperature, pressure, and media.',
                    '/toolkit/expansion-joint-selector/'
                ),
            ],
        ];

        return view('public.toolkit.expansion-joint-selector', compact('seo', 'siteSettings', 'breadcrumbs'));
    }

    public function chemicalResistance()
    {
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();

        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Engineering Toolkit', 'url' => '/toolkit/'],
            ['name' => 'Chemical Resistance Guide', 'url' => '/toolkit/chemical-resistance/'],
        ];

        $seo = [
            'meta_title' => 'Chemical Resistance Chart: PTFE, Rubber, 316SS',
            'meta_description' => 'Chemical compatibility chart rating PTFE, EPDM, NBR, Viton (FKM), Neoprene, Hypalon, natural rubber, 316 stainless, ceramic and FRP against 40+ chemicals.',
            'canonical_url' => '/toolkit/chemical-resistance/',
            'og_title' => 'Chemical Resistance Chart for Linings, Elastomers & Expansion Joints | Misuba Guna',
            'og_description' => 'Check chemical compatibility of PTFE, rubber, stainless steel, ceramic, and FRP before choosing linings, hoses, gaskets, or expansion joints.',
            'robots_index' => true,
            'robots_follow' => true,
            'schema_json' => [
                PublicSeo::organizationSchema($siteSettings),
                PublicSeo::breadcrumbSchema($breadcrumbs),
                PublicSeo::webApplicationSchema(
                    'Chemical Resistance Guide',
                    'Chemical compatibility ratings for lining, elastomer, metal, ceramic, and FRP materials against common industrial chemicals.',
                    '/toolkit/chemical-resistance/'
                ),
            ],
        ];

        return view('public.toolkit.chemical-resistance', compact('seo', 'siteSettings', 'breadcrumbs'));
    }
}
