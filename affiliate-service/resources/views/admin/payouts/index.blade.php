@extends('layouts.admin')
@section('title', '支払い')

@section('content')
<h1 class="h4 mb-3">支払い</h1>
<p class="text-muted">確定済みの報酬が最低支払額（{{ number_format($minPayout) }}円）以上のアフィリエイターを、一括で支払えます。「支払う」を押すと振込先を確認できます。閾値は「設定」で変更できます。</p>

<div class="card mb-4">
    <div class="card-header">支払い対象（確定報酬・未払い）</div>
    <div class="table-responsive">
    <table class="table mb-0 align-middle">
        <thead>
            <tr>
                <th>アフィリエイター</th>
                <th class="text-end">確定報酬合計</th>
                <th class="text-end">件数</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($targets as $affiliate)
                <tr>
                    <td>#{{ $affiliate->id }} {{ $affiliate->name }}</td>
                    <td class="text-end">{{ number_format($affiliate->confirmed_total) }} 円</td>
                    <td class="text-end">{{ $affiliate->confirmed_count }}</td>
                    <td class="text-end">
                        @if ($affiliate->confirmed_total >= $minPayout)
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#payModal{{ $affiliate->id }}">支払う</button>
                        @else
                            <span class="text-muted small">繰り越し（あと {{ number_format($minPayout - $affiliate->confirmed_total) }}円）</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted">支払い対象の確定報酬はありません。</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

@foreach ($targets as $affiliate)
    @if ($affiliate->confirmed_total >= $minPayout)
        <div class="modal fade" id="payModal{{ $affiliate->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">支払い確認</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3"><strong>{{ $affiliate->name }}</strong> さんへ <strong class="text-primary">{{ number_format($affiliate->confirmed_total) }}円</strong>（確定報酬 {{ $affiliate->confirmed_count }}件）を支払います。</p>
                        <h6 class="text-muted small mb-2">振込先口座</h6>
                        @if ($affiliate->bank_name || $affiliate->account_number)
                            <table class="table table-sm mb-0">
                                <tr><th style="width:110px;">銀行名</th><td>{{ $affiliate->bank_name ?: '—' }}</td></tr>
                                <tr><th>支店名</th><td>{{ $affiliate->bank_branch ?: '—' }}</td></tr>
                                <tr><th>口座種別</th><td>{{ $affiliate->account_type ?: '—' }}</td></tr>
                                <tr><th>口座番号</th><td>{{ $affiliate->account_number ?: '—' }}</td></tr>
                                <tr><th>口座名義</th><td>{{ $affiliate->account_holder ?: '—' }}</td></tr>
                            </table>
                        @else
                            <div class="alert alert-warning mb-0">振込先口座が未登録です。本人へ登録を依頼してから支払うことをおすすめします。</div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <form method="post" action="{{ route('admin.payouts.pay', $affiliate) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">支払い実行</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

<div class="card">
    <div class="card-header">支払い履歴</div>
    <div class="table-responsive">
    <table class="table mb-0">
        <thead>
            <tr>
                <th>支払日時</th>
                <th>アフィリエイター</th>
                <th class="text-end">金額</th>
                <th class="text-end">件数</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($payouts as $payout)
                <tr>
                    <td>{{ $payout->paid_at->format('Y-m-d H:i') }}</td>
                    <td>@if ($payout->affiliate)#{{ $payout->affiliate->id }} {{ $payout->affiliate->name }}@endif</td>
                    <td class="text-end">{{ number_format($payout->amount) }} 円</td>
                    <td class="text-end">{{ $payout->reward_count }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted">まだ支払い履歴はありません。</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
