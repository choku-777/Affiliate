{{-- SNS投稿のお願い。$setting 必須、$deadline は任意（発送済みのときだけ渡す） --}}
@php
    $accounts = array_filter([
        'X' => $setting->sns_account_x,
        'Instagram' => $setting->sns_account_instagram,
        'TikTok' => $setting->sns_account_tiktok,
    ]);
@endphp
<div class="alert alert-light border small mb-0">
    <div class="fw-bold mb-2">📣 投稿のお願い</div>
    <ul class="mb-2 ps-3">
        <li class="mb-1">
            ハッシュタグ：<strong>{{ $setting->sns_hashtag }}</strong> と <strong>#PR</strong><br>
            <span class="text-muted">（#PR は法律上必要な表示です。必ずお願いします）</span>
        </li>
        @if ($accounts)
            <li class="mb-1">
                公式アカウントのメンション：
                @foreach ($accounts as $name => $account)
                    {{ $name }} <strong>{{ '@'.ltrim($account, '@') }}</strong>{{ $loop->last ? '' : ' ／ ' }}
                @endforeach
            </li>
        @endif
        <li class="mb-1">投稿は「<strong>公開</strong>」設定でお願いします（非公開だと掲載できません）</li>
        <li>良い点も気になった点も、正直なご感想で大丈夫です</li>
    </ul>
    <div class="text-muted">
        <div>Instagram：フィード投稿またはリールで（ストーリーズは24時間で消えるため掲載できません）</div>
        <div>X：文の先頭に @ を置かないでください（返信扱いになり広がりません）</div>
        <div>TikTok：投稿設定の「コンテンツの開示」をONにしてください</div>
    </div>
    @if (!empty($deadline))
        <div class="mt-2 fw-bold text-danger">投稿期限：{{ $deadline->format('Y年n月j日') }} まで</div>
    @endif
</div>
