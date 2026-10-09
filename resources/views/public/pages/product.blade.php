@extends('public.layout')

@push('head')
    <link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
@endpush

@section('content')
@php
    $name = $product->name;
    $gallery = array_values(array_filter((array) $product->gallery));
    $specRows = \App\Support\ProductContent::specRows($product->technical_specs);
    $facts = \App\Support\ProductContent::keyFacts($specRows, 3);
    $features = array_values(array_filter((array) $product->key_features));
    $applications = array_values(array_filter((array) $product->applications));
    $materials = array_values(array_filter((array) $product->materials));
    $narrative = \App\Support\ProductContent::narrative($product->content_html, [
        'key_features' => count($features) > 0,
        'applications' => count($applications) > 0,
        'materials' => count($materials) > 0,
        'technical_specs' => count($specRows) > 0,
    ]);
    $benefits = array_slice($features, 0, 4);

    // Long text lives in tabs so it never pushes the buying zone down.
    $panels = array_filter([
        'description' => ['title' => 'Description', 'html' => $narrative],
        'features' => ['title' => 'Features', 'items' => count($features) > count($benefits) ? $features : []],
        'applications' => ['title' => 'Applications', 'items' => $applications],
        'materials' => ['title' => 'Materials', 'items' => $materials],
    ], fn ($panel) => ! empty($panel['html']) || ! empty($panel['items']));

    $hasFacts = count($facts) > 0 || count($benefits) > 0;
    $kicker = $product->product_subgroup ?: $product->product_group;
    $waUrl = \App\Support\ProductContent::whatsappUrl($name);
    $catalogPdf = \App\Support\ProductContent::CATALOG_PDF;
@endphp

