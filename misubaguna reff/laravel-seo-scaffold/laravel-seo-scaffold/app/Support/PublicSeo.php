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
}
