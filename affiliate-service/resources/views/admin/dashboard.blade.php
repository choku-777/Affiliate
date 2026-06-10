@extends('layouts.admin')
@section('title', 'ダッシュボード')

@section('content')
<h1 class="h4 mb-4">ダッシュボード</h1>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card card-body">
            <div class="text-muted small">承認待ち</div>
            <div class="h3 mb-0">{{ $pendingAffiliates }}</div>
            <a href="{{ route('admin.affiliates.index', ['status' => 'pending']) }}" class="small">確認する</a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-body">
            <div class="text-muted small">承認済みアフィリエイター</div>
            <div class="h3 mb-0">{{ $approvedAffiliates }}</div>
        </div>
    </div>
</div>

<h2 class="h6">報酬サマリー</h2>
<div class="row">
    @foreach (['pending', 'confirmed', 'paid', 'cancelled'] as $st)
        <div class="col">
            <div class="card card-body">
                <div class="text-muted small">{{ $statusLabels[$st] }}</div>
                <div class="h5 mb-0">{{ number_format(optional($totals->get($st))->total ?? 0) }} 円</div>
                <div class="small text-muted">{{ optional($totals->get($st))->cnt ?? 0 }} 件</div>
            </div>
        </div>
    @endforeach
</div>
@endsection
