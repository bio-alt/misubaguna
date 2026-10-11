@extends('public.layout')

@section('content')
<article>
    <h1>{{ $page->headline ?? $page->title }}</h1>
    {!! \App\Support\PublicSeo::demoteH1($page->content_html) !!}
</article>
@endsection
