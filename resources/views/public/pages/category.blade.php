@extends('public.layout')

@push('head')
    <link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
@endpush

@section('content')
@php
    $count = $products->count();
    $catalogPdf = \App\Support\ProductContent::CATALOG_PDF;
    $waUrl = \App\Support\ProductContent::whatsappUrl($category->name);
@endphp
<div class="ds">
    <div class="ds-wrap">
        <div class="pc-head">
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
            <h1>{{ $category->name }}</h1>
            <p class="pc-lede">{{ $category->summary ?? ($category->name . ' from PT Misuba Guna Indonesia.') }}</p>
            @if($category->description_html)
                <div class="pd-prose">{!! $category->description_html !!}</div>
            @endif
        </div>

        <div class="pc-toolbar">
            <h2>{{ $count }} {{ \Illuminate\Support\Str::plural('product', $count) }}</h2>
            <a href="{{ $catalogPdf }}" download>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4v11m0 0l-4-4m4 4l4-4M5 19h14"/></svg>
                Download company catalog (PDF)
            </a>
        </div>

        <ul class="pc-grid">
            @foreach($products as $item)
                @include('public.partials.product-card', ['item' => $item, 'compact' => false])
            @endforeach
        </ul>
    </div>

    <section class="ds-band" aria-labelledby="pc-band-title">
        <div class="ds-wrap">
            <div class="ds-band-in">
                <div>
                    <h2 id="pc-band-title">Not sure which {{ $category->name }} product fits?</h2>
                    <p>Send your flow, pressure, temperature and media. Our sales team will recommend a model and quote.</p>
                </div>
                @include('public.partials.quote-actions', ['subject' => $category->name])
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
@endsection
