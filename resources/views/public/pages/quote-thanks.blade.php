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

        <h1 class="qr-title">{{ __('Quote request received') }}</h1>
        <p class="qr-lede">{{ __('Thank you. Our sales team will review your request and reply by email. Please quote this reference if you contact us:') }}</p>
        <p class="qr-ref">{{ $inquiry->reference }}</p>

        <dl class="qr-answers">
            <div>
                <dt>{{ __('Product / model') }}</dt>
                <dd>
                    @if($productUrl)
                        <a href="{{ $productUrl }}">{{ $inquiry->product_name }}</a>
                    @else
                        {{ $inquiry->item ?: $inquiry->product_name }}
                    @endif
                </dd>
            </div>
            @foreach($inquiry->answers ?? [] as $answer)
                <div>
                    <dt>{{ $answer['label'] }}</dt>
                    <dd>{{ $answer['value'] }}</dd>
                </div>
            @endforeach
            @if(! empty($inquiry->files))
                <div>
                    <dt>{{ __('Files') }}</dt>
                    <dd>{{ implode(', ', array_column($inquiry->files, 'name')) }}</dd>
                </div>
            @endif
        </dl>

        <div class="ds-actions">
            <a href="{{ $waUrl }}" class="ds-btn" target="_blank" rel="noopener">{{ __('Chat on WhatsApp') }}</a>
            <a href="{{ $productUrl ?: (app()->getLocale() === 'id' ? '/id/' : '/catalog/') }}" class="ds-btn ds-btn--line">
                {{ $productUrl ? __('Back to product details') : __('Browse products') }}
            </a>
        </div>
    </div>
</div>
@endsection
