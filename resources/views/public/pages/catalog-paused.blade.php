@extends('public.layout')

@push('head')
    <link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
@endpush

@section('content')
<div class="ds qr">
    <div class="ds-wrap qr-wrap">
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

        <h1 class="qr-title">We are updating our catalog</h1>
        <p class="qr-lede">Our company catalog is being revised and will be back here soon. Please wait, or contact our sales team now for product information, datasheets and quotations.</p>

        <div class="ds-actions">
            <a class="ds-btn" href="/request-a-quote/">Request a quote</a>
            <a class="ds-btn" href="{{ \App\Support\ProductContent::whatsappUrl('your product catalog') }}" target="_blank" rel="noopener">Chat on WhatsApp</a>
            <a class="ds-btn" href="/contact-us/">Contact us</a>
        </div>
    </div>
</div>
@endsection
