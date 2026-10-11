@extends('public.layout')

@push('head')
    <link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
@endpush

@section('content')
@php
    $groupNames = [
        'Flexible Joint' => 'Expansion Joint & Selang Fleksibel',
        'Sealing System' => 'Sistem Sealing',
        'Corrosion Protection' => 'Lining & Proteksi Korosi',
        'Thermal Systems' => 'Sistem Termal',
        'Power Transfer' => 'Transmisi Daya',
    ];
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
            <h1>Produk Industri PT Misuba Guna Indonesia</h1>
            <p class="pc-lede">PT Misuba Guna Indonesia adalah supplier produk teknik industri dan kontraktor pemeliharaan pabrik yang berbasis di Tangerang, Banten. Kami memasok expansion joint, gland packing, gasket, mechanical seal, lining PTFE, karet dan keramik, cooling tower, heat exchanger, dan coupling untuk pabrik di seluruh Indonesia.</p>
            <p class="pd-lang"><a href="/catalog/" hreflang="en" lang="en">Read in English</a></p>
        </div>

        @foreach($groups as $group => $items)
            <section class="pd-related" aria-labelledby="id-group-{{ $loop->index }}">
                <div class="pd-related-head">
                    <h2 class="ds-h2" id="id-group-{{ $loop->index }}">{{ $groupNames[$group] ?? $group }}</h2>
                </div>
                <ul class="pc-grid">
                    @foreach($items as $item)
                        @include('public.partials.product-card', ['item' => $item, 'compact' => false])
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>

    <section class="ds-band" aria-labelledby="id-band-title">
        <div class="ds-wrap">
            <div class="ds-band-in">
                <div>
                    <h2 id="id-band-title">Butuh penawaran harga atau rekomendasi produk?</h2>
                    <p>Kirimkan kondisi operasi, ukuran, dan jumlah yang dibutuhkan. Tim sales kami akan memeriksa detailnya dan mengirimkan penawaran.</p>
                </div>
                @include('public.partials.quote-actions', ['subject' => 'produk industri'])
            </div>
        </div>
    </section>
</div>
@endsection
