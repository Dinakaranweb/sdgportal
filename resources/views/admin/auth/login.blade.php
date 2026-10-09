<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Sign in &mdash; {{ setting('site_short_name', 'SDG Portal') }}</title>
    <link rel="icon" href="{{ media_url(setting('favicon')) ?: asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
</head>
<body>
<main class="signin">
    <div class="signin-card">
        <span class="mark" aria-hidden="true">SDG</span>
        <h1>Control panel</h1>
        <p class="hint">Sign in to manage the goals, sub menus and page content.</p>

        @if ($errors->any())
            <div class="flash flash-bad" style="margin-bottom:1.1rem" role="alert">
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="post" action="{{ route('admin.login.attempt') }}">
            @csrf

            <div class="field">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       @class(['is-invalid' => $errors->has('email')])
                       required autofocus autocomplete="username">
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       @class(['is-invalid' => $errors->has('password')])
                       required autocomplete="current-password">
            </div>

            <div class="field">
                <label class="check">
                    <input type="checkbox" name="remember" value="1">
                    <span>Keep me signed in on this device</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%">Sign in</button>
        </form>

        <p class="hint" style="margin-top:1.25rem;margin-bottom:0">
            <a href="{{ route('home') }}">Back to the portal</a>
        </p>
    </div>
</main>
</body>
</html>
