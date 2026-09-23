<!DOCTYPE html>
<html lang="ja">
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; line-height: 1.8; color: #333;">
    <p>{{ $affiliate->name }} 様</p>

    <p>いつもご紹介いただきありがとうございます。<br>
       うましっぽ アンバサダー事務局です。</p>

    @switch($type)
        @case('arrival')
            <p>先日お届けしたサンプル商品は、お試しいただけましたでしょうか。<br>
               わんちゃんの反応はいかがでしたか？ ぜひSNSでご感想を投稿していただけますと幸いです。</p>
            @break
        @case('before')
            <p>お送りしたサンプル商品のご感想投稿について、<strong>期限が近づいて</strong>まいりましたのでご連絡いたしました。</p>
            @break
        @case('overdue')
            <p>サンプル商品のご感想投稿の期限を過ぎておりますが、まだ投稿のご申告を確認できておりません。<br>
               すでに投稿済みの場合は、<strong>投稿URLのご申告のみ</strong>お願いいたします。</p>
            @break
        @case('legacy')
            <p>以前お送りしたサンプル商品は、お試しいただけましたでしょうか。<br>
               よろしければ、SNSでご感想をお聞かせいただけますと大変うれしく思います。<br>
               投稿いただいたご感想は、「うましっぽ」公式サイトなどでご紹介させていただく場合がございます。</p>
            @break
        @default
            <p>お送りしたサンプル商品について、SNSでのご感想の投稿をお願いいたします。</p>
    @endswitch

    @if ($deadline && $type !== 'legacy')
        <p style="background:#fff4e5; border-left:4px solid #e6a23c; padding:8px 12px; margin:12px 0;">
            <strong>投稿期限：{{ $deadline->format('Y年n月j日') }}</strong>
        </p>
    @endif

    <p style="margin-bottom:4px;">▼ 投稿に入れていただくもの</p>
    <ul style="margin-top:0;">
        <li>ハッシュタグ：<strong>{{ $setting->sns_hashtag }}</strong> と <strong>#PR</strong><br>
            <span style="color:#888; font-size:12px;">（#PR は法律上必要な表示です。必ずお願いします）</span></li>
        @php
            $accounts = array_filter([
                'X' => $setting->sns_account_x,
                'Instagram' => $setting->sns_account_instagram,
                'TikTok' => $setting->sns_account_tiktok,
            ]);
        @endphp
        @if ($accounts)
            <li>公式アカウントのメンション：
                @foreach ($accounts as $name => $account)
                    {{ $name }} <strong>{{ '@'.ltrim($account, '@') }}</strong>{{ $loop->last ? '' : ' ／ ' }}
                @endforeach
            </li>
        @endif
        <li>投稿は「<strong>公開</strong>」設定で（非公開だと確認できません）</li>
        <li style="color:#555; font-size:13px;">Instagramはフィード投稿またはリールで（ストーリーズは対象外です）</li>
    </ul>

    <p style="margin-bottom:4px;">▼ 投稿したら（または、すでに投稿済みの方）</p>
    <p style="margin-top:0;">マイページにログインし、「<strong>投稿したURLを申告する</strong>」から投稿のURLを送ってください。<br>
       <a href="{{ route('affiliate.sns-posts.index') }}">{{ route('affiliate.sns-posts.index') }}</a></p>

    <p style="color:#555; font-size:13px;">良い点も気になった点も、正直なご感想で大丈夫です。</p>

    <p>ご不明な点は、マイページの「お問い合わせ」からご連絡ください。<br>
       行き違いでご申告済みの場合は、失礼をお許しください。</p>

    <hr style="border:none; border-top:1px solid #eee; margin:20px 0;">
    <p style="color:#999; font-size: 12px;">
        うましっぽ / 馬肉特急 アンバサダー事務局<br>
        大陸通商株式会社<br>
        <a href="https://umashippo.jp" style="color:#999;">https://umashippo.jp</a>
    </p>
</body>
</html>
