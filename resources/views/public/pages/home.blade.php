@extends('public.layout')

@section('content')
<section>
    <h1>{{ $page->headline ?? $page->title }}</h1>
    @if($page->excerpt)
        <p>{{ $page->excerpt }}</p>
    @endif
    {!! $page->content_html !!}
</section>

<section>
    <h2>Our Product Groups</h2>
    <ul>
        @foreach($categories as $category)
            <li><a href="{{ $category->url_path }}">{{ $category->name }}</a></li>
        @endforeach
    </ul>
</section>

<section>
    <h2>Our Products</h2>
    <ul>
        @foreach($products as $product)
            <li><a href="{{ $product->url_path }}">{{ $product->name }}</a></li>
        @endforeach
    </ul>
</section>
@endsection
