@extends('layouts.admin')
@section('title', '成果・報酬')

@section('content')
<h1 class="h4 mb-3">成果・報酬</h1>

<div class="row text-center mb-3 g-2">
    @foreach (['pending', 'confirmed', 'paid', 'cancelled'] as $st)
        <div class="col-6 col-md">
            <div class="card card-body">
                <div class="text-muted small">{{ $statusLabels[$st] }}</div>
                <div class="h5 mb-0">{{ number_format($totals[$st] ?? 0) }} 円</div>
            </div>
        </div>
    @endforeach
</div>

<form method="get" class="row g-2 mb-3">
    <div class="col-12 col-sm-auto">
        <select name="site_id" class="form-select">
            <option value="">全サイト</option>
            @foreach ($sites as $site)
                <option value="{{ $site->id }}" @selected(($filters['site_id'] ?? '') == $site->id)>{{ $site->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-sm-auto">
        <input type="number" name="affiliate_id" value="{{ $filters['affiliate_id'] ?? '' }}" class="form-control" placeholder="アフィリエイターID">
    </div>
    <div class="col-12 col-sm-auto">
        <select name="status" class="form-select">
            <option value="">すべて</option>
            @foreach ($statusLabels as $value => $label)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-sm-auto"><input type="date" name="start" value="{{ $filters['start'] ?? '' }}" class="form-control"></div>
    <div class="col-12 col-sm-auto"><input type="date" name="end" value="{{ $filters['end'] ?? '' }}" class="form-control"></div>
    <div class="col-12 col-sm-auto"><button class="btn btn-primary">検索</button></div>
</form>

<div class="card">
    <div class="table-responsive">
    <table class="table mb-0">
        <thead>
            <tr>
                <th>ID</th><th>サイト</th><th>アフィリエイター</th><th>注文番号</th>
                <th class="text-end">注文金額</th><th class="text-end">料率</th><th class="text-end">報酬額</th>
                <th>状態</th><th>発生日</th><th>操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rewards as $reward)
                <tr>
                    <td>{{ $reward->id }}</td>
                    <td>{{ optional($reward->site)->name }}</td>
                    <td>
                        @if ($reward->affiliate)
                            #{{ $reward->affiliate->id }} {{ $reward->affiliate->name }}
                        @endif
                    </td>
                    <td>{{ $reward->order_no }}</td>
                    <td class="text-end">{{ number_format($reward->order_total) }}</td>
                    <td class="text-end">{{ $reward->rate_applied }}%</td>
                    <td class="text-end">{{ number_format($reward->reward_amount) }}</td>
                    <td>{{ $reward->statusLabel() }}</td>
                    <td>{{ optional($reward->converted_at)->format('Y-m-d') }}</td>
                    <td>
                        @if (in_array($reward->status, ['pending', 'confirmed']))
                            <form method="post" action="{{ route('admin.rewards.cancel', $reward) }}" onsubmit="return confirm('本当に取り消ししますか？');" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger">取り消し</button>
                            </form>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-muted">該当なし</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-3">{{ $rewards->links() }}</div>
@endsection