<div class="ds">
    <div class="ds-wrap">
        <nav class="ds-crumbs" aria-label="Breadcrumb">
            <ol>
                @foreach($breadcrumbs as $crumb)
                    @if($loop->last)
                        <li><span aria-current="page">{{ $crumb['name'] }}</span></li>
                    @else
                        <li><a href="{{ $crumb['url'] }}">{{ $crumb['name'] }}</a></li>
                    @endif
                @endforeach
            </ol>
        </nav>

        <div class="pd">
            <figure class="pd-gallery {{ count($gallery) > 1 ? 'pd-gallery--multi' : '' }}" data-gallery tabindex="{{ count($gallery) > 1 ? '0' : '-1' }}" aria-label="Product photos">
                @if(count($gallery) > 0)
                    <div class="pd-stage">
                        <img id="pd-main" src="{{ asset($gallery[0]) }}" alt="{{ $name }}">
                        @if(count($gallery) > 1)
                            <button type="button" class="pd-nav pd-nav--prev" data-prev aria-label="Previous photo">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>
                            </button>
                            <button type="button" class="pd-nav pd-nav--next" data-next aria-label="Next photo">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                            </button>
                            <span class="pd-count" aria-live="polite"><span data-count>1</span> / {{ count($gallery) }}</span>
                        @endif
                    </div>
                    @if(count($gallery) > 1)
                        <ul class="pd-thumbs">
                            @foreach($gallery as $i => $img)
                                <li>
                                    <button type="button" class="pd-thumb" data-src="{{ asset($img) }}" aria-label="Show photo {{ $i + 1 }} of {{ count($gallery) }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}">
                                        <img src="{{ asset($img) }}" alt="" loading="lazy">
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @else
                    <div class="pd-stage pd-stage--empty">
                        @include('public.partials.plate', ['label' => 'Photo available on request'])
                    </div>
                @endif
            </figure>

            <div class="pd-head">
                @if($kicker)
                    <p class="pd-kicker">{{ $kicker }}</p>
                @endif
                <h1 class="pd-title">{{ $name }}</h1>
                @if($product->summary)
                    <p class="pd-summary">{{ $product->summary }}</p>
                @endif
            </div>

            <aside class="pd-rail" aria-label="Key facts and quote">
                @if(count($benefits) > 0)
                    <ul class="pd-why" aria-label="Why this product">
                        @foreach($benefits as $benefit)
                            <li>{{ $benefit }}</li>
                        @endforeach
                    </ul>
                @endif

                @if(count($facts) > 0)
                    <dl class="pd-facts" aria-label="Key facts">
                        @foreach($facts as $fact)
                            <div>
                                <dt>{{ $fact['label'] }}</dt>
                                <dd>{{ $fact['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                @if(! $hasFacts)
                    <section class="pd-need">
                        <h2>To quote this, tell us</h2>
                        <ul>
                            <li>Line size or dimensions</li>
                            <li>Design pressure and temperature</li>
                            <li>Medium and operating conditions</li>
                            <li>Quantity and delivery location</li>
                        </ul>
                    </section>
                @endif

                <div class="pd-buy-sentinel" aria-hidden="true"></div>
                <div class="pd-buy">
                    <p class="pd-buy-name" aria-hidden="true">{{ $name }}</p>
                    @include('public.partials.quote-actions', ['subject' => $name])
                    <ul class="pd-buy-links">
                        <li>
                            <a href="{{ $catalogPdf }}" download>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4v11m0 0l-4-4m4 4l4-4M5 19h14"/></svg>
                                Download company catalog (PDF)
                            </a>
                        </li>
                    </ul>
                </div>
            </aside>

            @if(count($specRows) > 0 || count($panels) > 0)
                <div class="pd-body">
                    @if(count($specRows) > 0)
                        <section class="pd-section" aria-labelledby="pd-spec-title">
                            <h2 class="ds-h2" id="pd-spec-title">Specifications</h2>
                            <dl class="pd-sheet">
                                @foreach($specRows as $row)
                                    @if($row['label'])
                                        <div>
                                            <dt>{{ $row['label'] }}</dt>
                                            <dd>{{ $row['value'] }}</dd>
                                        </div>
                                    @endif
                                @endforeach
                            </dl>
                            @foreach($specRows as $row)
                                @if(! $row['label'])
                                    <p class="pd-sheet-note">{{ $row['value'] }}</p>
                                @endif
                            @endforeach
                        </section>
                    @endif

                    @if(count($panels) > 0)
                        <section class="pd-section pd-detail" data-tabs aria-label="Product details">
                            @if(count($panels) > 1)
                                <div class="pd-tabs" role="tablist" aria-label="Product details" hidden>
                                    @foreach($panels as $key => $panel)
                                        <button type="button" class="pd-tab" role="tab" id="pd-tab-{{ $key }}" aria-controls="pd-panel-{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" tabindex="{{ $loop->first ? '0' : '-1' }}">{{ $panel['title'] }}</button>
                                    @endforeach
                                </div>
                            @endif
                            @foreach($panels as $key => $panel)
                                <div class="pd-panel" id="pd-panel-{{ $key }}" role="{{ count($panels) > 1 ? 'tabpanel' : 'region' }}" aria-labelledby="{{ count($panels) > 1 ? 'pd-tab-' . $key : 'pd-panel-title-' . $key }}" tabindex="0">
                                    <h2 class="ds-h2" id="pd-panel-title-{{ $key }}">{{ $panel['title'] }}</h2>
                                    @if(! empty($panel['html']))
                                        <div class="pd-prose">{!! $panel['html'] !!}</div>
                                    @else
                                        <ul class="pd-list">
                                            @foreach($panel['items'] as $item)
                                                <li>{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endforeach
                        </section>
                    @endif
                </div>
            @endif
        </div>

        @if($related->isNotEmpty())
            <section class="pd-related" aria-labelledby="pd-related-title">
                <div class="pd-related-head">
                    <h2 class="ds-h2" id="pd-related-title">More in {{ $product->product_group }}</h2>
                    @if($category)
                        <a href="/product-category/{{ $category->slug }}/">See all {{ $categoryCount }} {{ \Illuminate\Support\Str::plural('product', $categoryCount) }}</a>
                    @endif
                </div>
                <ul class="pc-grid pc-grid--small">
                    @foreach($related as $item)
                        @include('public.partials.product-card', ['item' => $item, 'compact' => true])
                    @endforeach
                </ul>
            </section>
        @endif
    </div>

    <section class="ds-band" aria-labelledby="pd-band-title">
        <div class="ds-wrap">
            <div class="ds-band-in">
                <div>
                    <h2 id="pd-band-title">Need a price, sizing or a drawing for {{ $name }}?</h2>
                    <p>Send your operating conditions and quantity. Our sales team will check the details and quote.</p>
                </div>
                @include('public.partials.quote-actions', ['subject' => $name])
            </div>
        </div>
    </section>

    <div class="ds-bar">
        <a href="/contact-us/" class="ds-btn">Request a quote</a>
        <a href="{{ $waUrl }}" class="ds-btn ds-btn--line" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
            WhatsApp
        </a>
    </div>
</div>

<script src="{{ asset('js/catalog.js') }}" defer></script>
@endsection
