@php
    $seoTitle = $seo['meta_title'] ?? $seo->meta_title ?? $seo['title'] ?? $seo->title ?? config('app.name');
    $seoDescription = $seo['meta_description'] ?? $seo->meta_description ?? $seo['excerpt'] ?? $seo->excerpt ?? $seo['summary'] ?? $seo->summary ?? null;
    $canonicalUrl = $seo['canonical_url'] ?? $seo->canonical_url ?? url()->current();
    $ogTitle = $seo['og_title'] ?? $seo->og_title ?? $seoTitle;
    $ogDescription = $seo['og_description'] ?? $seo->og_description ?? $seoDescription;
    $ogImagePath = $seo['og_image_path'] ?? $seo->og_image_path ?? null;
    $robotsIndex = (bool)($seo['robots_index'] ?? $seo->robots_index ?? true);
    $robotsFollow = (bool)($seo['robots_follow'] ?? $seo->robots_follow ?? true);
    $schemaJson = $seo['schema_json'] ?? $seo->schema_json ?? null;
@endphp

<title>{{ $seoTitle }}</title>
@if($seoDescription)
    <meta name="description" content="{{ $seoDescription }}">
@endif
<link rel="canonical" href="{{ $canonicalUrl }}">
<meta name="robots" content="{{ $robotsIndex ? 'index' : 'noindex' }},{{ $robotsFollow ? 'follow' : 'nofollow' }}">

<meta property="og:type" content="website">
<meta property="og:title" content="{{ $ogTitle }}">
@if($ogDescription)
    <meta property="og:description" content="{{ $ogDescription }}">
@endif
<meta property="og:url" content="{{ $canonicalUrl }}">
@if($ogImagePath)
    <meta property="og:image" content="{{ str_starts_with($ogImagePath, 'http') ? $ogImagePath : asset($ogImagePath) }}">
@endif

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $ogTitle }}">
@if($ogDescription)
    <meta name="twitter:description" content="{{ $ogDescription }}">
@endif
@if($ogImagePath)
    <meta name="twitter:image" content="{{ str_starts_with($ogImagePath, 'http') ? $ogImagePath : asset($ogImagePath) }}">
@endif

@if($schemaJson)
<script type="application/ld+json">
{!! is_array($schemaJson) ? json_encode($schemaJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $schemaJson !!}
</script>
@endif
