@extends('layouts.admin')
@section('title', $announcement->exists ? 'お知らせの編集' : 'お知らせの作成')

@section('content')
<div style="max-width: 800px;">
<h1 class="h4 mb-3">{{ $announcement->exists ? 'お知らせの編集' : 'お知らせの作成' }}</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="post" action="{{ $announcement->exists ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}">
    @csrf
    @if ($announcement->exists)
        @method('PUT')
    @endif

    <div class="mb-3">
        <label class="form-label">タイトル <span class="text-danger">*</span></label>
        <input type="text" name="title" value="{{ old('title', $announcement->title) }}" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">本文 <span class="text-danger">*</span></label>
        <textarea name="body" rows="8" class="form-control" required>{{ old('body', $announcement->body) }}</textarea>
        <div class="form-text">改行はそのまま表示されます。URLを書くと自動でリンクになります。</div>
    </div>

    <div class="row">
        <div class="col-sm-4 mb-3">
            <label class="form-label">表示順</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $announcement->sort_order ?? 0) }}" class="form-control" min="0" max="9999">
            <div class="form-text">小さいほど上に表示されます。</div>
        </div>
    </div>

    <div class="form-check mb-4">
        <input type="checkbox" name="is_published" value="1" id="is_published" class="form-check-input"
               {{ old('is_published', $announcement->is_published) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_published">
            公開する（チェックを外すとマイページに表示されません）
        </label>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">保存する</button>
        <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary">戻る</a>
    </div>
</form>
</div>
@endsection
