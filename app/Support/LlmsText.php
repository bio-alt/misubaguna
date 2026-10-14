<?php

namespace App\Support;

use App\Models\PublicSite\PublicProduct;
use App\Models\PublicSite\PublicProductCategory;
use App\Models\PublicSite\PublicService;

/**
 * Builds /llms.txt (short index) and /llms-full.txt (full catalog) from the published
 * catalog, so the files AI assistants read never drift from the live pages.
 *
 * Regenerate with `php artisan seo:llms` after catalog content changes.
 */
class LlmsText
{
    private const TOOLKIT = [
        ['Engineering Toolkit Hub', '/toolkit/', 'Free engineering utilities and technical reference tables.'],
        ['Flange Dimensions & Standards', '/toolkit/flange-standards/', 'DIN, JIS (5K, 10K, 16K, 20K) and ASME B16.5 (Class 150-2500) pipe flange dimensions and bolt patterns.'],
        ['Scientific & Engineering Calculator', '/toolkit/calculator/', 'Online engineering calculator with unit conversion and mathematical functions.'],
        ['Material Property Library', '/toolkit/material-specs/', 'Searchable property database of industrial materials: steels, nickel alloys, elastomers, fluoropolymers, ceramics and gasket sheets.'],
        ['Expansion Joint Selector', '/toolkit/expansion-joint-selector/', 'Pipe thermal expansion calculator (axial growth, anchor thrust) with rubber, PTFE, metal and fabric expansion joint recommendation.'],
        ['Chemical Resistance Guide', '/toolkit/chemical-resistance/', 'Chemical compatibility chart for PTFE, EPDM, NBR, FKM (Viton), Neoprene, Hypalon, natural rubber, 316 stainless steel, ceramic and FRP against 40+ industrial chemicals.'],
        ['EJMA Bellows Calculator', '/toolkit/ejma-bellows-calculator/', 'Online design check of metal bellows expansion joints (unreinforced, reinforced, toroidal) per EJMA Standards; results are reviewed by our engineering team.'],
    ];

    private const PAGES = [
        ['About Us', '/about-us/', 'Company history, mission, vision and capabilities.'],
        ['Product Catalog', '/catalog/', 'Company profile and downloadable product catalog (PDF).'],
        ['Project References', '/project-list/', 'Completed client projects in power plants, mills and refineries.'],
        ['Careers', '/career/', 'Current job openings at PT Misuba Guna Indonesia.'],
        ['Contact Us', '/contact-us/', 'Inquiry form, sales contacts, WhatsApp and office locations.'],
        ['XML Sitemap', '/sitemap.xml', 'Machine-readable list of every public page.'],
    ];

    public static function short(): string
    {
        $out = self::header();

        $out .= "## Product Categories\n";
        foreach (PublicProductCategory::publishedWithProducts() as $category) {
            $out .= self::link($category->name, $category->url_path ?: '/product-category/' . $category->slug . '/', $category->meta_description ?: $category->summary);
        }

        $out .= "\n## Products\n";
        foreach (self::products() as $product) {
            $out .= self::link($product->name, self::productPath($product), $product->summary);
        }

        $out .= "\n## Plant Maintenance & Engineering Services\n";
        foreach (self::services() as $service) {
            $out .= self::link($service->name, $service->url_path ?: '/services/' . $service->slug . '/', $service->summary);
        }

        $out .= self::indonesianSection();
        $out .= self::staticSections();
        $out .= self::link('Full LLM Knowledge File', '/llms-full.txt', 'Every product with specifications, materials and applications, plus full service scopes.');

        return $out;
    }

