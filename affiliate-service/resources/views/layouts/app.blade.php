<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'トップ') | 馬肉特急・うましっぽ アンバサダー</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.theme-head')
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
    @include('partials.site-header')
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
