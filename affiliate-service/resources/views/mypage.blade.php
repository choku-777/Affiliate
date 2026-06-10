@extends('layouts.app')
@section('title', 'マイページ')

@section('content')
<h1 class="h4 mb-3">{{ $affiliate->name }} 様のマイページ</h1>

@if ($affiliate->isApproved())
    <div class="card card-body mb-3">
        <label class="form-label small text-muted mb-1">あなたの紹介用URL</label>
        <code class="user-select-all">{{ $affiliate->affiliateUrl() }}</code>
    </div>
@else
    <div class="alert alert-info">現在のステータス：{{ $affiliate->statusLabel() }}（承認後にURLが有効になります）</div>
@endif

<div class="row text-center mb-3">
    @foreach (['pending' => '未確定', 'confirmed' => '確定', 'paid' => '支払済'] as $st => $label)
        <div class="col">
            <div class="card card-body">
                <div class="text-muted small">{{ $label }}</div>
                <div class="h5 mb-0">{{ number_format($totals[$st] ?? 0) }} 円</div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header">成果履歴</div>
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
<div class="mt-3">{{ $rewards->links() }}</div>
@endsection
