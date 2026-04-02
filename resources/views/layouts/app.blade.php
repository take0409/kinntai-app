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
        <div class="site-header-inner">
            <a href="{{ auth()->user()->is_admin ? '/admin/attendance/list' : '/attendance' }}" class="site-logo">
                <img src="{{ asset('coachtech-logo.png') }}" alt="COACHTECH">
            </a>
            <nav class="site-nav">
                @if(auth()->user()->is_admin)
                    <a href="/admin/attendance/list">勤怠一覧</a>
                    <a href="/admin/staff/list">スタッフ一覧</a>
                    <a href="/stamp_correction_request/list">申請一覧</a>
                @else
                    <a href="/attendance">勤怠</a>
                    <a href="/attendance/list">勤怠一覧</a>
                    <a href="/stamp_correction_request/list">申請</a>
                @endif
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit" class="nav-button">ログアウト</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="page">
        @if (session('status'))
            <div class="flash-message">{{ session('status') }}</div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>
</body>
</html>