    public static function full(): string
    {
        $out = self::header();
        $out .= self::staticSections();
        $out .= "\n---\n\n# Detailed Technical Catalog & Service Directory\n\n## All Industrial Products\n";

        foreach (self::products() as $product) {
            $out .= "\n### {$product->name}\n";
            $out .= '- **URL:** ' . PublicSeo::canonicalUrl(self::productPath($product)) . "\n";
            $category = trim(implode(' > ', array_filter([$product->product_group, $product->product_subgroup])));
            if ($category !== '') {
                $out .= "- **Category:** {$category}\n";
            }
            if ($product->summary) {
                $out .= '- **Summary:** ' . self::clean($product->summary) . "\n";
            }
            $out .= self::listLine('Key Features', $product->key_features);
            $out .= self::listLine('Specifications', $product->technical_specs);
            $out .= self::listLine('Materials', $product->materials);
            $out .= self::listLine('Applications', $product->applications);

            if (CouplingCatalog::isHub($product->slug)) {
                foreach (CouplingCatalog::families() as $family) {
                    $out .= '- **Family:** [' . $family['name'] . '](' . PublicSeo::canonicalUrl($family['url']) . '): ' . self::clean($family['tile']) . "\n";
                }
            } elseif ($family = CouplingCatalog::family($product->slug)) {
                foreach ($family['series'] as $series) {
                    $facts = $series['facts'] ? ' (' . implode('; ', $series['facts']) . ')' : '';
                    $out .= "- **Series {$series['code']}:** {$series['name']}. " . self::clean($series['line']) . $facts . "\n";
                }
            }

            $out .= '- **How to buy:** Request a quotation from sales@misubaguna.com or WhatsApp +62 811-8715-671 with operating conditions and quantity.' . "\n";
        }

        $out .= "\n## All Services\n";
        foreach (self::services() as $service) {
            $out .= "\n### {$service->name}\n";
            $out .= '- **URL:** ' . PublicSeo::canonicalUrl($service->url_path ?: '/services/' . $service->slug . '/') . "\n";
            if ($service->service_group) {
                $out .= "- **Group:** {$service->service_group}\n";
            }
            if ($service->summary) {
                $out .= '- **Summary:** ' . self::clean($service->summary) . "\n";
            }
            $out .= self::listLine('Capabilities', $service->key_features);
            $out .= self::listLine('Scope of Work', $service->scope_of_work);
            $out .= self::listLine('Problems Solved', $service->typical_problems);
            $out .= self::listLine('Industries Served', $service->industries_served);
            $out .= self::listLine('Materials & Equipment', $service->materials_equipment);
        }

        return $out;
    }

    private static function header(): string
    {
        return <<<'TXT'
# PT Misuba Guna Indonesia

> PT Misuba Guna Indonesia is an Indonesian industrial engineering supplier and plant maintenance contractor. It supplies expansion joints, sealing systems, corrosion protection linings and coatings, couplings, thermal equipment and pulp & paper machinery, and provides on-site repair, lining and maintenance services to industrial plants across Indonesia.

Prices, stock and lead times are quoted per enquiry; none are published on the website.

## Company Overview
- **Legal Entity:** PT Misuba Guna Indonesia
- **Website:** https://misubaguna.com/
- **Sales Email:** sales@misubaguna.com
- **Phone:** +62 21 55660700
- **WhatsApp:** +62 811-8715-671
- **Operational & Warehouse:** Ruko Mutiara Karawaci Blok C29, Bencongan Indah, Kelapa Dua, Tangerang, Banten 15810, Indonesia
- **Billing Office:** Gedung STC Senayan Lt. 4 Ruang 80, Jalan Asia Afrika Pintu IX Gelora Senayan, Gelora, Tanah Abang, Kota Adm. Jakarta Pusat, DKI Jakarta 10270, Indonesia
- **Industries Served:** Power generation, petrochemical, chemical processing, pulp & paper, palm oil, mining, water treatment, food & beverage, heavy manufacturing.
- **Languages:** English, Indonesian


TXT;
    }

    private static function indonesianSection(): string
    {
        $pages = IndonesianPages::products();
        if ($pages->isEmpty()) {
            return '';
        }

        $out = "
## Halaman Bahasa Indonesia (Indonesian-language pages)
";
        $out .= self::link('Produk Industri', IndonesianPages::PREFIX . '/', 'Daftar produk dalam Bahasa Indonesia.');
        foreach ($pages as $product) {
            $c = IndonesianPages::content($product->slug);
            $out .= self::link($c['name'], IndonesianPages::url($product->slug), $c['summary'] ?? null);
        }

        return $out;
    }

    private static function staticSections(): string
    {
        $out = "\n## Engineering Toolkit (free tools)\n";
        foreach (self::TOOLKIT as [$name, $path, $text]) {
            $out .= self::link($name, $path, $text);
        }
        $out .= "\n## Key Pages\n";
        foreach (self::PAGES as [$name, $path, $text]) {
            $out .= self::link($name, $path, $text);
        }

        return $out;
    }

    private static function products()
    {
        return PublicProduct::where('is_published', true)->orderBy('product_group')->orderBy('sort_order')->orderBy('name')->get();
    }

    private static function services()
    {
        return PublicService::where('is_published', true)->orderBy('sort_order')->get();
    }

    private static function productPath(PublicProduct $product): string
    {
        return $product->url_path ?: '/product/' . $product->slug . '/';
    }

    private static function link(string $name, string $path, ?string $text): string
    {
        $text = self::clean((string) $text);

        return '- [' . $name . '](' . PublicSeo::canonicalUrl($path) . ')' . ($text !== '' ? ': ' . $text : '') . "\n";
    }

    private static function listLine(string $label, $items): string
    {
        $items = array_values(array_filter(array_map(fn ($i) => self::clean((string) $i), (array) $items)));

        return $items ? "- **{$label}:** " . implode('; ', $items) . "\n" : '';
    }

    private static function clean(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', strip_tags($text)));
    }
}
