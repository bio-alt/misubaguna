@php
    $yieldTitle = trim($__env->yieldContent('title'));
    $yieldDescription = trim($__env->yieldContent('meta_description'));

    $seoTitle = $seo['meta_title'] ?? $seo->meta_title ?? ($yieldTitle ?: ($seo['title'] ?? $seo->title ?? config('app.name')));
    $seoTitle = \App\Support\PublicSeo::title($seoTitle);

    $seoDescription = $seo['meta_description'] ?? $seo->meta_description ?? ($yieldDescription ?: ($seo['excerpt'] ?? $seo->excerpt ?? $seo['summary'] ?? $seo->summary ?? \App\Support\PublicSeo::DEFAULT_DESC));
    $seoDescription = \App\Support\PublicSeo::description($seoDescription);

    $rawCanonical = $seo['canonical_url'] ?? $seo->canonical_url ?? request()->path();
    $canonicalUrl = \App\Support\PublicSeo::canonicalUrl($rawCanonical);

    $ogTitle = $seo['og_title'] ?? $seo->og_title ?? $seoTitle;
    $ogDescription = $seo['og_description'] ?? $seo->og_description ?? $seoDescription;
    $ogImageRaw = ($seo['og_image_path'] ?? $seo->og_image_path ?? null) ?: \App\Support\PublicSeo::DEFAULT_OG_IMAGE;
    // The wide logo crops badly in link previews; use the 1200x630 share image instead.
    if (str_contains($ogImageRaw, 'misuba-logo')) {
        $ogImageRaw = \App\Support\PublicSeo::DEFAULT_OG_IMAGE;
    }
    $ogImagePath = \App\Support\PublicSeo::assetUrl($ogImageRaw);
    $ogImageSize = str_starts_with($ogImageRaw, 'http') ? null : @getimagesize(public_path(rawurldecode(ltrim($ogImageRaw, '/'))));
    if ($ogImageRaw === \App\Support\PublicSeo::DEFAULT_OG_IMAGE) {
        $ogImageSize = [1200, 630];
    }
    $ogType = $seo['og_type'] ?? 'website';

    $robotsIndex = (bool)($seo['robots_index'] ?? $seo->robots_index ?? true);
    $robotsFollow = (bool)($seo['robots_follow'] ?? $seo->robots_follow ?? true);
    $schemaJson = $seo['schema_json'] ?? $seo->schema_json ?? null;
@endphp

<title>{{ $seoTitle }}</title>
@if($seoDescription)
<meta name="description" content="{{ $seoDescription }}">
@endif
<link rel="canonical" href="{{ $canonicalUrl }}">
<meta name="robots" content="{{ $robotsIndex ? 'index' : 'noindex' }}, {{ $robotsFollow ? 'follow' : 'nofollow' }}, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

<!-- Open Graph / Facebook / LinkedIn / WhatsApp -->
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:site_name" content="PT Misuba Guna Indonesia">
<meta property="og:title" content="{{ $ogTitle }}">
@if($ogDescription)
<meta property="og:description" content="{{ $ogDescription }}">
@endif
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:image" content="{{ $ogImagePath }}">
@if($ogImageSize)
<meta property="og:image:width" content="{{ $ogImageSize[0] }}">
<meta property="og:image:height" content="{{ $ogImageSize[1] }}">
@endif
<meta property="og:image:alt" content="{{ $ogTitle }}">
<meta property="og:locale" content="en_US">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $ogTitle }}">
@if($ogDescription)
<meta name="twitter:description" content="{{ $ogDescription }}">
@endif
<meta name="twitter:image" content="{{ $ogImagePath }}">

<!-- AI / LLM Discovery Standard Link -->
<link rel="alternate" type="text/plain" href="https://misubaguna.com/llms.txt" title="LLM summary of PT Misuba Guna Indonesia">
<link rel="alternate" type="application/xml" href="https://misubaguna.com/sitemap.xml" title="Sitemap">

@if($schemaJson)
<script type="application/ld+json">
{!! is_array($schemaJson) ? json_encode($schemaJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $schemaJson !!}
</script>
@endif
