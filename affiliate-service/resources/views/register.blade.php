@extends('layouts.app')
@section('title', 'アフィリエイト登録')

@section('content')
<h1 class="h4 mb-3">アフィリエイト登録</h1>
<p class="text-muted">お申し込み後、管理者の承認をもってご紹介用URLをメールでお送りします。</p>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="post" action="{{ route('register.store') }}" class="card card-body">
    @csrf
    <div class="mb-3">
        <label class="form-label">お名前 <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">メールアドレス <span class="text-danger">*</span></label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
    </div>
    <hr>
    <p class="text-muted small">報酬の振込先（任意・後から登録も可）</p>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">銀行名</label>
            <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">支店名</label>
            <input type="text" name="bank_branch" value="{{ old('bank_branch') }}" class="form-control">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">口座種別</label>
            <select name="account_type" class="form-select">
                <option value="">選択</option>
                <option value="普通" @selected(old('account_type') === '普通')>普通</option>
                <option value="当座" @selected(old('account_type') === '当座')>当座</option>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">口座番号</label>
            <input type="text" name="account_number" value="{{ old('account_number') }}" class="form-control">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">口座名義</label>
            <input type="text" name="account_holder" value="{{ old('account_holder') }}" class="form-control">
        </div>
    </div>
    <div>
        <button type="submit" class="btn btn-primary">登録する</button>
    </div>
</form>
@endsection
