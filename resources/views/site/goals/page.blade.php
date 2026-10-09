@extends('layouts.site')

@php
    // A page may override the goal colour for its own branch of the tree.
    $pageColour = $page->color;
    $position = $siblings->pluck('id')->search($page->id);
    $previous = $position !== false && $position > 0 ? $siblings[$position - 1] : null;
    $next = $position !== false && $position < $siblings->count() - 1 ? $siblings[$position + 1] : null;
    $hideSidebar = $page->layout === 'wide';
@endphp

@push('head')
    @if ($pageColour)
        <style>
            :root {
                --goal: {{ $pageColour }};
                --goal-dark: {{ \App\Support\Palette::shade($pageColour, -24) }};
                --goal-light: {{ \App\Support\Palette::shade($pageColour, 84) }};
                --goal-soft: {{ \App\Support\Palette::alpha($pageColour, 0.12) }};
                --goal-ring: {{ \App\Support\Palette::alpha($pageColour, 0.32) }};
                --goal-ink: {{ \App\Support\Palette::readableInk($pageColour) }};
            }
        </style>
    @endif
@endpush

@section('content')
    @include('partials.goal-banner', [
        'goal' => $goal,
        'heading' => $goal->title,
        'subheading' => $goal->statement,
        'bannerImage' => $page->banner_image ?: $goal->banner_image,
    ])

    <nav class="crumbs" aria-label="Breadcrumb">
        <div class="shell">
            <ol>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><a href="{{ route('goals.index') }}">Goals</a></li>
                <li><a href="{{ $goal->url }}">{{ $goal->display_title }}</a></li>
                @foreach ($trail as $ancestor)
                    <li><a href="{{ $ancestor->url }}">{{ $ancestor->title }}</a></li>
                @endforeach
                <li aria-current="page">{{ \Illuminate\Support\Str::limit($page->title, 60) }}</li>
            </ol>
        </div>
    </nav>

    <div class="section">
        <div class="shell">
            <div @class(['with-sidebar' => ! $hideSidebar])>
                @unless ($hideSidebar)
                    @include('partials.goal-sidebar', ['pages' => $rootPages, 'current' => $page, 'goal' => $goal])
                @endunless

                <div>
                    <div class="page-intro">
                        @if ($page->type === 'question')
                            <span class="page-type-chip"><x-icon name="dot" size="10" /> Reporting question</span>
                        @endif
                        @if (filled($page->code))
                            <span class="page-code">{{ $page->code }}</span>
                        @endif
                        <h1>{{ $page->title }}</h1>
                        @if (filled($page->summary))
                            <p class="lede">{{ $page->summary }}</p>
                        @endif
                    </div>

                    @if (filled($page->image) && $page->layout !== 'wide')
                        <figure class="figure-block" style="margin-bottom:2rem">
                            <img src="{{ media_url($page->image) }}" alt="{{ $page->title }}">
                        </figure>
                    @endif

                    @if (filled($page->description))
                        <div class="prose" style="margin-bottom:2rem">{!! rich_text($page->description) !!}</div>
                    @endif

                    <x-site.blocks :blocks="$blocks" />

                    @if ($blocks->isNotEmpty() && $children->isNotEmpty())
                        <hr>
                    @endif

                    @include('partials.child-grid', [
                        'children' => $children,
                        'heading' => 'Within this section',
                    ])

                    @if ($blocks->isEmpty() && $children->isEmpty() && blank($page->description))
                        <p class="empty-note">Content for this page has not been published yet.</p>
                    @endif

                    @if ($previous || $next)
                        <nav class="page-pager" aria-label="Sibling pages">
                            @if ($previous)
                                <a href="{{ $previous->url }}" rel="prev">
                                    <span class="dir">Previous</span>
                                    <span>{{ \Illuminate\Support\Str::limit($previous->title, 70) }}</span>
                                </a>
                            @else
                                <span></span>
                            @endif
                            @if ($next)
                                <a href="{{ $next->url }}" rel="next" class="next">
                                    <span class="dir">Next</span>
                                    <span>{{ \Illuminate\Support\Str::limit($next->title, 70) }}</span>
                                </a>
                            @endif
                        </nav>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
