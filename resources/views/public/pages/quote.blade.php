@extends('public.layout')

@push('head')
    <link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
@endpush

@section('content')
@php
    $thumb = $product ? (array_values(array_filter((array) $product->gallery))[0] ?? $product->og_image_path) : null;
    $oldAnswers = (array) old('answers', []);
@endphp

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

        <h1 class="qr-title">{{ $product ? __('Request a quote: :name', ['name' => $product->name]) : __('Request a quote') }}</h1>
        <p class="qr-lede">{{ __('Answer what you can and attach a drawing if you have one. Our sales team will check the details and reply with a quote.') }}</p>

        @if(session('quote_received'))
            <p class="qr-note" role="status">{{ __('Thank you. Your request has been received.') }}</p>
        @endif

        @if($product)
            <section class="qr-product" aria-label="{{ __('Product') }}">
                <div class="qr-product-media">
                    @if($thumb)
                        <img src="{{ asset($thumb) }}" alt="{{ $product->name }}" width="96" height="72" loading="lazy">
                    @else
                        @include('public.partials.plate', ['label' => __('Photo on request')])
                    @endif
                </div>
                <div>
                    @if($product->product_subgroup || $product->product_group)
                        <p class="qr-product-group">{{ $product->product_subgroup ?: $product->product_group }}</p>
                    @endif
                    <p class="qr-product-name">{{ $product->name }}</p>
                    <a href="{{ $productUrl }}">{{ __('Back to product details') }}</a>
                </div>
            </section>
        @endif

        @if($errors->any())
            <div class="qr-errors" role="alert">
                <p><strong>{{ __('Please check the form.') }}</strong> {{ __('Any files you chose must be attached again.') }}</p>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="qr-form" method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
            @csrf
            @if($product)
                <input type="hidden" name="product" value="{{ $product->slug }}">
            @endif
            <input type="hidden" name="source_url" value="{{ old('source_url', $sourceUrl) }}">

            <fieldset class="qr-set">
                <legend>{{ __('What do you need?') }}</legend>
                <div class="qr-field">
                    <label for="qr-item">{{ __('Product / model') }}</label>
                    <input type="text" id="qr-item" name="item" maxlength="160" value="{{ old('item', $prefill['item']) }}" @unless($product) required @endunless>
                </div>
                @foreach($checklist as $i => $label)
                    <div class="qr-field">
                        <label for="qr-answer-{{ $i }}">{{ $label }}</label>
                        <input type="text" id="qr-answer-{{ $i }}" name="answers[{{ $i }}]" maxlength="500" value="{{ $oldAnswers[$i] ?? '' }}">
                    </div>
                @endforeach
                <div class="qr-field">
                    <label for="qr-message">{{ __('Anything else') }} <span class="qr-opt">{{ __('(optional)') }}</span></label>
                    <textarea id="qr-message" name="message" rows="4" maxlength="5000">{{ old('message', $prefill['message']) }}</textarea>
                </div>
                <div class="qr-field">
                    <label for="qr-files">{{ __('Drawings or datasheets') }} <span class="qr-opt">{{ __('(optional)') }}</span></label>
                    <input type="file" id="qr-files" name="files[]" multiple accept=".pdf,.dwg,.dxf,.jpg,.jpeg,.png,.xlsx,.docx">
                    <p class="qr-hint">{{ __('Up to 3 files, 10 MB each: PDF, DWG, DXF, JPG, PNG, XLSX or DOCX.') }}</p>
                </div>
            </fieldset>

            <fieldset class="qr-set">
                <legend>{{ __('Your details') }}</legend>
                <div class="qr-grid">
                    <div class="qr-field">
                        <label for="qr-name">{{ __('Name') }}</label>
                        <input type="text" id="qr-name" name="name" maxlength="120" autocomplete="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="qr-field">
                        <label for="qr-company">{{ __('Company') }}</label>
                        <input type="text" id="qr-company" name="company" maxlength="160" autocomplete="organization" value="{{ old('company') }}" required>
                    </div>
                    <div class="qr-field">
                        <label for="qr-email">{{ __('Email') }}</label>
                        <input type="email" id="qr-email" name="email" maxlength="160" autocomplete="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="qr-field">
                        <label for="qr-phone">{{ __('Phone / WhatsApp') }}</label>
                        <input type="tel" id="qr-phone" name="phone" maxlength="40" autocomplete="tel" value="{{ old('phone') }}" required>
                    </div>
                    <div class="qr-field qr-field--wide">
                        <label for="qr-location">{{ __('Delivery city or site') }} <span class="qr-opt">{{ __('(optional)') }}</span></label>
                        <input type="text" id="qr-location" name="location" maxlength="160" value="{{ old('location') }}">
                    </div>
                </div>
            </fieldset>

            <div class="qr-hp" aria-hidden="true">
                <label for="qr-website">Website</label>
                <input type="text" id="qr-website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="ds-actions">
                <button type="submit" class="ds-btn">{{ __('Send quote request') }}</button>
                <a href="{{ $waUrl }}" class="ds-btn ds-btn--line" target="_blank" rel="noopener">{{ __('Chat on WhatsApp') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
