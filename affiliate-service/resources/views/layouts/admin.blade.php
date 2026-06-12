<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', '管理') | アフィリエイト管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">アフィリエイト管理</a>
            <div class="navbar-nav me-auto">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">ダッシュボード</a>
                <a class="nav-link" href="{{ route('admin.affiliates.index') }}">アフィリエイター</a>
                <a class="nav-link" href="{{ route('admin.rewards.index') }}">成果・報酬</a>
                <a class="nav-link" href="{{ route('admin.payouts.index') }}">支払い</a>
                @if (session('admin_role') === 'manager')
                    <a class="nav-link" href="{{ route('admin.settings.edit') }}">設定</a>
                @endif
            </div>
            <div class="navbar-nav">
                <span class="navbar-text me-3">
                    {{ session('admin_name') }}
                    <span class="badge {{ session('admin_role') === 'manager' ? 'bg-primary' : 'bg-secondary' }}">{{ session('admin_role') === 'manager' ? '管理' : '運用' }}</span>
                </span>
                <form method="post" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-light">ログアウト</button>
                </form>
            </div>
        </div>
    </nav>
    <main class="container-fluid p-4">
        @foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $cls)
            @if (session($key))
                <div class="alert alert-{{ $cls }}">{{ session($key) }}</div>
            @endif
        @endforeach
        @yield('content')
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
