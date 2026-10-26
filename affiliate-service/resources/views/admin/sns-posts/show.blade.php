@extends('layouts.admin')
@section('title', 'SNS投稿の確認')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">SNS投稿の確認</h1>
    <a href="{{ route('admin.sns-posts.index', ['tab' => 'action']) }}" class="btn btn-outline-secondary btn-sm">一覧へ戻る</a>
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
                    <tr><th>画像の掲載同意</th><td>
                        @if ($post->affiliate?->hasMediaConsent())
                            同意済み（{{ $post->affiliate->media_consent_at->format('Y-m-d H:i') }}{{ $post->affiliate->isMediaConsentByProxy() ? '・代理記録' : '' }}）
                        @else
                            <span class="text-danger">未同意</span>
                            <span class="text-muted">（同意が記録されるまで、サムネイルの保存・公式サイトへの掲載はできません）</span>
                            @if ($post->affiliate)
                                <form method="post" action="{{ route('admin.sns-posts.media-consent', $post) }}" class="mt-1"
                                      onsubmit="return confirm('このアンバサダーから、画像・動画の公式サイト掲載への同意をもらっていますか？\n代理で同意を記録します（記録者と日時はメモに残ります）。');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">代理で同意を記録</button>
                                </form>
                            @endif
                        @endif
                    </td></tr>
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

        @php $missing = $post->missingListingFields(); @endphp
        <div class="card mb-3">
            <div class="card-header">② 公式サイト掲載用の情報</div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger small py-2 mb-2">
                        @foreach ($errors->all() as $message)<div>{{ $message }}</div>@endforeach
                    </div>
                @endif
                @if ($missing)
                    <div class="alert alert-warning small py-2">掲載するには入力が必要です（未入力：{{ implode('・', $missing) }}）</div>
                @endif
                <form method="post" action="{{ route('admin.sns-posts.listing', $post) }}">
                    @csrf
                    <div class="row g-2 small">
                        <div class="col-sm-6">
                            <label class="form-label mb-1" for="site_id">掲載先サイト</label>
                            <select name="site_id" id="site_id" class="form-select form-select-sm" required>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}" {{ (int) old('site_id', $post->effectiveSite()?->id) === $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label mb-1" for="product">商品ページのURL（または商品番号）</label>
                            <input type="text" name="product" id="product" class="form-control form-control-sm" required
                                   value="{{ old('product', $post->product_id) }}" placeholder="https://umashippo.jp/products/detail/19">
                        </div>
                        <div class="col-12">
                            <label class="form-label mb-1" for="product_name">商品名（サイトに表示）</label>
                            <input type="text" name="product_name" id="product_name" class="form-control form-control-sm" required maxlength="255"
                                   value="{{ old('product_name', $post->product_name ?? $post->sampleRequest?->product_name) }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label mb-1" for="dog_breed">犬種<span class="text-muted">（任意）</span></label>
                            <input type="text" name="dog_breed" id="dog_breed" class="form-control form-control-sm" maxlength="64"
                                   value="{{ old('dog_breed', $post->dog_breed) }}" placeholder="例：ポメラニアン">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label mb-1" for="dog_age">年齢<span class="text-muted">（任意）</span></label>
                            <input type="text" name="dog_age" id="dog_age" class="form-control form-control-sm" maxlength="32"
                                   value="{{ old('dog_age', $post->dog_age) }}" placeholder="例：12歳 / 8ヶ月">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label mb-1" for="dog_size">体格<span class="text-muted">（任意）</span></label>
                            <select name="dog_size" id="dog_size" class="form-select form-select-sm">
                                <option value="">未選択</option>
                                @foreach (\App\Models\SnsPost::$dogSizeLabels as $value => $label)
                                    <option value="{{ $value }}" {{ old('dog_size', $post->dog_size) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label mb-1" for="dog_stage">年代<span class="text-muted">（任意）</span></label>
                            <select name="dog_stage" id="dog_stage" class="form-select form-select-sm">
                                <option value="">未選択</option>
                                @foreach (\App\Models\SnsPost::$dogStageLabels as $value => $label)
                                    <option value="{{ $value }}" {{ old('dog_stage', $post->dog_stage) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary mt-2">保存</button>
                </form>

                <hr>
                <div class="small fw-bold mb-2">サムネイル</div>
                <div class="d-flex gap-3 align-items-start">
                    @if ($post->hasThumbnail())
                        <img src="{{ route('admin.sns-posts.thumbnail', $post) }}?v={{ $post->thumbnail_fetched_at?->timestamp }}"
                             alt="サムネイル" style="width: 90px; height: 160px; object-fit: cover; border-radius: 6px;">
                    @endif
                    <div class="small">
                        @if ($post->hasThumbnail())
                            <div class="text-muted mb-2">{{ $post->thumbnail_fetched_at?->format('Y-m-d H:i') }} に保存</div>
                        @else
                            <div class="text-muted mb-2">「掲載する」を押したときに自動で取得します。</div>
                        @endif
                        <form method="post" action="{{ route('admin.sns-posts.thumbnail.fetch', $post) }}" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">{{ $post->hasThumbnail() ? '取り直す' : '今すぐ取得する' }}</button>
                        </form>
                        <form method="post" action="{{ route('admin.sns-posts.thumbnail.upload', $post) }}" enctype="multipart/form-data">
                            @csrf
                            <label class="form-label mb-1" for="thumbnail">自動で取れないときだけ：画像をアップロード（JPEG・PNG・WebP）</label>
                            <div class="input-group input-group-sm">
                                <input type="file" name="thumbnail" id="thumbnail" class="form-control" accept="image/jpeg,image/png,image/webp" required>
                                <button type="submit" class="btn btn-outline-secondary">登録</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">③ 確認 / 掲載 / 却下</div>
            <div class="card-body">
                @php $st = $post->status; @endphp

                @if ($st === \App\Models\SnsPost::STATUS_APPROVED)
                    @if ($post->isListable())
                        <div class="alert alert-success small">現在「掲載中」です。公式サイトに表示されます。</div>
                    @else
                        <div class="alert alert-warning small">「掲載中」ですが、<strong>公式サイトにはまだ表示されません</strong>。② の掲載用の情報を保存し、サムネイルを取得してください。</div>
                    @endif
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
                            <button type="submit" class="btn btn-success" {{ $post->isPrChecked() && !$missing ? '' : 'disabled' }}>掲載する</button>
                        </form>
                    </div>
                    @unless ($post->isPrChecked())
                        <div class="small text-muted mb-3">← 先に #PR を確認するとボタンが押せます</div>
                    @endunless
                    @if ($post->isPrChecked() && $missing)
                        <div class="small text-muted mb-3">「掲載する」は、② の掲載用の情報を保存すると押せます</div>
                    @endif
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
            <div class="card-header">④ 表示順（任意）</div>
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
