<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'アフィリエイト')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">アフィリエイトプログラム</span>
            <div>
                @if (session('affiliate_authenticated'))
                    <a class="text-light me-3 text-decoration-none" href="{{ route('affiliate.mypage') }}">マイページ</a>
                    <a class="text-light me-3 text-decoration-none" href="{{ route('affiliate.inquiry.create') }}">お問い合わせ</a>
                    <form method="post" action="{{ route('affiliate.logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light">ログアウト</button>
                    </form>
                @else
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
