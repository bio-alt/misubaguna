{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($pages as $page)
    <url>
        <loc>{{ \App\Support\PublicSeo::canonicalUrl($page->canonical_url ?: $page->url_path) }}</loc>
        <lastmod>{{ optional($page->updated_at)->format('Y-m-d') ?: date('Y-m-d') }}</lastmod>
        <changefreq>{{ $page->sitemap_changefreq ?? 'weekly' }}</changefreq>
        <priority>{{ $page->sitemap_priority ?? '0.9' }}</priority>
    </url>
@endforeach

@if(isset($toolkits))
@foreach($toolkits as $tool)
    <url>
        <loc>{{ \App\Support\PublicSeo::canonicalUrl($tool['loc']) }}</loc>
        <lastmod>{{ date('Y-m-d') }}</lastmod>
        <changefreq>{{ $tool['changefreq'] ?? 'monthly' }}</changefreq>
        <priority>{{ $tool['priority'] ?? '0.8' }}</priority>
    </url>
@endforeach
@endif

@foreach($categories as $category)
    <url>
        <loc>{{ \App\Support\PublicSeo::canonicalUrl($category->canonical_url ?: $category->url_path) }}</loc>
        <lastmod>{{ optional($category->updated_at)->format('Y-m-d') ?: date('Y-m-d') }}</lastmod>
        <changefreq>{{ $category->sitemap_changefreq ?? 'weekly' }}</changefreq>
        <priority>{{ $category->sitemap_priority ?? '0.8' }}</priority>
    </url>
@endforeach

@if(isset($services))
@foreach($services as $service)
    <url>
        <loc>{{ \App\Support\PublicSeo::canonicalUrl($service->canonical_url ?: $service->url_path) }}</loc>
        <lastmod>{{ optional($service->updated_at)->format('Y-m-d') ?: date('Y-m-d') }}</lastmod>
        <changefreq>{{ $service->sitemap_changefreq ?? 'monthly' }}</changefreq>
        <priority>{{ $service->sitemap_priority ?? '0.8' }}</priority>
    </url>
@endforeach
@endif

@foreach($products as $product)
    <url>
        <loc>{{ \App\Support\PublicSeo::canonicalUrl($product->canonical_url ?: $product->url_path) }}</loc>
        <lastmod>{{ optional($product->updated_at)->format('Y-m-d') ?: date('Y-m-d') }}</lastmod>
        <changefreq>{{ $product->sitemap_changefreq ?? 'monthly' }}</changefreq>
        <priority>{{ $product->sitemap_priority ?? '0.7' }}</priority>
    </url>
@endforeach
</urlset>
