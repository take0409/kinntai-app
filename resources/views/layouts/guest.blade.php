<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="site-header">
        <div class="site-header__inner site-header__inner--guest">
            <a href="/login" class="site-logo">
                <img src="{{ asset('coachtech-logo.png') }}" alt="COACHTECH">
            </a>
        </div>
    </header>

    <main class="guest-page">
        @if (session('status') && session('status') !== 'verification-link-sent')
            <div class="flash-message flash-message--guest">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
