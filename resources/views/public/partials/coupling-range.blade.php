{{-- Coupling hub: the ten families. Family page: its series, then every family for quick switching. Pass `slug`. --}}
@php
    $isHub = \App\Support\CouplingCatalog::isHub($slug);
    $families = \App\Support\CouplingCatalog::families();
    $family = $isHub ? null : \App\Support\CouplingCatalog::family($slug);
    $quoteUrl = fn (string $subject) => '/contact-us/?' . http_build_query([
        'category' => 'General Inquiry',
        'message' => 'Please quote ' . $subject . '. Motor power (kW): , speed (rpm): , shaft diameters: , quantity: ',
    ]) . '#inquiry-form';
@endphp

@if($isHub)
    <section class="cr" aria-labelledby="cr-title">
        <div class="cr-head">
            <h2 class="ds-h2" id="cr-title">Coupling families</h2>
            <p>{{ count($families) }} families, {{ \App\Support\CouplingCatalog::seriesCount() }} series</p>
        </div>
        <ul class="cr-grid">
            @foreach($families as $item)
                <li class="cr-fam">
                    <div class="cr-media"><img src="{{ asset($item['gallery'][0]) }}" alt="" loading="lazy"></div>
                    <div class="cr-body">
                        <h3 class="cr-name"><a href="{{ $item['url'] }}">{{ $item['name'] }}</a></h3>
                        <p class="cr-text">{{ $item['tile'] }}</p>
                        <ul class="cr-codes" aria-label="Series">
                            @foreach($item['series'] as $series)
                                <li>{{ $series['code'] }}</li>
                            @endforeach
                        </ul>
                        <span class="cr-more" aria-hidden="true">{{ count($item['series']) }} series</span>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
@elseif($family)
    <section class="cr" aria-labelledby="cr-title">
        <div class="cr-head">
            <h2 class="ds-h2" id="cr-title">Series in this range</h2>
            <p>{{ count($family['series']) }} series · dimension tables on request</p>
        </div>
        <ul class="cr-grid cr-grid--series">
            @foreach($family['series'] as $series)
                <li class="cr-ser">
                    <div class="cr-media"><img src="{{ asset($series['image']) }}" alt="{{ $series['name'] }}" loading="lazy"></div>
                    <div class="cr-body">
                        <p class="cr-code">{{ $series['code'] }}</p>
                        <h3 class="cr-name">{{ $series['name'] }}</h3>
                        <p class="cr-text">{{ $series['line'] }}</p>
                        @if(count($series['facts']) > 0)
                            <ul class="cr-facts">
                                @foreach($series['facts'] as $fact)
                                    <li>{{ $fact }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <a class="cr-quote" href="{{ $quoteUrl($series['code'] . ' ' . $series['name']) }}">Ask for price and size<span class="ds-sr">: {{ $series['name'] }}</span></a>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    <nav class="cr-switch" aria-label="Coupling families">
        <h2 class="ds-h2">All coupling families</h2>
        <ul>
            <li><a href="/product/{{ \App\Support\CouplingCatalog::HUB_SLUG }}/">Overview</a></li>
            @foreach($families as $item)
                <li><a href="{{ $item['url'] }}" @if($item['slug'] === $slug) aria-current="page" @endif>{{ $item['name'] }}</a></li>
            @endforeach
        </ul>
    </nav>
@endif
