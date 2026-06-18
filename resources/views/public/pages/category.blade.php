@extends('public.layout')

@section('content')
<article>
    <h1>{{ $category->name }}</h1>
    @if($category->summary)
        <p>{{ $category->summary }}</p>
    @endif
    {!! $category->description_html !!}

    @if($products->count())
    <section>
        <h2>Products</h2>
        <ul>
            @foreach($products as $product)
                <li><a href="{{ $product->url_path }}">{{ $product->name }}</a></li>
            @endforeach
        </ul>
    </section>
    @endif
</article>
@endsection
