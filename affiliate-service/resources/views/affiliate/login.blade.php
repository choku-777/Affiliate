@extends('layouts.app')
@section('title', 'アンバサダー ログイン')

@section('content')
<h1 class="h4 mb-3">アンバサダー ログイン</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="post" action="{{ route('affiliate.login.post') }}" class="card card-body" style="max-width: 480px;">
    @csrf
    <div class="mb-3">
        <label class="form-label">メールアドレス</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
    </div>
    <div class="mb-3">
        <label class="form-label">パスワード</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div>
        <button type="submit" class="btn btn-primary">ログイン</button>
    </div>
    <p class="text-muted small mt-3 mb-0">
        登録がお済みでない方は <a href="{{ route('register.create') }}">こちらから登録</a>してください。
    </p>
</form>
@endsection
