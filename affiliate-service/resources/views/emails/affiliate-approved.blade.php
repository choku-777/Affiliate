<!DOCTYPE html>
<html lang="ja">
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; line-height: 1.7;">
    <p>{{ $affiliate->name }} 様</p>
    <p>アフィリエイトのお申し込みが承認されました。<br>
       以下のURLからのご紹介が成果対象になります（サイトごと）。</p>
    @foreach ($affiliateUrls as $row)
        <p style="margin: 6px 0;">
            <strong>{{ $row['site']->name }}</strong><br>
            <a href="{{ $row['url'] }}">{{ $row['url'] }}</a>
        </p>
    @endforeach
    <p>成果や報酬の状況は、マイページからご確認いただけます。<br>
       登録時のメールアドレスとパスワードで下記からログインしてください。</p>
    <p><a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>
    <hr>
    <p style="color:#888; font-size: 12px;">本メールにお心当たりのない場合は破棄してください。</p>
</body>
</html>
