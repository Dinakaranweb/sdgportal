@extends('layouts.site')

@section('content')
    @php
        $heroImage = media_url(setting('home_hero_image'));
        $pageCount = \App\Models\GoalPage::where('is_active', true)->count();
    @endphp

    <section @class(['hero', 'hero--image' => (bool) $heroImage])
             @if ($heroImage) style="--hero-image:url('{{ $heroImage }}')" @endif>
        <div class="shell">
            @if ($eyebrow = setting('home_hero_eyebrow'))
                <span class="eyebrow">{{ $eyebrow }}</span>
            @endif
            <h1>{{ setting('home_hero_title', setting('site_name')) }}</h1>
            @if ($text = setting('home_hero_text'))
                <p class="hero-lede">{{ $text }}</p>
            @endif

            <div class="hero-actions">
                @if ($ctaUrl = setting('home_hero_cta_url'))
                    <a class="btn btn--light" href="{{ portal_url($ctaUrl) }}">
                        {{ setting('home_hero_cta_label', 'Explore the goals') }}
                        <x-icon name="arrow-right" size="16" />
                    </a>
                @endif
                <a class="btn btn--on-colour" href="{{ route('goals.index') }}">All goal pages</a>
            </div>

            <div class="hero-stats">
                <div><strong>{{ $goals->count() }}</strong><span>Goals reported</span></div>
                <div><strong>{{ $pageCount }}</strong><span>Evidence pages</span></div>
                <div><strong>2030</strong><span>Agenda horizon</span></div>
            </div>
        </div>
    </section>

    @if (setting('home_intro_title') || setting('home_intro_text'))
        <section class="section section--surface">
            <div class="shell">
                <div class="with-sidebar--reverse with-sidebar">
                    <div class="prose">
                        @if ($title = setting('home_intro_title'))
                            <h2>{{ $title }}</h2>
                        @endif
                        {!! rich_text(setting('home_intro_text')) !!}
                    </div>
                    <aside class="sidebar">
                        <div class="sidebar-card">
                            <p class="sidebar-title">Share these pages</p>
                            <div class="sidebar-note">
                                <p>Every goal and every sub page has its own permanent address, so a single link can be handed to a ranking body or a partner portal.</p>
                                <p><a href="{{ route('sitemap') }}">View the full address list</a></p>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </section>
    @endif

    <section class="section" id="goals" aria-labelledby="goals-heading">
        <div class="shell">
            <div class="section-head">
                <span class="eyebrow">Goal index</span>
                <h2 id="goals-heading">{{ setting('home_goals_title', 'The 17 goals') }}</h2>
                @if ($text = setting('home_goals_text'))
                    <p>{{ $text }}</p>
                @endif
            </div>

            @if ($goals->isEmpty())
                <p class="empty-note">No goals have been published yet.</p>
            @else
                <div class="goal-grid">
                    @foreach ($goals as $goal)
                        <x-site.goal-tile :goal="$goal" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if ($featured->isNotEmpty())
        <section class="section section--soft" aria-labelledby="featured-heading">
            <div class="shell">
                <div class="section-head">
                    <span class="eyebrow">In focus</span>
                    <h2 id="featured-heading">Where we are making the most progress</h2>
                </div>
                <div class="card-grid cols-3">
                    @foreach ($featured as $goal)
                        <x-site.goal-card :goal="$goal" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
