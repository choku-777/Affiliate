@extends('layouts.admin')
@section('title', 'アンバサダー')

@section('content')
<h1 class="h4 mb-3">アンバサダー</h1>

<form method="get" class="row g-2 mb-3">
    <div class="col-12 col-sm-auto">
        <input type="text" name="keyword" value="{{ $filters['keyword'] ?? '' }}" class="form-control" placeholder="氏名・メール">
    </div>
    <div class="col-12 col-sm-auto">
        <select name="status" class="form-select">
            <option value="">すべてのステータス</option>
            @foreach ($statusLabels as $value => $label)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-sm-auto"><button class="btn btn-primary">検索</button></div>
</form>

<div class="card">
    <div class="table-responsive">
    <table class="table mb-0">
        <thead>
            <tr><th>ID</th><th>氏名</th><th>メール</th><th>コード</th><th>ステータス</th><th>申請日</th><th></th></tr>
        </thead>
        <tbody>
            @forelse ($affiliates as $affiliate)
                <tr>
                    <td>{{ $affiliate->id }}</td>
                    <td>{{ $affiliate->name }}</td>
                    <td>{{ $affiliate->email }}</td>
                    <td><code>{{ $affiliate->affiliate_code }}</code></td>
                    <td>{{ $affiliate->statusLabel() }}</td>
                    <td>{{ $affiliate->created_at->format('Y-m-d') }}</td>
                    <td><a href="{{ route('admin.affiliates.show', $affiliate) }}" class="btn btn-sm btn-outline-primary">詳細</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-muted">該当なし</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-3">{{ $affiliates->links() }}</div>
@endsection
