<?php

namespace App\Support;

class PublicSeo
{
    public static function organizationSchema(array $settings = []): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $settings['company_name'] ?? 'PT Misuba Guna Indonesia',
            'url' => $settings['company_website'] ?? 'https://misubaguna.com',
            'email' => $settings['company_email'] ?? 'marketing@misubaguna.com',
            'telephone' => $settings['company_phone'] ?? '+62 21 55660700',
        ];
    }

    public static function productSchema(object|array $product): array
    {
        $get = fn ($key, $default = null) => is_array($product) ? ($product[$key] ?? $default) : ($product->{$key} ?? $default);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $get('name'),
            'description' => $get('summary') ?: $get('meta_description'),
            'url' => $get('canonical_url'),
            'brand' => [
                '@type' => 'Brand',
                'name' => 'PT Misuba Guna Indonesia',
            ],
        ];
    }

    public static function serviceSchema(object|array $service, array $settings = []): array
    {
        $get = fn ($key, $default = null) => is_array($service) ? ($service[$key] ?? $default) : ($service->{$key} ?? $default);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $get('name'),
            'description' => $get('summary') ?: $get('meta_description'),
            'url' => $get('canonical_url') ?: url($get('url_path', '/services/')),
            'provider' => [
                '@type' => 'Organization',
                'name' => $settings['company_name'] ?? 'PT Misuba Guna Indonesia',
                'url' => $settings['company_website'] ?? 'https://misubaguna.com',
            ],
            'serviceType' => $get('service_group') ?: 'Industrial Engineering & Field Services',
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
                'item' => $item['url'],
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

        return [
            'meta_title' => $get('meta_title') ?: $get('title') ?: $get('name'),
            'meta_description' => $get('meta_description') ?: $get('excerpt') ?: $get('summary'),
            'canonical_url' => $get('canonical_url') ?? url($get('url_path', '/')),
            'og_title' => $get('og_title') ?: $get('meta_title') ?: $get('title') ?: $get('name'),
            'og_description' => $get('og_description') ?: $get('meta_description') ?: $get('excerpt') ?: $get('summary'),
            'og_image_path' => $get('og_image_path'),
            'robots_index' => (bool) ($get('robots_index') ?? true),
            'robots_follow' => (bool) ($get('robots_follow') ?? true),
            'schema_json' => $get('schema_json') ?: self::organizationSchema($siteSettings),
        ];
    }
}
