@extends('layouts.app')
@section('title', 'マイページ')

@section('content')
<h1 class="h4 mb-3">{{ $affiliate->name }} 様のマイページ</h1>

@if ($announcements->isNotEmpty())
    <div class="card mb-3">
        <div class="card-header">📢 お知らせ</div>
        <div class="list-group list-group-flush">
            @foreach ($announcements as $announcement)
                <div class="list-group-item">
                    <div class="fw-bold">{{ $announcement->title }}</div>
                    <div class="small mt-1">{!! $announcement->bodyHtml() !!}</div>
                    <div class="text-muted small mt-2">{{ $announcement->updated_at->format('Y年n月j日') }}</div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="mb-3">
    <a href="{{ route('inquiry.create') }}" class="btn btn-outline-primary btn-sm">お問い合わせ</a>
</div>

@if ($affiliate->isApproved())
    <div class="card card-body mb-3">
        <label class="form-label small text-muted mb-2">あなたの紹介用URL（サイトごと）</label>
        @foreach ($affiliate->affiliateUrls() as $row)
            <div class="mb-2">
                <div class="small fw-bold">{{ $row['site']->name }}</div>
                <code class="user-select-all" style="word-break: break-all;">{{ $row['url'] }}</code>
            </div>
        @endforeach
    </div>
@else
    <div class="alert alert-info">現在のステータス：{{ $affiliate->statusLabel() }}（承認後にURLが有効になります）</div>
@endif

@if ($affiliate->isApproved())
    @include('partials.ambassador-benefits')

    <div class="card mb-3">
        <div class="card-header">📦 サンプル商品のお申し込み</div>
        <div class="card-body">
            @if ($sampleRequest)
                @if ($sampleRequest->status === \App\Models\SampleRequest::STATUS_SHIPPED)
                    <div class="alert alert-success mb-2">
                        発送済みです（{{ optional($sampleRequest->shipped_at)->format('Y年n月j日') }}）
                    </div>
                    @if ($sampleRequest->tracking_number)
                        <div class="small">
                            伝票番号：{{ $sampleRequest->formattedTrackingNumber() }}<br>
                            <a href="{{ $sampleRequest->trackingUrl() }}" target="_blank" rel="noopener">配送状況を確認する</a>
                        </div>
                    @endif
                @else
                    <div class="alert alert-info mb-0">
                        お申し込みを受け付けました。発送までもうしばらくお待ちください。
                    </div>
                @endif

                {{-- SNS投稿のお願い（投稿はサンプルの条件） --}}
                @php
                    $snsLatest = $sampleRequest->snsPosts->first();
                    $snsDeadline = $sampleRequest->snsDeadline((int) $setting->sns_post_deadline_days);
                @endphp
                <hr>
                @include('partials.sns-post-rules', ['setting' => $setting, 'deadline' => $snsDeadline])
                <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
                    <a href="{{ route('affiliate.sns-posts.index') }}" class="btn btn-outline-primary">投稿したURLを申告する</a>
                    @if ($snsLatest)
                        <span class="small">
                            最新の申告：{{ $snsLatest->platformLabel() }}
                            @if ($snsLatest->status === \App\Models\SnsPost::STATUS_APPROVED)
                                <span class="badge bg-success">掲載中</span>
                            @elseif ($snsLatest->status === \App\Models\SnsPost::STATUS_PENDING)
                                <span class="badge bg-info text-dark">確認中</span>
                            @elseif ($snsLatest->status === \App\Models\SnsPost::STATUS_CHECKED)
                                <span class="badge bg-primary">確認済み</span>
                            @else
                                <span class="badge bg-secondary">非掲載</span>
                            @endif
                        </span>
                    @endif
                </div>
            @elseif (! $setting->sample_request_enabled)
                <div class="alert alert-secondary mb-0">
                    現在、サンプルのお申し込みを停止しています。再開までお待ちください。
                </div>
            @else
                <p class="mb-2">
                    <strong>{{ $setting->sample_product_name }}</strong> を、お一人さま1回に限りお試しいただけます。
                    @if ($setting->sample_product_url)
                        <br><a href="{{ $setting->sample_product_url }}" target="_blank" rel="noopener">商品の詳細を見る</a>
                    @endif
                </p>
                <a href="{{ route('affiliate.sample.create') }}" class="btn btn-primary">サンプルを申し込む</a>
                <div class="small text-muted mt-3">
                    ※サンプルのご提供は<strong>ペットフードのみ</strong>とさせていただいております。<br>
                    馬刺しセットは配送方法を検討中のため、現在お申し込みいただけません。
                </div>
            @endif
        </div>
    </div>
@endif

<div class="row text-center mb-3 g-2">
    @foreach (['pending' => '未確定', 'confirmed' => '確定', 'paid' => '支払済'] as $st => $label)
        <div class="col-4">
            <div class="card card-body">
                <div class="text-muted small">{{ $label }}</div>
                <div class="h5 mb-0">{{ number_format($totals[$st] ?? 0) }} 円</div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header">成果履歴</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr><th>発生日</th><th>注文番号</th><th class="text-end">報酬額</th><th>状態</th></tr>
            </thead>
            <tbody>
                @forelse ($rewards as $reward)
                    <tr>
                        <td>{{ optional($reward->converted_at)->format('Y-m-d') }}</td>
                        <td>{{ $reward->order_no }}</td>
                        <td class="text-end">{{ number_format($reward->reward_amount) }} 円</td>
                        <td>{{ $statusLabels[$reward->status] ?? $reward->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">まだ成果はありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $rewards->links() }}</div>

<div class="card mt-4">
    <div class="card-header">支払い履歴</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr><th>支払日</th><th class="text-end">金額</th><th class="text-end">件数</th></tr>
            </thead>
            <tbody>
                @forelse ($payouts as $payout)
                    <tr>
                        <td>{{ $payout->paid_at->format('Y-m-d') }}</td>
                        <td class="text-end">{{ number_format($payout->amount) }} 円</td>
                        <td class="text-end">{{ $payout->reward_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-muted">まだお支払いはありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
