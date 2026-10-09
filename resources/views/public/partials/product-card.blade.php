{{-- One product in a listing. Pass `item`; pass `compact => true` for the small related-products cards. --}}
@php
    $compact = $compact ?? false;
    $itemName = $item->name;
    $itemGallery = array_values(array_filter((array) $item->gallery));
    $thumb = $itemGallery[0] ?? $item->og_image_path;
    $href = $item->url_path ?: '/product/' . $item->slug . '/';
    $itemFacts = $compact ? [] : \App\Support\ProductContent::keyFacts(\App\Support\ProductContent::specRows($item->technical_specs), 2);
@endphp
<li class="pc-card">
    <div class="pc-media">
        @if($thumb)
            <img src="{{ asset($thumb) }}" alt="" loading="lazy">
        @else
            @include('public.partials.plate', ['label' => 'Photo on request'])
        @endif
    </div>
    <div class="pc-body">
        @if($item->product_subgroup)
            <p class="pc-group">{{ $item->product_subgroup }}</p>
        @endif
        <h3 class="pc-title"><a href="{{ $href }}">{{ $itemName }}</a></h3>
        @if(! $compact && $item->summary)
            <p class="pc-summary">{{ $item->summary }}</p>
        @endif

        @if(count($itemFacts) > 0)
            <dl class="pc-facts">
                @foreach($itemFacts as $fact)
                    <div>
                        <dt>{{ $fact['label'] }}</dt>
                        <dd>{{ $fact['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif

        <div class="pc-foot">
            <a class="pc-quote" href="/contact-us/">Request a quote<span class="ds-sr"> for {{ $itemName }}</span></a>
            <span class="pc-view" aria-hidden="true">View details</span>
        </div>
    </div>
</li>
