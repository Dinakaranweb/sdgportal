@extends('layouts.site')

@section('content')
    <section class="hero">
        <div class="shell">
            <span class="eyebrow">The 2030 Agenda</span>
            <h1>{{ setting('goals_page_title', 'Sustainable Development Goals') }}</h1>
            @if ($intro = setting('goals_page_intro'))
                <p class="hero-lede">{{ $intro }}</p>
            @endif
        </div>
    </section>

    <nav class="crumbs" aria-label="Breadcrumb">
        <div class="shell">
            <ol>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li aria-current="page">All goals</li>
            </ol>
        </div>
    </nav>

    <section class="section">
        <div class="shell">
            @if ($goals->isEmpty())
                <p class="empty-note">No goals have been published yet.</p>
            @else
                <div class="goal-grid" style="margin-bottom:clamp(2rem,1.5rem+2vw,3.25rem)">
                    @foreach ($goals as $goal)
                        <x-site.goal-tile :goal="$goal" />
                    @endforeach
                </div>

                <div class="section-head">
                    <h2>Goal by goal</h2>
                    <p>Each goal opens on its own page, with the sub menus and questions listed down the side.</p>
                </div>

                <div class="card-grid cols-3">
                    @foreach ($goals as $goal)
                        <x-site.goal-card :goal="$goal" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
