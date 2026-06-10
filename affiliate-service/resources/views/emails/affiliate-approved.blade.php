<!DOCTYPE html>
<html lang="ja">
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; line-height: 1.7;">
    <p>{{ $affiliate->name }} 様</p>
    <p>アフィリエイトのお申し込みが承認されました。<br>
       以下のURLからのご紹介が成果対象になります。</p>
    <p><a href="{{ $affiliateUrl }}">{{ $affiliateUrl }}</a></p>
    <p>成果や報酬の状況は、以下のマイページからご確認いただけます。</p>
    <p><a href="{{ $mypageUrl }}">{{ $mypageUrl }}</a></p>
    <hr>
    <p style="color:#888; font-size: 12px;">本メールにお心当たりのない場合は破棄してください。</p>
</body>
</html>
