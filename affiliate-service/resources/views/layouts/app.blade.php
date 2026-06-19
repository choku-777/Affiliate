<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'トップ') | 馬肉特急・自然派いぬ生活 アンバサダー</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ヘッダーのサイト名：長いのでスマホでは小さく＆折り返して見切れ防止 */
        .navbar-brand { white-space: normal; line-height: 1.2; }
        @media (max-width: 575.98px) {
            .navbar-brand { font-size: 1rem; }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container flex-wrap gap-2">
            <span class="navbar-brand me-0">馬肉特急・自然派いぬ生活 アンバサダー</span>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if (session('affiliate_authenticated'))
                    <a class="text-light text-decoration-none small" href="{{ route('affiliate.mypage') }}">マイページ</a>
                    <a class="text-light text-decoration-none small" href="{{ route('inquiry.create') }}">お問い合わせ</a>
                    <form method="post" action="{{ route('affiliate.logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light">ログアウト</button>
                    </form>
                @else
                    <a class="btn btn-sm btn-outline-light" href="{{ route('inquiry.create') }}">お問い合わせ</a>
                    <a class="btn btn-sm btn-outline-light" href="{{ route('affiliate.login') }}">ログイン</a>
                @endif
            </div>
        </div>
    </nav>
    <main class="container py-4" style="max-width: 720px;">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>
