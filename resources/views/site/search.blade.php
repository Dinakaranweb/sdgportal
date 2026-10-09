@extends('layouts.site')

@section('content')
    <section class="search-hero">
        <div class="shell">
            <h1 style="margin-bottom:1.1rem">Search the portal</h1>
            <form class="search-big" action="{{ route('search') }}" method="get" role="search">
                <label class="visually-hidden" for="q">Search term</label>
                <input type="search" id="q" name="q" value="{{ $term }}" placeholder="Policy, report, figure, goal number" autofocus>
                <button type="submit" class="btn btn--solid">Search</button>
            </form>
        </div>
    </section>

    <nav class="crumbs" aria-label="Breadcrumb">
        <div class="shell">
            <ol>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li aria-current="page">Search</li>
            </ol>
        </div>
    </nav>

    <section class="section">
        <div class="shell">
            @if ($term === '')
                <p class="empty-note">Enter a goal number, or a word from a policy, report or figure.</p>
            @else
                @php $total = $goals->count() + $pages->count(); @endphp

                <div class="section-head">
                    @if ($matchedGoal)
                        <h2>Goal {{ $matchedGoal->code }}: {{ $matchedGoal->title }}</h2>
                        <p>Everything published under this goal.</p>
                    @else
                        <h2>{{ $total }} {{ \Illuminate\Support\Str::plural('result', $total) }} for &ldquo;{{ $term }}&rdquo;</h2>
                    @endif
                </div>

                @if ($total === 0)
                    <p class="empty-note">Nothing matched that search. Try a goal number such as 13, a policy name, or a shorter term.</p>
                @else
                    <div class="result-list">
                        @foreach ($goals as $goal)
                            <a class="result" href="{{ $goal->link }}" style="--tile:{{ $goal->color }}">
                                <span class="kicker">Goal {{ $goal->code }}</span>
                                <h3>{{ $goal->title }}</h3>
                                <p>{{ excerpt($goal->statement ?: $goal->summary, 180) }}</p>
                            </a>
                        @endforeach

                        @foreach ($pages as $page)
                            <a class="result" href="{{ $page->url }}" style="--tile:{{ $page->color ?: $page->goal->color }}">
                                <span class="kicker">
                                    Goal {{ $page->goal->code }}
                                    &middot; {{ $page->type === 'question' ? 'Question' : 'Sub menu' }}
                                </span>
                                <h3>{{ $page->title }}</h3>
                                @if (filled($page->summary))
                                    <p>{{ excerpt($page->summary, 180) }}</p>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </section>
@endsection
