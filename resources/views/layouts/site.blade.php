@php
    use App\Support\Theme;

    $activeGoal = $goal ?? null;
    $meta = array_merge([
        'title' => null,
        'description' => null,
        'image' => null,
        'canonical' => null,
        'robots' => null,
        'keywords' => null,
    ], $meta ?? []);

    $siteName = setting('site_name', config('app.name'));
    $shortName = setting('site_short_name', $siteName);
    $title = filled($meta['title']) ? $meta['title'].' | '.$shortName : $siteName;
    $description = $meta['description'] ?: setting('meta_description_default');
    $shareImage = media_url($meta['image'] ?: setting('og_image'));
    $fontUrl = Theme::fontUrl();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>

    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif
    @if ($keywords = $meta['keywords'] ?: setting('meta_keywords_default'))
        <meta name="keywords" content="{{ $keywords }}">
    @endif
    @if ($meta['robots'])
        <meta name="robots" content="{{ $meta['robots'] }}">
    @endif
    <link rel="canonical" href="{{ $meta['canonical'] ?: url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $meta['title'] ?: $siteName }}">
    @if ($description)
        <meta property="og:description" content="{{ $description }}">
    @endif
    <meta property="og:url" content="{{ $meta['canonical'] ?: url()->current() }}">
    @if ($shareImage)
        <meta property="og:image" content="{{ $shareImage }}">
        <meta name="twitter:card" content="summary_large_image">
    @else
        <meta name="twitter:card" content="summary">
    @endif

    @if ($favicon = media_url(setting('favicon')))
        <link rel="icon" href="{{ $favicon }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    @if ($fontUrl)
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="{{ $fontUrl }}">
    @endif

    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}?v={{ setting('theme_cache_key', '1') }}">

    {{-- Theme tokens are written from the saved settings and, on a goal page,
         from that goal's own palette. --}}
    <style>{!! Theme::styleBlock($activeGoal) !!}</style>

    @stack('head')

    @if ($analytics = setting('analytics_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $analytics }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $analytics }}');
        </script>
    @endif
</head>
<body @class(['tiles--'.Theme::tileShape()])>
<a class="skip-link" href="#main">Skip to content</a>

@if (setting('show_utility_bar') === '1')
    <div class="utility">
        <div class="shell">
            <span>{{ setting('utility_message') }}</span>
            @if (($utilityMenu ?? collect())->isNotEmpty())
                <ul class="utility-links">
                    @foreach ($utilityMenu as $item)
                        @if ($url = $item->resolved_url)
                            <li><a href="{{ $url }}" @if ($item->open_in_new_tab) target="_blank" rel="noopener" @endif>{{ $item->label }}</a></li>
                        @endif
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endif

<header class="masthead">
    <div class="shell">
        @php
            $logo = media_url(setting('site_logo'));
            $institution = setting('institution_name');
        @endphp
        <a href="{{ route('home') }}" @class(['brand', 'brand--with-logo' => (bool) $logo])>
            @if ($logo)
                {{-- The uploaded lockup already carries the institution name, so
                     only the portal name is repeated beside it. --}}
                <img src="{{ $logo }}" alt="{{ $institution ?: $siteName }}">
            @else
                <span class="brand-mark" aria-hidden="true">SDG</span>
            @endif
            <span class="brand-text">
                <span class="brand-name">{{ $shortName }}</span>
                @if (! $logo && $institution)
                    <span class="brand-sub">{{ $institution }}</span>
                @endif
            </span>
        </a>

        <div class="masthead-tools">
            @if (setting('show_search') === '1')
                <form class="search-form" action="{{ route('search') }}" method="get" role="search">
                    <label class="visually-hidden" for="site-search">Search the portal</label>
                    <input type="search" id="site-search" name="q" placeholder="Search" value="{{ request('q') }}">
                    <button type="submit" aria-label="Search"><x-icon name="search" size="17" /></button>
                </form>
            @endif

            <button type="button" class="nav-toggle" data-nav-toggle aria-expanded="false" aria-controls="mainnav">
                <span class="bars" aria-hidden="true"><span></span><span></span><span></span></span>
                Menu
            </button>
        </div>

        <nav class="mainnav" id="mainnav" aria-label="Main">
            <button type="button" class="nav-close" data-nav-close aria-label="Close menu">&times;</button>
            <ul>
                @foreach (($headerMenu ?? collect()) as $item)
                    @php $url = $item->resolved_url; $kids = $item->children->where('is_active', true); @endphp
                    <li @class(['has-children' => $kids->isNotEmpty()])>
                        @if ($url)
                            <a href="{{ $url }}" @if ($item->open_in_new_tab) target="_blank" rel="noopener" @endif>
                                {{ $item->label }}
                            </a>
                        @else
                            <span class="nav-label">{{ $item->label }}</span>
                        @endif
                        @if ($kids->isNotEmpty())
                            <button type="button" class="nav-caret" data-nav-caret aria-expanded="false" aria-label="Show {{ $item->label }} submenu">
                                <x-icon name="chevron-right" size="16" />
                            </button>
                            <ul class="submenu">
                                @foreach ($kids as $child)
                                    @if ($childUrl = $child->resolved_url)
                                        <li><a href="{{ $childUrl }}" @if ($child->open_in_new_tab) target="_blank" rel="noopener" @endif>{{ $child->label }}</a></li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach

                @if (($navGoals ?? collect())->isNotEmpty())
                    <li class="has-children">
                        <a href="{{ route('goals.index') }}">Goals</a>
                        <button type="button" class="nav-caret" data-nav-caret aria-expanded="false" aria-label="Show the goal list">
                            <x-icon name="chevron-right" size="16" />
                        </button>
                        <ul class="submenu">
                            @foreach ($navGoals as $navGoal)
                                <li>
                                    <a href="{{ $navGoal->link }}">
                                        <span class="swatch" style="background:{{ $navGoal->color }}" aria-hidden="true"></span>
                                        <span>{{ $navGoal->code }}. {{ $navGoal->display_title }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif
            </ul>

            @if (setting('show_search') === '1')
                <form class="search-form" action="{{ route('search') }}" method="get" role="search">
                    <label class="visually-hidden" for="drawer-search">Search the portal</label>
                    <input type="search" id="drawer-search" name="q" placeholder="Search the portal">
                    <button type="submit" aria-label="Search"><x-icon name="search" size="17" /></button>
                </form>
            @endif
        </nav>
    </div>
</header>

<div class="nav-overlay" data-nav-overlay></div>

<main id="main" class="page-body">
    @yield('content')
</main>

@if (setting('footer_show_goal_strip') === '1' && ($navGoals ?? collect())->isNotEmpty())
    <section class="goal-strip" aria-labelledby="strip-heading">
        <div class="shell">
            <h2 id="strip-heading">Jump to a goal</h2>
            <div class="strip-row">
                @foreach ($navGoals as $stripGoal)
                    <a href="{{ $stripGoal->link }}"
                       style="--tile:{{ $stripGoal->color }};--tile-ink:{{ $stripGoal->text_color }}"
                       title="Goal {{ $stripGoal->code }}: {{ $stripGoal->title }}">
                        <span aria-hidden="true">{{ $stripGoal->code }}</span>
                        <span class="visually-hidden">Goal {{ $stripGoal->code }}: {{ $stripGoal->title }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

<footer class="site-footer">
    <div class="shell">
        <div class="footer-grid">
            <div class="footer-about">
                <div class="footer-brand">
                    @if ($footerLogo = media_url(setting('site_logo_footer') ?: setting('site_logo')))
                        <img src="{{ $footerLogo }}" alt="{{ $siteName }}">
                    @else
                        <span class="brand-mark" aria-hidden="true">SDG</span>
                    @endif
                    <strong>{{ $shortName }}</strong>
                </div>
                @if ($about = setting('footer_about'))
                    <p>{{ $about }}</p>
                @endif
                @php
                    $social = collect([
                        'facebook' => setting('social_facebook'),
                        'x' => setting('social_x'),
                        'linkedin' => setting('social_linkedin'),
                        'instagram' => setting('social_instagram'),
                        'youtube' => setting('social_youtube'),
                    ])->filter();
                @endphp
                @if ($social->isNotEmpty())
                    <div class="social-row">
                        @foreach ($social as $network => $link)
                            <a href="{{ $link }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($network) }}">
                                <x-icon :name="$network" size="17" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            @if (($footerPrimary ?? collect())->isNotEmpty())
                <div>
                    <h2>Explore</h2>
                    <ul>
                        @foreach ($footerPrimary as $item)
                            @if ($url = $item->resolved_url)
                                <li><a href="{{ $url }}" @if ($item->open_in_new_tab) target="_blank" rel="noopener" @endif>{{ $item->label }}</a></li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (($footerSecondary ?? collect())->isNotEmpty())
                <div>
                    <h2>More</h2>
                    <ul>
                        @foreach ($footerSecondary as $item)
                            @if ($url = $item->resolved_url)
                                <li><a href="{{ $url }}" @if ($item->open_in_new_tab) target="_blank" rel="noopener" @endif>{{ $item->label }}</a></li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (setting('footer_address') || setting('footer_email') || setting('footer_phone'))
                <div>
                    <h2>Contact</h2>
                    <div class="contact-list">
                        @if ($address = setting('footer_address'))
                            <div><x-icon name="pin" size="16" /><span>{!! nl2br(e($address)) !!}</span></div>
                        @endif
                        @if ($email = setting('footer_email'))
                            <div><x-icon name="mail" size="16" /><a href="mailto:{{ $email }}">{{ $email }}</a></div>
                        @endif
                        @if ($phone = setting('footer_phone'))
                            <div><x-icon name="phone" size="16" /><a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">{{ $phone }}</a></div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="footer-base">
            <span>&copy; {{ now()->year }} {{ setting('institution_name') ?: $siteName }}. {{ setting('footer_note') }}</span>

            @if ($credit = setting('footer_credit'))
                <span class="footer-credit">
                    @if ($creditUrl = portal_url(setting('footer_credit_url')))
                        <a href="{{ $creditUrl }}" @if (is_external_url($creditUrl)) target="_blank" rel="noopener" @endif>{{ $credit }}</a>
                    @else
                        {{ $credit }}
                    @endif
                </span>
            @endif

            <ul>
                <li><a href="{{ route('goals.index') }}">All goals</a></li>
            </ul>
        </div>
    </div>
</footer>

<script src="{{ asset('assets/js/site.js') }}" defer></script>
@stack('scripts')
</body>
</html>
