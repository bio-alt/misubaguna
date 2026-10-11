<?php

namespace App\Support;

class PublicSeo
{
    public const DEFAULT_DOMAIN = 'https://misubaguna.com';
    public const DEFAULT_LOGO = 'https://misubaguna.com/images/misuba-logo.png';
    public const DEFAULT_OG_IMAGE = 'https://misubaguna.com/images/og-default.jpg';
    public const BRAND = 'PT Misuba Guna Indonesia';
    public const BRAND_SHORT = 'Misuba Guna';
    public const TITLE_MAX = 60;
    public const DESC_MAX = 160;
    public const DEFAULT_DESC = 'PT Misuba Guna Indonesia provides high-performance industrial engineering products and plant maintenance services, specializing in expansion joints, sealing systems, corrosion protection linings, and equipment overhaul.';

    public const WAREHOUSE_ADDRESS = [
        '@type' => 'PostalAddress',
        'streetAddress' => 'Ruko Mutiara Karawaci Blok C29, Bencongan Indah, Kecamatan Kelapa Dua',
        'addressLocality' => 'Tangerang',
        'addressRegion' => 'Banten',
        'postalCode' => '15810',
        'addressCountry' => 'ID',
    ];

    public const BILLING_ADDRESS = [
        '@type' => 'PostalAddress',
        'streetAddress' => 'Gedung STC Senayan Lantai 4 No 89, Jalan Asia Afrika Pintu IX, Gelora Senayan, Tanah Abang',
        'addressLocality' => 'Jakarta Pusat',
        'addressRegion' => 'DKI Jakarta',
        'addressCountry' => 'ID',
    ];

    public static function canonicalUrl(?string $urlOrPath = '/'): string
    {
        if (empty($urlOrPath)) {
            $urlOrPath = '/';
        }

        // If already an absolute URL on misubaguna.com or localhost
        if (str_starts_with($urlOrPath, 'http://') || str_starts_with($urlOrPath, 'https://')) {
            $parsed = parse_url($urlOrPath);
            $path = $parsed['path'] ?? '/';
            $urlOrPath = $path;
        }

        $trimmed = '/' . ltrim($urlOrPath, '/');
        // Ensure trailing slash for directory routes, avoid for filenames like sitemap.xml, robots.txt, llms.txt
        if (!preg_match('/\.[a-z0-9]+$/i', $trimmed) && !str_ends_with($trimmed, '/')) {
            $trimmed .= '/';
        }

        return self::DEFAULT_DOMAIN . $trimmed;
    }

    /** Site-relative path with a trailing slash, so internal links skip the slash redirect. */
    public static function path(?string $urlOrPath): string
    {
        $path = parse_url((string) $urlOrPath, PHP_URL_PATH) ?: '/';
        $path = '/' . ltrim($path, '/');

        if (! preg_match('/\.[a-z0-9]+$/i', $path) && ! str_ends_with($path, '/')) {
            $path .= '/';
        }

        return $path;
    }

    /** Absolute URL on the canonical domain for a public file such as an image. */
    public static function assetUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $segments = array_map('rawurlencode', explode('/', ltrim($path, '/')));

