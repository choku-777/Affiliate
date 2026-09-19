<!DOCTYPE html>
<html lang="ja">
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; line-height: 1.8; color: #333;">
    <p>{{ $sampleRequest->affiliate?->name }} 様</p>

    <p>いつもご紹介いただきありがとうございます。<br>
       うましっぽ アンバサダー事務局です。</p>

    <p>お申し込みいただいたサンプル商品を、本日発送いたしました。</p>

    <table style="border-collapse: collapse; margin: 8px 0;">
        <tr>
            <td style="padding: 2px 12px 2px 0; color:#888;">商品</td>
            <td>{{ $sampleRequest->product_name }}</td>
        </tr>
        <tr>
            <td style="padding: 2px 12px 2px 0; color:#888;">配送方法</td>
            <td>ヤマト運輸 ネコポス（ポストへのお届け）</td>
        </tr>
        @if ($sampleRequest->tracking_number)
            <tr>
                <td style="padding: 2px 12px 2px 0; color:#888;">伝票番号</td>
                <td>{{ $sampleRequest->formattedTrackingNumber() }}</td>
            </tr>
        @endif
    </table>

    @if ($sampleRequest->tracking_number)
        <p>配送状況は下記からご確認いただけます。<br>
           <a href="{{ $sampleRequest->trackingUrl() }}">{{ $sampleRequest->trackingUrl() }}</a></p>
    @endif

    <hr style="border:none; border-top:1px solid #eee; margin:20px 0;">

    <p style="font-weight: bold; font-size: 15px;">■ SNS投稿のお願い（サンプルのお受け取り条件です）</p>

    <p>お申し込みの際にご同意いただいたとおり、商品が届きましたら
       <strong>X・Instagram・TikTok のいずれか</strong>にご感想の投稿をお願いいたします。</p>

    @if ($deadline)
        <p style="background:#fff4e5; border-left:4px solid #e6a23c; padding:8px 12px; margin:12px 0;">
            <strong>投稿期限：{{ $deadline->format('Y年n月j日') }}</strong>（発送から{{ $setting->sns_post_deadline_days }}日以内）
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
    </ul>

    <p style="margin-bottom:4px;">▼ SNSごとのご注意</p>
    <ul style="margin-top:0; color:#555; font-size:13px;">
        <li>Instagram：フィード投稿またはリールで（ストーリーズは24時間で消えるため対象外です）</li>
        <li>X：文の先頭に @ を置かないでください（返信扱いになります）</li>
        <li>TikTok：投稿設定の「コンテンツの開示」をONにしてください</li>
    </ul>

    <p style="margin-bottom:4px;">▼ 投稿したら</p>
    <p style="margin-top:0;">マイページの「<strong>投稿したURLを申告する</strong>」から、投稿のURLを送ってください。<br>
       内容を確認のうえ、「うましっぽ」公式サイトなどで紹介させていただきます。<br>
       <a href="{{ route('affiliate.sns-posts.index') }}">{{ route('affiliate.sns-posts.index') }}</a></p>

    <p style="color:#555; font-size:13px;">
        良い点も気になった点も、正直なご感想で大丈夫です。<br>
        公式サイトの商品写真は、マイページの「アンバサダー特典」のとおりご自由にお使いいただけます。
    </p>

    <p>ご不明な点は、マイページの「お問い合わせ」からご連絡ください。</p>

    <hr style="border:none; border-top:1px solid #eee; margin:20px 0;">
    <p style="color:#999; font-size: 12px;">
        うましっぽ / 馬肉特急 アンバサダー事務局<br>
        大陸通商株式会社<br>
        <a href="https://umashippo.jp" style="color:#999;">https://umashippo.jp</a>
    </p>
</body>
</html>
