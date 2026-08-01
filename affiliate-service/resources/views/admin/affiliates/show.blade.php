@extends('layouts.admin')
@section('title', 'アンバサダー詳細')

@section('content')
<h1 class="h4 mb-3">アンバサダー詳細</h1>

<div class="card card-body mb-3">
    <div class="table-responsive">
    <table class="table mb-0">
        <tr><th style="min-width: 110px;">ID</th><td>{{ $affiliate->id }}</td></tr>
        <tr><th>氏名</th><td>{{ $affiliate->name }}
            @if ($affiliate->last_name_kana || $affiliate->first_name_kana)
                <span class="text-muted small">（{{ $affiliate->last_name_kana }} {{ $affiliate->first_name_kana }}）</span>
            @endif
        </td></tr>
        <tr><th>メール</th><td>{{ $affiliate->email }}</td></tr>
        <tr><th>電話番号</th><td>{{ $affiliate->phone }}</td></tr>
        <tr><th>主なSNS</th><td>{{ $affiliate->sns }}@if ($affiliate->sns_account)（{{ $affiliate->sns_account }}）@endif</td></tr>
        <tr><th>生年月日</th><td>{{ optional($affiliate->birth_date)->format('Y-m-d') }}</td></tr>
        <tr><th>性別</th><td>{{ \App\Models\Affiliate::$genderLabels[$affiliate->gender] ?? '' }}</td></tr>
        <tr><th>ステータス</th><td>{{ $affiliate->statusLabel() }}</td></tr>
        <tr><th>コード</th><td><code>{{ $affiliate->affiliate_code }}</code></td></tr>
        <tr>
            <th>発行URL</th>
            <td>
                @foreach ($affiliate->affiliateUrls() as $row)
                    <div class="mb-1">
                        <span class="small text-muted">{{ $row['site']->name }}：</span>
                        @if ($affiliate->isApproved())
                            <code class="user-select-all" style="word-break: break-all;">{{ $row['url'] }}</code>
                        @else
                            <span class="text-muted small">承認後に有効（<code style="word-break: break-all;">{{ $row['url'] }}</code>）</span>
                        @endif
                    </div>
                @endforeach
            </td>
        </tr>
        <tr><th>住所</th><td>
            @if ($affiliate->postal_code)〒{{ $affiliate->postal_code }}<br>@endif
            {{ $affiliate->prefecture }}{{ $affiliate->city }}{{ $affiliate->address1 }} {{ $affiliate->address2 }}
        </td></tr>
        <tr><th>振込先</th><td>{{ $affiliate->bank_name }} {{ $affiliate->bank_branch }} {{ $affiliate->account_type }} {{ $affiliate->account_number }} {{ $affiliate->account_holder }}</td></tr>
        <tr><th>申請日</th><td>{{ $affiliate->created_at->format('Y-m-d H:i') }}</td></tr>
        <tr><th>承認日</th><td>{{ optional($affiliate->approved_at)->format('Y-m-d H:i') }}</td></tr>
        <tr>
            <th>サンプル送付</th>
            <td>
                @if ($affiliate->hasSampleSent())
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="badge bg-success">送付済み</span>
                        <span>{{ $affiliate->sample_sent_at->format('Y-m-d H:i') }}</span>
                        @if ($affiliate->sample_sent_by)
                            <span class="text-muted small">記録：{{ $affiliate->sample_sent_by }}</span>
                        @endif
                        <form method="post" action="{{ route('admin.affiliates.sample.unmark', $affiliate) }}" onsubmit="return confirm('サンプル送付の記録を取り消しますか？');">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary">取り消す</button>
                        </form>
                    </div>
                @else
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="badge bg-secondary">未送付</span>
                        <form method="post" action="{{ route('admin.affiliates.sample.mark', $affiliate) }}">
                            @csrf
                            <button class="btn btn-sm btn-primary">送付済みにする</button>
                        </form>
                    </div>
                @endif
            </td>
        </tr>
    </table>
    </div>
</div>

<div class="d-flex gap-2 flex-wrap">
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

<div class="card mt-4">
    <div class="card-body">
        <h2 class="h5 mb-3">メモ・備考</h2>

        <form method="post" action="{{ route('admin.affiliates.notes.store', $affiliate) }}" class="mb-3">
            @csrf
            <div class="mb-2">
                <textarea name="body" rows="3" maxlength="2000"
                          class="form-control @error('body') is-invalid @enderror"
                          placeholder="サンプル発送の内容、電話でのやりとり、注意点など">{{ old('body') }}</textarea>
                @error('body')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button class="btn btn-primary">追加する</button>
            <span class="text-muted small ms-2">追加すると日時と担当者が記録されます（2000文字まで）</span>
        </form>

        @forelse ($notes as $note)
            <div class="border-top py-3">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <span class="text-muted small">{{ $note->created_at->format('Y-m-d H:i') }}</span>
                    <span class="badge bg-secondary">{{ $note->authorLabel() }}</span>
                    <form method="post" action="{{ route('admin.affiliates.notes.destroy', [$affiliate, $note]) }}"
                          onsubmit="return confirm('このメモを削除しますか？');" class="ms-auto">
                        @csrf
                        @method('delete')
                        <button class="btn btn-sm btn-outline-danger">削除</button>
                    </form>
                </div>
                <div style="white-space: pre-wrap;">{{ $note->body }}</div>
            </div>
        @empty
            <p class="text-muted mb-0">まだメモはありません。</p>
        @endforelse
    </div>
</div>
@endsection
