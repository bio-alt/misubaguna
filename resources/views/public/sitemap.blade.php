{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
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
        <lastmod>{{ $tool['lastmod'] ?? date('Y-m-d') }}</lastmod>
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
@foreach(array_slice(array_values(array_filter((array) $product->gallery)), 0, 10) as $img)
        <image:image>
            <image:loc>{{ \App\Support\PublicSeo::assetUrl($img) }}</image:loc>
        </image:image>
@endforeach
    </url>
@endforeach
@if(isset($idProducts) && $idProducts->isNotEmpty())
    <url>
        <loc>{{ \App\Support\PublicSeo::canonicalUrl('/id/') }}</loc>
        <lastmod>{{ optional($idProducts->max('updated_at'))->format('Y-m-d') ?: date('Y-m-d') }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
@foreach($idProducts as $product)
    <url>
        <loc>{{ \App\Support\PublicSeo::canonicalUrl(\App\Support\IndonesianPages::url($product->slug)) }}</loc>
        <lastmod>{{ date('Y-m-d', max(optional($product->updated_at)->timestamp ?? 0, @filemtime(resource_path('content/id/products/' . $product->slug . '.php')) ?: 0) ?: time()) }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
@endforeach
@endif
</urlset>
