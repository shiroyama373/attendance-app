<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '勤怠管理システム - 管理者')</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
    <header class="header">
        <h1>COACHTECH</h1>
        @auth  
        <nav>
            <a href="{{ route('admin.attendance.index') }}">勤怠一覧</a>
            <a href="{{ route('admin.staff.index') }}">スタッフ一覧</a>
            <a href="{{ route('stamp_correction_request.index') }}">申請一覧</a>
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit">ログアウト</button>
            </form>
        </nav>
        @endauth
    </header>

    <main class="container">

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>