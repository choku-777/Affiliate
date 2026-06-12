@extends('layouts.admin')
@section('title', '設定')

@section('content')
<h1 class="h4 mb-3">設定</h1>

@if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="post" action="{{ route('admin.settings.update') }}" class="card card-body" style="max-width: 640px;">
    @csrf
    @method('PUT')

    <h2 class="h6 text-muted border-bottom pb-2 mb-3">サイト別の報酬料率（%）</h2>
    @foreach ($sites as $site)
        <div class="mb-3">
            <label class="form-label">
                {{ $site->name }}
                <span class="text-muted small">（{{ $site->code }}）</span>
                @if ($site->is_default)<span class="badge bg-secondary">既定</span>@endif
            </label>
            <input type="number" step="0.01" min="0" max="100" name="site_rate[{{ $site->id }}]" value="{{ old('site_rate.'.$site->id, $site->commission_rate) }}" class="form-control">
        </div>
    @endforeach

    <h2 class="h6 text-muted border-bottom pb-2 mb-3 mt-2">共通設定（全サイト共通）</h2>
    <div class="mb-3">
        <label class="form-label">成果確定までの猶予日数（日）</label>
        <input type="number" name="confirm_after_days" value="{{ old('confirm_after_days', $setting->confirm_after_days) }}" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">最低支払額（円）</label>
        <input type="number" name="min_payout_amount" value="{{ old('min_payout_amount', $setting->min_payout_amount) }}" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">クッキー有効期間（日）<span class="text-muted small">※実際の付与はEC-CUBE側 .env で設定</span></label>
        <input type="number" name="cookie_lifetime_days" value="{{ old('cookie_lifetime_days', $setting->cookie_lifetime_days) }}" class="form-control">
    </div>

    <div><button class="btn btn-primary">保存</button></div>
</form>
@endsection
