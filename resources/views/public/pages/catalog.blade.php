@extends('public.layout')

@section('content')
<article>
    <h1>{{ $page->headline ?? $page->title }}</h1>
    {!! $page->content_html !!}
    <p><a href="/wp-content/uploads/2023/11/Misuba-Guna-Indonesia-Company-Profile_Interactive.pdf">Download Company Profile (PDF)</a></p>
</article>
@endsection
