@extends('layouts.admin')
@section('title', '支払い')

@section('content')
<h1 class="h4 mb-3">支払い（月次締め）</h1>
<p class="text-muted">毎月1日に、前月末までに確定した報酬（最低支払額以上）が支払いリストになります。CSVをダウンロードして振込し（翌月10日まで）、入金後に「入金済みにする」を押してください。</p>

@if ($months->isEmpty())
    <div class="alert alert-info">まだ支払いリストはありません。毎月1日に自動で作成されます。</div>
@else
    {{-- 締め月ごとのまとめ＆CSVダウンロード --}}
    @foreach ($months as $m)
        <div class="card card-body mb-2">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <strong>{{ $m->closing_month }} 分</strong>
                <span class="text-muted small">{{ $m->cnt }}名 ・ {{ number_format($m->total) }}円 ・ 入金済 {{ $m->paid_cnt }}/{{ $m->cnt }}</span>
                <a href="{{ route('admin.payouts.csv', $m->closing_month) }}" class="btn btn-sm btn-outline-primary ms-sm-auto">CSVダウンロード</a>
            </div>
        </div>
    @endforeach
@endif

<div class="card mt-3">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>締め月</th><th>アフィリエイター</th><th class="text-end">金額</th><th class="text-end">件数</th>
                    <th>CSV</th><th>入金</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payouts as $payout)
                    <tr>
                        <td>{{ $payout->closing_month }}</td>
                        <td>@if ($payout->affiliate)#{{ $payout->affiliate->id }} {{ $payout->affiliate->name }}@endif</td>
                        <td class="text-end">{{ number_format($payout->amount) }} 円</td>
                        <td class="text-end">{{ $payout->reward_count }}</td>
                        <td>
                            @if ($payout->isCsvDownloaded())
                                <span class="badge bg-success">DL済</span>
                            @else
                                <span class="badge bg-secondary">未DL</span>
                            @endif
                        </td>
                        <td>
                            @if ($payout->isPaid())
                                <span class="badge bg-primary">入金済</span>
                            @else
                                <span class="badge bg-warning text-dark">未入金</span>
                            @endif
                        </td>
                        <td>
                            @unless ($payout->isPaid())
                                <form method="post" action="{{ route('admin.payouts.paid', $payout) }}" onsubmit="return confirm('入金済みにしますか？対象の報酬が支払済になります。');" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-primary">入金済みにする</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">支払いリストはまだありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $payouts->links() }}</div>
@endsection