        return self::DEFAULT_DOMAIN . '/' . implode('/', $segments);
    }

    /**
     * width/height attributes for a local image so the browser reserves its box (no layout shift).
     * Returns '' when the file is missing or unreadable.
     */
    public static function imgSize(?string $path): string
    {
        static $cache = [];

        if (empty($path) || str_starts_with($path, 'http')) {
            return '';
        }
        if (! array_key_exists($path, $cache)) {
            $file = public_path(rawurldecode(ltrim($path, '/')));
            $size = is_file($file) ? @getimagesize($file) : false;
            $cache[$path] = $size ? 'width="' . $size[0] . '" height="' . $size[1] . '"' : '';
        }

        return $cache[$path];
    }

    /** Page title kept within what search results show, with the brand appended once. */
    public static function title(?string $raw): string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return self::BRAND;
        }

        // A title that leads with the brand (the home page) is already final.
        if (str_starts_with($raw, 'PT Misuba') || str_starts_with($raw, 'Misuba')) {
            return $raw;
        }

        $base = trim(preg_replace('/\s*[|\-–—]\s*(PT\s+)?Misuba(\s+Guna)?(\s+Indonesia)?\s*$/iu', '', $raw));
        $base = $base === '' ? $raw : $base;

        // The brand is already worked into the wording ("Contact ... with PT Misuba Guna Indonesia").
        if (str_contains($base, 'Misuba')) {
            return $raw;
        }

        foreach ([self::BRAND, self::BRAND_SHORT] as $brand) {
            $candidate = $base . ' | ' . $brand;
            if (mb_strlen($candidate) <= self::TITLE_MAX) {
                return $candidate;
            }
        }

        return $base . ' | ' . self::BRAND_SHORT;
    }

    /** Meta description cut on a word boundary so it never ends mid-word in search results. */
    public static function description(?string $raw): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $raw)));
        if ($text === '') {
            return self::DEFAULT_DESC;
        }
        if (mb_strlen($text) <= self::DESC_MAX) {
            return $text;
        }

        $cut = mb_substr($text, 0, self::DESC_MAX - 1);
        $space = mb_strrpos($cut, ' ');
        if ($space !== false && $space > 100) {
            $cut = mb_substr($cut, 0, $space);
        }

        return rtrim($cut, " ,;:-–") . '…';
    }

    /** Editor HTML placed under the page's own h1 must not add a second h1. */
    public static function demoteH1(?string $html): string
    {
        return preg_replace(['/<h1(\s|>)/i', '/<\/h1>/i'], ['<h2$1', '</h2>'], (string) $html);
    }

    public static function defaultOgImage(): string
    {
        return self::DEFAULT_OG_IMAGE;
    }

    public static function organizationSchema(array $settings = []): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => ['Organization', 'Corporation'],
            '@id' => self::DEFAULT_DOMAIN . '/#organization',
            'name' => $settings['company_name'] ?? 'PT Misuba Guna Indonesia',
            'alternateName' => 'Misuba Guna Indonesia',
            'url' => self::DEFAULT_DOMAIN . '/',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => self::DEFAULT_LOGO,
                'width' => 1920,
                'height' => 561,
            ],
            'image' => self::DEFAULT_LOGO,
            'description' => self::DEFAULT_DESC,
            'email' => $settings['company_email'] ?? 'sales@misubaguna.com',
            'telephone' => $settings['company_phone'] ?? '+62 21 55660700',
            'address' => self::WAREHOUSE_ADDRESS,
            'location' => [
                ['@type' => 'Place', 'name' => 'Operational & Warehouse (Tangerang)', 'address' => self::WAREHOUSE_ADDRESS],
                ['@type' => 'Place', 'name' => 'Billing Office (Jakarta)', 'address' => self::BILLING_ADDRESS],
            ],
            'contactPoint' => [
                [
                    '@type' => 'ContactPoint',
                    'telephone' => $settings['company_phone'] ?? '+62 21 55660700',
                    'contactType' => 'sales',
                    'areaServed' => 'ID',
                    'availableLanguage' => ['English', 'Indonesian'],
                ],
                [
                    '@type' => 'ContactPoint',
                    'telephone' => $settings['company_whatsapp'] ?? '+62 811-8715-671',
                    'contactType' => 'technical support',
                    'contactOption' => 'TollFree',
                    'areaServed' => 'ID',
                    'availableLanguage' => ['English', 'Indonesian'],
                ],
            ],
            'areaServed' => ['@type' => 'Country', 'name' => 'Indonesia'],
            'sameAs' => [
                'https://wa.me/628118715671',
            ],
            'knowsAbout' => [
                'Industrial Expansion Joints',
                'Gaskets and Sealing Systems',
                'PTFE and Rubber Lining',
                'Ceramic Abrasion Linings',
                'Cooling Tower Maintenance & Overhaul',
                'Heat Exchanger Tube Cleaning & Retubing',
                'Thermal Spray Coating',
                'Pulp and Paper Mill Equipment',
            ],
        ];
    }

    public static function websiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => self::DEFAULT_DOMAIN . '/#website',
            'url' => self::DEFAULT_DOMAIN . '/',
            'name' => 'PT Misuba Guna Indonesia',
            'description' => 'Official website of PT Misuba Guna Indonesia - Industrial Engineering Solutions, Sealing Systems, Expansion Joints, and Plant Maintenance.',
            'publisher' => [
                '@id' => self::DEFAULT_DOMAIN . '/#organization',
            ],
            'inLanguage' => 'en-US',
        ];
    }

    public static function localBusinessSchema(array $settings = []): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ProfessionalService',
            '@id' => self::DEFAULT_DOMAIN . '/#localbusiness',
            'name' => $settings['company_name'] ?? 'PT Misuba Guna Indonesia',
            'url' => self::DEFAULT_DOMAIN . '/',
            'logo' => self::DEFAULT_LOGO,
            'image' => self::DEFAULT_LOGO,
            'telephone' => $settings['company_phone'] ?? '+62 21 55660700',
            'email' => $settings['company_email'] ?? 'sales@misubaguna.com',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Ruko Mutiara Karawaci Blok C29, Bencongan Indah, Kecamatan Kelapa Dua',
                'addressLocality' => 'Tangerang',
                'addressRegion' => 'Banten',
                'postalCode' => '15810',
                'addressCountry' => 'ID',
            ],
            'priceRange' => '$$$',
            'openingHours' => 'Mo-Fr 08:30-17:30',
        ];
    }

    public static function productSchema(object|array $product): array
    {
        $get = fn ($key, $default = null) => is_array($product) ? ($product[$key] ?? $default) : ($product->{$key} ?? $default);

        $productUrl = self::canonicalUrl($get('canonical_url') ?: $get('url_path', '/product/' . $get('slug') . '/'));

        $paths = array_values(array_filter((array) $get('gallery')));
        if (empty($paths) && $get('og_image_path')) {
            $paths = [$get('og_image_path')];
        }
        $images = array_map(fn ($img) => self::assetUrl($img), $paths);

        // Price and stock are quoted per enquiry, so no Offer is published: a made-up price or
        // availability would be a false statement to search engines and AI answer engines.
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            '@id' => $productUrl . '#product',
            'name' => $get('name'),
            'description' => $get('summary') ?: $get('meta_description') ?: ($get('name') . ' supplied by PT Misuba Guna Indonesia.'),
            'url' => $productUrl,
            'brand' => [
                '@type' => 'Brand',
                'name' => 'Misuba Guna',
            ],
            'category' => trim(implode(' > ', array_filter([$get('product_group'), $get('product_subgroup')]))) ?: 'Industrial Equipment',
            'audience' => [
                '@type' => 'BusinessAudience',
                'audienceType' => 'Industrial plants, EPC contractors and maintenance teams',
            ],
        ];

        if (! empty($images)) {
            $schema['image'] = count($images) === 1 ? $images[0] : $images;
        }

        $materials = array_values(array_filter((array) $get('materials')));
        if (! empty($materials)) {
            $schema['material'] = implode('; ', array_slice($materials, 0, 12));
        }

        $properties = [];
        foreach (ProductContent::specRows((array) $get('technical_specs')) as $row) {
            if ($row['label']) {
                $properties[] = ['@type' => 'PropertyValue', 'name' => $row['label'], 'value' => $row['value']];
            }
        }
        if (! empty($properties)) {
            $schema['additionalProperty'] = $properties;
        }

        return $schema;
    }

    /**
     * FAQ built only from the product's own fields, so every answer is already stated on the page.
     *
     * @return array<int, array{q: string, a: string}>
     */
    public static function productFaq(object $product, array $quoteChecklist = []): array
    {
        $name = $product->name;
        $faq = [];

        $applications = array_values(array_filter((array) $product->applications));
        if (! empty($applications)) {
            $faq[] = ['q' => "Where is {$name} used?", 'a' => implode('; ', array_slice($applications, 0, 8)) . '.'];
        }

        $materials = array_values(array_filter((array) $product->materials));
        if (! empty($materials)) {
            $faq[] = ['q' => "What materials are available for {$name}?", 'a' => implode('; ', array_slice($materials, 0, 8)) . '.'];
        }

        if (! empty($quoteChecklist)) {
            $faq[] = ['q' => "What do you need to quote {$name}?", 'a' => implode('; ', $quoteChecklist) . '.'];
        }

        $faq[] = [
            'q' => "Who supplies {$name} in Indonesia?",
            'a' => "PT Misuba Guna Indonesia supplies {$name} to industrial plants across Indonesia from its base in Tangerang, Banten. Send your operating conditions and quantity to sales@misubaguna.com or WhatsApp +62 811-8715-671 for a quotation.",
        ];

        return $faq;
    }

    /** @param  array<int, array{q: string, a: string}>  $faq */
    public static function faqSchema(array $faq): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($item) => [
                '@type' => 'Question',
                'name' => $item['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
            ], $faq),
        ];
    }

    public static function serviceSchema(object|array $service, array $settings = []): array
    {
        $get = fn ($key, $default = null) => is_array($service) ? ($service[$key] ?? $default) : ($service->{$key} ?? $default);
        $serviceUrl = self::canonicalUrl($get('canonical_url') ?: $get('url_path', '/services/' . $get('slug') . '/'));

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $get('name'),
            'description' => $get('summary') ?: $get('meta_description') ?: ('Professional industrial service: ' . $get('name') . ' by PT Misuba Guna Indonesia.'),
            'url' => $serviceUrl,
            'provider' => [
                '@type' => 'Organization',
                'name' => $settings['company_name'] ?? 'PT Misuba Guna Indonesia',
                'url' => self::DEFAULT_DOMAIN . '/',
                'telephone' => $settings['company_phone'] ?? '+62 21 55660700',
            ],
            'serviceType' => $get('service_group') ?: 'Industrial Engineering & Plant Maintenance',
            'areaServed' => [
                '@type' => 'Country',
                'name' => 'Indonesia',
            ],
            'termsOfService' => self::DEFAULT_DOMAIN . '/contact-us/',
        ];
    }

    public static function jobPostingSchema(array $job, array $settings = []): array
    {
        $responsibilitiesList = !empty($job['responsibilities']) ? '<h3>Key Responsibilities:</h3><ul><li>' . implode('</li><li>', $job['responsibilities']) . '</li></ul>' : '';
        $requirementsList = !empty($job['requirements']) ? '<h3>Requirements:</h3><ul><li>' . implode('</li><li>', $job['requirements']) . '</li></ul>' : '';
        $desc = '<p>' . ($job['summary'] ?? '') . '</p>' . $responsibilitiesList . $requirementsList;

        return [
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            'title' => $job['title'],
            'description' => $desc,
            'datePosted' => '2026-01-01',
            'validThrough' => '2027-12-31',
            'employmentType' => 'FULL_TIME',
            'hiringOrganization' => [
                '@type' => 'Organization',
                'name' => $settings['company_name'] ?? 'PT Misuba Guna Indonesia',
                'sameAs' => self::DEFAULT_DOMAIN . '/',
                'logo' => self::DEFAULT_LOGO,
            ],
            'jobLocation' => [
                '@type' => 'Place',
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => 'Ruko Mutiara Karawaci Blok C29, Bencongan Indah, Kelapa Dua',
                    'addressLocality' => 'Tangerang',
                    'addressRegion' => 'Banten',
                    'addressCountry' => 'ID',
                ],
            ],
            'industry' => 'Industrial Manufacturing & Engineering Services',
        ];
    }

    public static function webApplicationSchema(string $name, string $description, string $url): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => $name,
            'description' => $description,
            'url' => self::canonicalUrl($url),
            'applicationCategory' => 'EngineeringApplication',
            'operatingSystem' => 'All',
            'browserRequirements' => 'Requires JavaScript. Requires HTML5.',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD',
            ],
            'creator' => [
                '@type' => 'Organization',
                'name' => 'PT Misuba Guna Indonesia',
                'url' => self::DEFAULT_DOMAIN . '/',
            ],
        ];
    }

    /**
     * ItemList of products (with url) or product variants (without).
     *
     * @param  array<int, array{name: string, url?: string, description?: string, image?: string}>  $items
     */
    public static function itemListSchema(string $name, array $items): array
    {
        $elements = [];
        foreach (array_values($items) as $i => $item) {
            $thing = array_filter([
                '@type' => 'Product',
                'name' => $item['name'],
                'description' => $item['description'] ?? null,
                'url' => isset($item['url']) ? self::canonicalUrl($item['url']) : null,
                'image' => isset($item['image']) ? self::assetUrl($item['image']) : null,
            ]);
            $elements[] = ['@type' => 'ListItem', 'position' => $i + 1, 'item' => $thing];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $name,
            'numberOfItems' => count($elements),
            'itemListElement' => $elements,
        ];
    }

    public static function breadcrumbSchema(array $items): array
    {
        $elements = [];
        foreach ($items as $index => $item) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => self::canonicalUrl($item['url']),
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }

    public static function buildSeoData(object|array $record, array $siteSettings = []): array
    {
        $get = fn ($key, $default = null) => is_array($record) ? ($record[$key] ?? $default) : ($record->{$key} ?? $default);

        $path = $get('url_path') ?: '/';
        $canonicalUrl = self::canonicalUrl($get('canonical_url') ?: $path);

        $seoTitle = self::title($get('meta_title') ?: $get('title') ?: $get('name') ?: config('app.name'));
        $seoDescription = self::description($get('meta_description') ?: $get('excerpt') ?: $get('summary') ?: self::DEFAULT_DESC);

        $ogTitle = $get('og_title') ?: $seoTitle;
        $ogDescription = $get('og_description') ?: $seoDescription;
        $ogImage = $get('og_image_path') ?: self::DEFAULT_OG_IMAGE;

        return [
            'meta_title' => $seoTitle,
            'meta_description' => $seoDescription,
            'canonical_url' => $canonicalUrl,
            'og_title' => $ogTitle,
            'og_description' => $ogDescription,
            'og_image_path' => $ogImage,
            'robots_index' => (bool) ($get('robots_index') ?? true),
            'robots_follow' => (bool) ($get('robots_follow') ?? true),
            'schema_json' => $get('schema_json') ?: self::organizationSchema($siteSettings),
        ];
    }
}
