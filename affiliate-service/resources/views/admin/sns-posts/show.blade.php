@extends('layouts.admin')
@section('title', 'SNS投稿の確認')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">SNS投稿の確認</h1>
    <a href="{{ route('admin.sns-posts.index') }}" class="btn btn-outline-secondary btn-sm">一覧へ戻る</a>
</div>

<div class="row g-3">
    {{-- 左：投稿の実物 --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>投稿のプレビュー（{{ $post->platformLabel() }}）</span>
                <a href="{{ $post->normalized_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">投稿を新しいタブで開く</a>
            </div>
            <div class="card-body">
                <div id="embed-area">{!! $post->embedHtml() !!}</div>
                <div id="embed-fallback" class="alert alert-warning small mt-2" hidden>
                    <div class="fw-bold mb-1">埋め込みが表示できませんでした</div>
                    <ul class="mb-2 ps-3">
                        <li>ブラウザの広告ブロック（Brave の Shields など）が {{ $post->platformLabel() }} の埋め込みを止めている</li>
                        <li>非公開アカウント、または投稿が削除されている</li>
                    </ul>
                    上の「<strong>投稿を新しいタブで開く</strong>」から直接ご確認ください。内容を見て #PR があれば、そのまま右側で確認・承認できます。
                </div>
            </div>
        </div>
    </div>

    {{-- 右：情報と操作 --}}
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header">申告内容</div>
            <div class="card-body small">
                <table class="table table-sm mb-0">
                    <tr><th style="width: 110px;">アンバサダー</th><td>{{ $post->affiliate?->name }}</td></tr>
                    <tr><th>SNS</th><td>{{ $post->platformLabel() }}</td></tr>
                    <tr><th>URL</th><td><a href="{{ $post->normalized_url }}" target="_blank" rel="noopener" style="word-break: break-all;">{{ $post->normalized_url }}</a></td></tr>
                    <tr><th>申告日時</th><td>{{ $post->created_at->format('Y-m-d H:i') }}</td></tr>
                    @if ($post->note)
                        <tr><th>ひとこと</th><td>{{ $post->note }}</td></tr>
                    @endif
                    <tr><th>状態</th><td>{{ $post->statusLabel() }}
                        @if ($post->reject_reason)<span class="text-muted">（{{ $post->reject_reason }}）</span>@endif
                    </td></tr>
                    @if ($post->approved_at)
                        <tr><th>承認</th><td>{{ $post->approved_at->format('Y-m-d H:i') }} / {{ $post->approved_by }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">① #PR の確認</div>
            <div class="card-body">
                <form method="post" action="{{ route('admin.sns-posts.pr-check', $post) }}">
                    @csrf
                    <input type="hidden" name="checked" value="{{ $post->isPrChecked() ? 0 : 1 }}">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="pr_checked" {{ $post->isPrChecked() ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="form-check-label" for="pr_checked">
                            投稿に <strong>#PR</strong> の記載があることを確認した
                        </label>
                    </div>
                    @if ($post->isPrChecked())
                        <div class="text-muted small mt-1">{{ $post->pr_checked_at->format('Y-m-d H:i') }} / {{ $post->pr_checked_by }}</div>
                    @else
                        <div class="text-danger small mt-1">#PR が無い場合は承認せず、却下（理由：#PRなし）してください。</div>
                    @endif
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">② 確認 / 掲載 / 却下</div>
            <div class="card-body">
                @php $st = $post->status; @endphp

                @if ($st === \App\Models\SnsPost::STATUS_APPROVED)
                    <div class="alert alert-success small">現在「掲載中」です。公式サイトの掲載対象になっています。</div>
                    <form method="post" action="{{ route('admin.sns-posts.hide', $post) }}" class="mb-3"
                          onsubmit="return confirm('掲載を停止します。よろしいですか？');">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">掲載を停止する</button>
                    </form>
                @else
                    @if ($st === \App\Models\SnsPost::STATUS_CHECKED)
                        <div class="alert alert-primary small">「確認済み」です。掲載はまだされていません。</div>
                    @endif
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @if ($st !== \App\Models\SnsPost::STATUS_CHECKED)
                            <form method="post" action="{{ route('admin.sns-posts.check', $post) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary" {{ $post->isPrChecked() ? '' : 'disabled' }}>確認済みにする（掲載は保留）</button>
                            </form>
                        @endif
                        <form method="post" action="{{ route('admin.sns-posts.approve', $post) }}"
                              onsubmit="return confirm('この投稿を「掲載中」にし、公式サイトの掲載対象にします。よろしいですか？');">
                            @csrf
                            <button type="submit" class="btn btn-success" {{ $post->isPrChecked() ? '' : 'disabled' }}>掲載する</button>
                        </form>
                    </div>
                    @unless ($post->isPrChecked())
                        <div class="small text-muted mb-3">← 先に #PR を確認するとボタンが押せます</div>
                    @endunless
                @endif

                @if ($st !== \App\Models\SnsPost::STATUS_REJECTED)
                    <form method="post" action="{{ route('admin.sns-posts.reject', $post) }}"
                          onsubmit="return confirm('この投稿を却下します。よろしいですか？');">
                        @csrf
                        <div class="input-group">
                            <select name="reject_reason" class="form-select" required>
                                <option value="">却下理由を選択</option>
                                <option>#PR の記載がない</option>
                                <option>非公開アカウント・投稿が見られない</option>
                                <option>投稿が削除されている</option>
                                <option>内容が不適切</option>
                                <option>その他</option>
                            </select>
                            <button type="submit" class="btn btn-outline-danger">却下する</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">③ 表示順（任意）</div>
            <div class="card-body">
                <form method="post" action="{{ route('admin.sns-posts.sort', $post) }}" class="d-flex gap-2 align-items-center">
                    @csrf
                    <input type="number" name="sort_order" value="{{ $post->sort_order }}" class="form-control" style="max-width: 120px;" min="0" max="9999">
                    <button type="submit" class="btn btn-outline-primary">保存</button>
                    <span class="small text-muted">小さいほど上に表示。同じなら承認が新しい順。</span>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script async src="{{ \App\Models\SnsPost::$embedScripts[$post->platform] ?? '' }}"
        onerror="document.getElementById('embed-fallback').hidden = false;"></script>
<script>
// 各SNSのスクリプトは blockquote を iframe に置き換える。数秒たっても置き換わらなければ表示できていないと判断する
setTimeout(function () {
    var area = document.getElementById('embed-area');
    if (area && !area.querySelector('iframe')) {
        document.getElementById('embed-fallback').hidden = false;
    }
}, 6000);
</script>
@endsection
