@extends('layouts.site')

@php
    $banner = media_url($page->banner_image);
    $narrow = $page->layout === 'narrow';
    $wide = $page->layout === 'wide';
@endphp

@section('content')
    <section @class(['hero', 'hero--image' => (bool) $banner])
             @if ($banner) style="--hero-image:url('{{ $banner }}')" @endif>
        <div class="shell">
            <h1>{{ $page->title }}</h1>
            @if (filled($page->summary))
                <p class="hero-lede">{{ $page->summary }}</p>
            @endif
        </div>
    </section>

    <nav class="crumbs" aria-label="Breadcrumb">
        <div class="shell">
            <ol>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li aria-current="page">{{ $page->title }}</li>
            </ol>
        </div>
    </nav>

    <div class="section">
        <div @class(['shell', 'shell--narrow' => $narrow, 'shell--wide' => $wide])>
            @if (filled($page->image))
                <figure class="figure-block" style="margin-bottom:2rem">
                    <img src="{{ media_url($page->image) }}" alt="{{ $page->title }}">
                </figure>
            @endif

            @if (filled($page->description))
                <div class="prose" style="margin-bottom:2rem">{!! rich_text($page->description) !!}</div>
            @endif

            <x-site.blocks :blocks="$blocks" />

            @if ($blocks->isEmpty() && blank($page->description))
                <p class="empty-note">Content for this page has not been published yet.</p>
            @endif
        </div>
    </div>
@endsection
