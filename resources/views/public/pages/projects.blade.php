@extends('public.layout')

@section('content')
<article>
    <h1>{{ $page->headline ?? $page->title }}</h1>
    {!! $page->content_html !!}
</article>
@endsection
