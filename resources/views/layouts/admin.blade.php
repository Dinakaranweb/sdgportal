@php
    $user = auth()->user();
    $goalCount = \App\Models\Goal::count();
    $pageCount = \App\Models\GoalPage::count();
    $section = $section ?? '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="editor-upload" content="{{ route('admin.editor.upload') }}">
    <title>@yield('title', 'Control panel') &mdash; {{ setting('site_short_name', 'SDG Portal') }}</title>
    <link rel="icon" href="{{ media_url(setting('favicon')) ?: asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('assets/vendor/summernote/summernote-lite.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
</head>
<body>
<div class="ad-shell">
    <nav class="ad-nav" aria-label="Control panel">
        <a href="{{ route('admin.dashboard') }}" class="ad-nav-brand">
            <span class="mark" aria-hidden="true">SDG</span>
            <span>
                <strong>{{ setting('site_short_name', 'SDG Portal') }}</strong>
                <span>Control panel</span>
            </span>
        </a>

        <div class="ad-nav-group">
            <span>Content</span>
            <a class="ad-link {{ $section === 'dashboard' ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}">
                <x-icon name="stats" size="17" /> Overview
            </a>
            <a class="ad-link {{ $section === 'goals' ? 'is-active' : '' }}" href="{{ route('admin.goals.index') }}">
                <x-icon name="gallery" size="17" /> Goals <span class="count">{{ $goalCount }}</span>
            </a>
            <a class="ad-link {{ $section === 'pages' ? 'is-active' : '' }}" href="{{ route('admin.goals.index') }}?focus=pages">
                <x-icon name="list" size="17" /> Sub menus <span class="count">{{ $pageCount }}</span>
            </a>
            <a class="ad-link {{ $section === 'media' ? 'is-active' : '' }}" href="{{ route('admin.media.index') }}">
                <x-icon name="image" size="17" /> Media library
            </a>
        </div>

        <div class="ad-nav-group">
            <span>Appearance</span>
            <a class="ad-link {{ $section === 'settings' ? 'is-active' : '' }}" href="{{ route('admin.settings.edit', 'theme') }}">
                <x-icon name="star" size="17" /> Theme &amp; settings
            </a>
            <a class="ad-link {{ $section === 'menu' ? 'is-active' : '' }}" href="{{ route('admin.menu.index', 'header') }}">
                <x-icon name="link" size="17" /> Navigation
            </a>
        </div>

        @if ($user?->isAdministrator())
            <div class="ad-nav-group">
                <span>Administration</span>
                <a class="ad-link {{ $section === 'users' ? 'is-active' : '' }}" href="{{ route('admin.users.index') }}">
                    <x-icon name="people" size="17" /> Accounts
                </a>
            </div>
        @endif

        <div class="ad-nav-group">
            <span>Portal</span>
            <a class="ad-link" href="{{ route('home') }}" target="_blank" rel="noopener">
                <x-icon name="external" size="17" /> View the portal
            </a>
        </div>

        <div class="ad-nav-foot">
            <div class="who">
                <span class="avatar" aria-hidden="true">{{ $user?->initials() }}</span>
                <span>
                    <strong>{{ $user?->name }}</strong>
                    <span class="role">{{ \App\Models\User::ROLES[$user?->role] ?? 'Editor' }}</span>
                </span>
            </div>
            <form method="post" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm" style="width:100%">Sign out</button>
            </form>
        </div>
    </nav>

    <div class="ad-main">
        <header class="ad-top">
            <button type="button" class="ad-burger" data-ad-burger aria-label="Open the menu">
                <x-icon name="list" size="18" />
            </button>
            <div>
                <h1>@yield('heading', 'Control panel')</h1>
                @hasSection('subheading')
                    <p class="sub">@yield('subheading')</p>
                @endif
            </div>
            <div class="ad-top-actions">@yield('actions')</div>
        </header>

        <div class="ad-body">
            @include('admin.partials.flash')
            @yield('content')
        </div>
    </div>
</div>

<script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/vendor/summernote/summernote-lite.min.js') }}"></script>
<script src="{{ asset('assets/js/editor.js') }}" defer></script>
<script src="{{ asset('assets/js/admin.js') }}" defer></script>
@stack('scripts')
</body>
</html>
