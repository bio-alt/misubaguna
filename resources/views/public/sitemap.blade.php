{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($pages as $page)
    <url>
        <loc>{{ $page->canonical_url ?: url($page->url_path) }}</loc>
        <changefreq>{{ $page->sitemap_changefreq ?? 'monthly' }}</changefreq>
        <priority>{{ $page->sitemap_priority ?? '0.5' }}</priority>
    </url>
@endforeach
@foreach($products as $product)
    <url>
        <loc>{{ $product->canonical_url ?: url($product->url_path) }}</loc>
        <changefreq>{{ $product->sitemap_changefreq ?? 'monthly' }}</changefreq>
        <priority>{{ $product->sitemap_priority ?? '0.5' }}</priority>
    </url>
@endforeach
@foreach($categories as $category)
    <url>
        <loc>{{ $category->canonical_url ?: url($category->url_path) }}</loc>
        <changefreq>{{ $category->sitemap_changefreq ?? 'monthly' }}</changefreq>
        <priority>{{ $category->sitemap_priority ?? '0.5' }}</priority>
    </url>
@endforeach
</urlset>
