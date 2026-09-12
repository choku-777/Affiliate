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

    <h2 class="h6 text-muted border-bottom pb-2 mb-3 mt-4">サンプル発送</h2>

    <div class="mb-3 form-check">
        <input type="checkbox" name="sample_request_enabled" value="1" id="sample_request_enabled" class="form-check-input"
               {{ old('sample_request_enabled', $setting->sample_request_enabled) ? 'checked' : '' }}>
        <label class="form-check-label" for="sample_request_enabled">
            サンプルの申し込みを受け付ける
        </label>
        <div class="form-text">在庫切れのときはチェックを外すと、マイページから申し込めなくなります。</div>
    </div>

    <div class="mb-3">
        <label class="form-label">サンプル商品名（マイページ表示用）</label>
        <input type="text" name="sample_product_name" value="{{ old('sample_product_name', $setting->sample_product_name) }}" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">サンプル商品ページのURL</label>
        <input type="url" name="sample_product_url" value="{{ old('sample_product_url', $setting->sample_product_url) }}" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">送り状の品名</label>
        <input type="text" name="sample_invoice_item_name" value="{{ old('sample_invoice_item_name', $setting->sample_invoice_item_name) }}" class="form-control">
        <div class="form-text">ヤマトの送り状に印字される品名です。</div>
    </div>

    <div class="mb-3">
        <label class="form-label">サンプル申し込み通知用 Discord Webhook URL</label>
        <input type="url" name="discord_sample_webhook_url" value="{{ old('discord_sample_webhook_url', $setting->discord_sample_webhook_url) }}" class="form-control" placeholder="https://discord.com/api/webhooks/...">
        <div class="form-text">
            サンプルの申し込みがあったときに通知するDiscordチャンネルのURLです。空にすると通知しません。<br>
            このURLを知っている人は誰でもそのチャンネルに書き込めるため、取り扱いにご注意ください。
        </div>
    </div>

    <h2 class="h6 text-muted border-bottom pb-2 mb-3 mt-4">SNS投稿</h2>

    <div class="mb-3">
        <label class="form-label">ハッシュタグ</label>
        <input type="text" name="sns_hashtag" value="{{ old('sns_hashtag', $setting->sns_hashtag) }}" class="form-control" style="max-width: 320px;">
        <div class="form-text">「#PR」はこれとは別に自動で案内されます。</div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">X の公式アカウント</label>
            <input type="text" name="sns_account_x" value="{{ old('sns_account_x', $setting->sns_account_x) }}" class="form-control" placeholder="@umashippo">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Instagram の公式アカウント</label>
            <input type="text" name="sns_account_instagram" value="{{ old('sns_account_instagram', $setting->sns_account_instagram) }}" class="form-control" placeholder="@umashippo">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">TikTok の公式アカウント</label>
            <input type="text" name="sns_account_tiktok" value="{{ old('sns_account_tiktok', $setting->sns_account_tiktok) }}" class="form-control" placeholder="@umashippo">
        </div>
    </div>
    <div class="form-text mb-3">公式アカウントは空のままでも大丈夫です。入力すると「投稿のお願い」にメンション先として表示されます。</div>

    <div class="mb-3">
        <label class="form-label">投稿期限（発送から何日以内）</label>
        <input type="number" name="sns_post_deadline_days" value="{{ old('sns_post_deadline_days', $setting->sns_post_deadline_days) }}" class="form-control" style="max-width: 160px;" min="1" max="90">
        <div class="form-text">この日数を過ぎても申告がない人は、サンプル発送画面で「期限切れ」と表示されます。</div>
    </div>

    <div><button class="btn btn-primary">保存</button></div>
</form>
@endsection
