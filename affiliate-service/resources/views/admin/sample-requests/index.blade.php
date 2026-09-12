@extends('layouts.admin')
@section('title', 'サンプル発送')

@section('content')
<h1 class="h4 mb-3">サンプル発送</h1>

{{-- 状態での絞り込み。初期表示は「未発送」なので、送るべき人がひと目で分かる --}}
<ul class="nav nav-pills mb-3 flex-wrap gap-1">
    @foreach ($statusLabels as $key => $label)
        <li class="nav-item">
            <a class="nav-link {{ $status === $key ? 'active' : '' }}" href="{{ route('admin.sample-requests.index', ['status' => $key]) }}">
                {{ $label }}
                <span class="badge {{ $status === $key ? 'bg-light text-dark' : 'bg-secondary' }}">{{ $counts[$key] ?? 0 }}</span>
            </a>
        </li>
    @endforeach
    <li class="nav-item">
        <a class="nav-link {{ $status === 'all' ? 'active' : '' }}" href="{{ route('admin.sample-requests.index', ['status' => 'all']) }}">すべて</a>
    </li>
</ul>

<div class="card mb-4">
    <div class="card-header">発送の手順</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-lg-5">
                <div class="border rounded p-3 h-100">
                    <div class="fw-bold mb-2">① 送り状を作る</div>
                    <p class="small text-muted">
                        未発送の申し込みをまとめてCSVにします。<br>
                        B2クラウドに取り込んで送り状を発行し、商品を発送してください。
                    </p>
                    <a href="{{ route('admin.sample-requests.csv') }}" class="btn btn-primary">ネコポス用CSVを出力</a>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="border rounded p-3 h-100">
                    <div class="fw-bold mb-2">② 発送後、伝票番号を取り込む</div>
                    <p class="small text-muted">
                        B2クラウドの「発行済データ」をそのまま選んでください。<br>
                        サンプル以外（通常のご注文）の行は自動で除かれます。
                    </p>
                    <form method="post" action="{{ route('admin.sample-requests.import') }}" enctype="multipart/form-data"
                          onsubmit="return confirmImport(this);">
                        @csrf
                        <div class="row g-2 align-items-center">
                            <div class="col-sm-7">
                                <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                            </div>
                            <div class="col-sm-5">
                                <button type="submit" class="btn btn-outline-primary w-100">発送データを取り込む</button>
                            </div>
                        </div>
                        <div class="form-check mt-2">
                            <input type="checkbox" name="send_mail" value="1" id="send_mail" class="form-check-input" checked>
                            <label class="form-check-label small" for="send_mail">
                                アンバサダーへ発送完了メールを送る
                            </label>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width: 92px;">申込日</th>
                    <th style="width: 170px;">アンバサダー</th>
                    <th>送付先</th>
                    <th style="width: 96px;">状態</th>
                    <th style="width: 136px;">伝票番号</th>
                    <th style="width: 210px;">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $r)
                    <tr>
                        <td class="small text-nowrap">{{ optional($r->requested_at)->format('Y-m-d') }}</td>
                        <td>
                            <div>{{ $r->affiliate?->name }}</div>
                            <div class="text-muted small">{{ $r->product_name }}</div>
                        </td>
                        <td class="small">
                            〒{{ $r->postal_code }}
                            {{ $r->fullAddress() }}{{ $r->address2 ? ' '.$r->address2 : '' }}<br>
                            {{ $r->recipient_name }} 様 / {{ $r->phone }}
                            @if ($r->note)
                                <div class="text-muted mt-1">要望: {{ $r->note }}</div>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            @if ($r->status === \App\Models\SampleRequest::STATUS_SHIPPED)
                                <span class="badge bg-success">発送済</span>
                                <div class="text-muted small mt-1">{{ optional($r->shipped_at)->format('Y-m-d') }}</div>
                            @elseif ($r->status === \App\Models\SampleRequest::STATUS_CSV_EXPORTED)
                                <span class="badge bg-info text-dark">CSV出力済</span>
                            @elseif ($r->status === \App\Models\SampleRequest::STATUS_CANCELLED)
                                <span class="badge bg-secondary">取消</span>
                            @else
                                <span class="badge bg-warning text-dark">未発送</span>
                            @endif
                        </td>
                        <td class="small text-nowrap">
                            @if ($r->tracking_number)
                                {{ $r->formattedTrackingNumber() }}
                                <div><a href="{{ $r->trackingUrl() }}" target="_blank" rel="noopener">追跡</a></div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if ($r->status !== \App\Models\SampleRequest::STATUS_SHIPPED && $r->status !== \App\Models\SampleRequest::STATUS_CANCELLED)
                                <form method="post" action="{{ route('admin.sample-requests.tracking', $r) }}" class="d-flex gap-1 mb-1"
                                      onsubmit="return confirmTracking();">
                                    @csrf
                                    <input type="text" name="tracking_number" class="form-control form-control-sm" placeholder="伝票番号" required>
                                    <button type="submit" class="btn btn-sm btn-primary text-nowrap">登録</button>
                                </form>
                                <form method="post" action="{{ route('admin.sample-requests.cancel', $r) }}"
                                      onsubmit="return confirm('この申し込みを取り消します。よろしいですか？');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">取消</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">該当する申し込みはありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $requests->links() }}</div>
@endsection

@section('scripts')
<script>
// 取り込みは伝票番号の登録とメール送信が同時に起きるため、必ず確認してから実行する
function confirmImport(form) {
    var sendMail = form.querySelector('input[name="send_mail"]').checked;
    var message = '発送データを取り込みます。\n\n'
        + '・伝票番号を登録します\n'
        + '・該当の申し込みを「発送済」にします\n';
    message += sendMail
        ? '・アンバサダーへ発送完了メールを送信します\n\n※メールは送信後に取り消せません。\n\n実行してよろしいですか？'
        : '・メールは送信しません\n\n実行してよろしいですか？';
    return confirm(message);
}

// 個別登録もメールが飛ぶので同様に確認する
function confirmTracking() {
    return confirm('伝票番号を登録して「発送済」にします。\n'
        + 'アンバサダーへ発送完了メールを送信します。\n\n'
        + '※メールは送信後に取り消せません。\n\n実行してよろしいですか？');
}
</script>
@endsection
