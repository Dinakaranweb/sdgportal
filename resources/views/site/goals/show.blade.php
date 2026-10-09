@extends('layouts.site')

@section('content')
    @include('partials.goal-banner', ['goal' => $goal])

    <nav class="crumbs" aria-label="Breadcrumb">
        <div class="shell">
            <ol>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><a href="{{ route('goals.index') }}">Goals</a></li>
                <li aria-current="page">{{ $goal->display_title }}</li>
            </ol>
        </div>
    </nav>

    <div class="section">
        <div class="shell">
            <div class="with-sidebar">
                @include('partials.goal-sidebar', ['pages' => $pages, 'current' => null, 'goal' => $goal])

                <div>
                    @if (filled($goal->description) || filled($goal->summary))
                        <div class="page-intro">
                            <h2>About this goal</h2>
                            @if (filled($goal->description))
                                <div class="prose">{!! rich_text($goal->description) !!}</div>
                            @else
                                <p class="lede">{{ $goal->summary }}</p>
                            @endif
                        </div>
                    @endif

                    <x-site.blocks :blocks="$blocks" />

                    @if ($blocks->isNotEmpty() && $pages->isNotEmpty())
                        <hr>
                    @endif

                    @include('partials.child-grid', [
                        'children' => $pages,
                        'heading' => 'Sub menus and questions',
                        'subheading' => 'Each page below has its own address and can be linked to directly.',
                    ])

                    @if ($pages->isEmpty() && $blocks->isEmpty())
                        <p class="empty-note">Content for this goal has not been published yet.</p>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection
