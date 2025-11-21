{{-- 管理者用共通レイアウト --}}
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <link href="{{ asset('css/reset.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/common.css') }}" rel="stylesheet" />
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header-inner">
            <div class="header-left">
                <h1 class="header-logo">
                    <a class="header-logo__link" href="/admin/attendance/list">
                        <img src="{{ asset('img/logo.svg') }}" alt="ロゴ画像">
                    </a>
                </h1>
            </div>
            @if (!isset($hideSection) || !$hideSection)
            <div class="header-right">
                <a class="header-link" href="/admin/attendance/list">勤怠一覧</a>
                <a class="header-link" href="#">スタッフ一覧</a>
                <a class="header-link" href="#">申請一覧</a>
                <form action="/logout" method="POST">
                    @csrf
                    <button class="header-logout">ログアウト</button>
                </form>
            </div>
            @endif
        </div>
    </header>
    @yield('content')
</body>
</html>
