<?php

namespace App\Support;

class PublicSeo
{
    public const DEFAULT_DOMAIN = 'https://misubaguna.com';
    public const DEFAULT_LOGO = 'https://misubaguna.com/images/misuba-logo.png';
    public const DEFAULT_DESC = 'PT Misuba Guna Indonesia provides high-performance industrial engineering products and plant maintenance services, specializing in expansion joints, sealing systems, corrosion protection linings, and equipment overhaul.';

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

    public static function defaultOgImage(): string
    {
        return self::DEFAULT_LOGO;
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
                'width' => '320',
                'height' => '80',
            ],
            'image' => self::DEFAULT_LOGO,
            'description' => self::DEFAULT_DESC,
            'email' => $settings['company_email'] ?? 'sales@misubaguna.com',
            'telephone' => $settings['company_phone'] ?? '+62 21 55660700',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Ruko Mutiara Karawaci Blok C29, Bencongan Indah, Kecamatan Kelapa Dua',
                'addressLocality' => 'Tangerang',
                'addressRegion' => 'Banten',
                'postalCode' => '15810',
                'addressCountry' => 'ID',
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
                'Industrial Filtration Systems',
                'Pulp and Paper Mill Equipment',
                'Palm Oil Mill Machinery',
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
        
        $images = [];
        $gallery = $get('gallery');
        if (is_array($gallery) && !empty($gallery)) {
            foreach ($gallery as $img) {
                $images[] = str_starts_with($img, 'http') ? $img : url($img);
            }
        } elseif ($get('og_image_path')) {
            $images[] = str_starts_with($get('og_image_path'), 'http') ? $get('og_image_path') : url($get('og_image_path'));
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $get('name'),
            'description' => $get('summary') ?: $get('meta_description') ?: ('High-performance ' . $get('name') . ' engineered by PT Misuba Guna Indonesia.'),
            'url' => $productUrl,
            'brand' => [
                '@type' => 'Brand',
                'name' => 'PT Misuba Guna Indonesia',
            ],
            'manufacturer' => [
                '@type' => 'Organization',
                'name' => 'PT Misuba Guna Indonesia',
                'url' => self::DEFAULT_DOMAIN . '/',
            ],
            'category' => $get('product_group') ?: 'Industrial Equipment & Sealing',
            'offers' => [
                '@type' => 'Offer',
                'url' => $productUrl,
                'priceCurrency' => 'IDR',
                'price' => '0',
                'priceSpecification' => [
                    '@type' => 'PriceSpecification',
                    'priceCurrency' => 'IDR',
                    'valueAddedTaxIncluded' => true,
                ],
                'availability' => 'https://schema.org/InStock',
                'itemCondition' => 'https://schema.org/NewCondition',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => 'PT Misuba Guna Indonesia',
                ],
            ],
        ];

        if (!empty($images)) {
            $schema['image'] = count($images) === 1 ? $images[0] : $images;
        }

        return $schema;
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

        $rawTitle = $get('meta_title') ?: $get('title') ?: $get('name') ?: config('app.name');
        // Ensure brand name is cleanly formatted
        if (!str_contains($rawTitle, 'Misuba')) {
            $seoTitle = $rawTitle . ' | PT Misuba Guna Indonesia';
        } else {
            $seoTitle = $rawTitle;
        }

        $seoDescription = $get('meta_description') ?: $get('excerpt') ?: $get('summary') ?: self::DEFAULT_DESC;
        // Truncate to clean length if too long (max 160 characters for optimal SERP display)
        if (mb_strlen($seoDescription) > 165) {
            $seoDescription = mb_substr($seoDescription, 0, 157) . '...';
        }

        $ogTitle = $get('og_title') ?: $seoTitle;
        $ogDescription = $get('og_description') ?: $seoDescription;
        $ogImage = $get('og_image_path') ?: self::DEFAULT_LOGO;

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
