<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>管理ログイン | アフィリエイト管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container" style="max-width: 420px;">
    <div class="card card-body mt-5 text-center">
        <h1 class="h5 mb-4">アフィリエイト管理画面</h1>
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <a href="{{ route('admin.auth.google.redirect') }}" class="btn btn-primary">
            Googleアカウントでログイン
        </a>
        <p class="text-muted small mt-3 mb-0">許可されたアカウントのみログインできます。</p>
    </div>
</div>
</body>
</html>
