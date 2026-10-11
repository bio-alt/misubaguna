{{-- Other names buyers search for, and the link to the same page in the other language. --}}
@if(! empty($alsoKnownAs))
    <p class="pd-aka">{{ __('Also known as') }}: {{ implode(', ', $alsoKnownAs) }}</p>
@endif
@if(! empty($languageLink))
    <p class="pd-lang"><a href="{{ $languageLink['url'] }}" hreflang="{{ $languageLink['lang'] }}" lang="{{ $languageLink['lang'] }}">{{ $languageLink['label'] }}</a></p>
@endif
