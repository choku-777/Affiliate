@extends('layouts.admin')
@section('title', 'アフィリエイター詳細')

@section('content')
<h1 class="h4 mb-3">アフィリエイター詳細</h1>

<div class="card card-body mb-3">
    <table class="table mb-0">
        <tr><th style="width: 200px;">ID</th><td>{{ $affiliate->id }}</td></tr>
        <tr><th>氏名</th><td>{{ $affiliate->name }}
            @if ($affiliate->last_name_kana || $affiliate->first_name_kana)
                <span class="text-muted small">（{{ $affiliate->last_name_kana }} {{ $affiliate->first_name_kana }}）</span>
            @endif
        </td></tr>
        <tr><th>メール</th><td>{{ $affiliate->email }}</td></tr>
        <tr><th>電話番号</th><td>{{ $affiliate->phone }}</td></tr>
        <tr><th>生年月日</th><td>{{ optional($affiliate->birth_date)->format('Y-m-d') }}</td></tr>
        <tr><th>性別</th><td>{{ \App\Models\Affiliate::$genderLabels[$affiliate->gender] ?? '' }}</td></tr>
        <tr><th>ステータス</th><td>{{ $affiliate->statusLabel() }}</td></tr>
        <tr><th>コード</th><td><code>{{ $affiliate->affiliate_code }}</code></td></tr>
        <tr>
            <th>発行URL</th>
            <td>
                @if ($affiliate->isApproved())
                    <code class="user-select-all">{{ $affiliate->affiliateUrl() }}</code>
                @else
                    <span class="text-muted">承認後に有効（{{ $affiliate->affiliateUrl() }}）</span>
                @endif
            </td>
        </tr>
        <tr><th>住所</th><td>
            @if ($affiliate->postal_code)〒{{ $affiliate->postal_code }}<br>@endif
            {{ $affiliate->prefecture }}{{ $affiliate->city }}{{ $affiliate->address1 }} {{ $affiliate->address2 }}
        </td></tr>
        <tr><th>振込先</th><td>{{ $affiliate->bank_name }} {{ $affiliate->bank_branch }} {{ $affiliate->account_type }} {{ $affiliate->account_number }} {{ $affiliate->account_holder }}</td></tr>
        <tr><th>申請日</th><td>{{ $affiliate->created_at->format('Y-m-d H:i') }}</td></tr>
        <tr><th>承認日</th><td>{{ optional($affiliate->approved_at)->format('Y-m-d H:i') }}</td></tr>
    </table>
</div>

<div class="d-flex gap-2">
    @if (!$affiliate->isApproved())
        <form method="post" action="{{ route('admin.affiliates.approve', $affiliate) }}">
            @csrf
            <button class="btn btn-primary">承認する</button>
        </form>
    @endif
    @if ($affiliate->status !== \App\Models\Affiliate::STATUS_REJECTED)
        <form method="post" action="{{ route('admin.affiliates.reject', $affiliate) }}" onsubmit="return confirm('却下しますか？');">
            @csrf
            <button class="btn btn-outline-danger">却下する</button>
        </form>
    @endif
    @if ($affiliate->isApproved())
        <form method="post" action="{{ route('admin.affiliates.suspend', $affiliate) }}" onsubmit="return confirm('停止しますか？');">
            @csrf
            <button class="btn btn-outline-secondary">停止する</button>
        </form>
    @endif
    <a href="{{ route('admin.affiliates.index') }}" class="btn btn-light">一覧へ</a>
</div>
@endsection
