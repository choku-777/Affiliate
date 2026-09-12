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
            <td>ヤマト運輸 ネコポス</td>
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

    <p>ネコポスはポストへのお届けです。ご不在でもお受け取りいただけます。</p>

    <p>商品が届きましたら、ぜひSNSなどでご紹介いただけると嬉しいです。<br>
       公式サイトの画像は、マイページの「アンバサダー特典」のとおりご自由にお使いいただけます。</p>

    <p>ご不明な点は、マイページの「お問い合わせ」からご連絡ください。</p>

    <hr style="border:none; border-top:1px solid #eee; margin:20px 0;">
    <p style="color:#999; font-size: 12px;">
        うましっぽ / 馬肉特急 アンバサダー事務局<br>
        大陸通商株式会社<br>
        <a href="https://umashippo.jp" style="color:#999;">https://umashippo.jp</a>
    </p>
</body>
</html>
