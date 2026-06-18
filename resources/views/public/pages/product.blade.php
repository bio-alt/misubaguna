@extends('public.layout')

@section('content')
<article>
    <h1>{{ $product->headline ?? $product->name }}</h1>
    @if($product->summary)
        <p>{{ $product->summary }}</p>
    @endif
    {!! $product->content_html !!}

    @if($product->key_features)
    <section>
        <h2>Key Features and Benefits</h2>
        <ul>
            @foreach($product->key_features as $feature)
                <li>{{ $feature }}</li>
            @endforeach
        </ul>
    </section>
    @endif

    @if($product->applications)
    <section>
        <h2>Applications</h2>
        <ul>
            @foreach($product->applications as $app)
                <li>{{ $app }}</li>
            @endforeach
        </ul>
    </section>
    @endif

    @if($product->materials)
    <section>
        <h2>Construction and Material</h2>
        <ul>
            @foreach($product->materials as $material)
                <li>{{ $material }}</li>
            @endforeach
        </ul>
    </section>
    @endif

    @if($product->technical_specs)
    <section>
        <h2>Technical Notes</h2>
        <ul>
            @foreach($product->technical_specs as $spec)
                <li>{{ $spec }}</li>
            @endforeach
        </ul>
    </section>
    @endif
</article>
@endsection
