@extends('layouts.app')
@section('title', 'お問い合わせ')

@section('content')
<h1 class="h4 mb-3">お問い合わせ</h1>
@if ($affiliate)
    <p class="text-muted">ご登録のお名前・メールアドレスを添えて管理者へ送信します。返信は登録メールアドレス宛に届きます。</p>
@else
    <p class="text-muted">お名前・メールアドレスをご記入のうえ送信してください。返信はご記入のメールアドレス宛に届きます。</p>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="post" action="{{ route('inquiry.store') }}" class="card card-body">
    @csrf
    <div class="mb-3">
        <label class="form-label">お名前 @unless($affiliate)<span class="text-danger">*</span>@endunless</label>
        @if ($affiliate)
            <input type="text" class="form-control" value="{{ $affiliate->name }}" disabled>
        @else
            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
        @endif
    </div>
    <div class="mb-3">
        <label class="form-label">メールアドレス @unless($affiliate)<span class="text-danger">*</span>@endunless</label>
        @if ($affiliate)
            <input type="email" class="form-control" value="{{ $affiliate->email }}" disabled>
        @else
            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
        @endif
    </div>
    <div class="mb-3">
        <label class="form-label">件名 <span class="text-danger">*</span></label>
        <input type="text" name="subject" value="{{ old('subject') }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">お問い合わせ内容 <span class="text-danger">*</span></label>
        <textarea name="body" rows="6" class="form-control" required>{{ old('body') }}</textarea>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">送信する</button>
        @if ($affiliate)
            <a href="{{ route('affiliate.mypage') }}" class="btn btn-outline-secondary">マイページに戻る</a>
        @else
            <a href="{{ route('register.create') }}" class="btn btn-outline-secondary">登録画面に戻る</a>
        @endif
    </div>
</form>
@endsection
