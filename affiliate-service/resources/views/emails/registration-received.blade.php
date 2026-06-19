<!DOCTYPE html>
<html lang="ja">
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; line-height: 1.8; color: #333;">
    <p>{{ $affiliate->name }} 様</p>

    <p>このたびは「自然派いぬ生活」アンバサダープログラムへのご登録ありがとうございます。<br>
       下記の内容でお申し込みを受け付けました。</p>

    <table style="border-collapse: collapse; margin: 8px 0;">
        <tr><td style="padding: 2px 12px 2px 0; color:#888;">お名前</td><td>{{ $affiliate->name }}</td></tr>
        <tr><td style="padding: 2px 12px 2px 0; color:#888;">メールアドレス</td><td>{{ $affiliate->email }}</td></tr>
    </table>

    <p>現在、担当者が内容を確認しております。<br>
       審査が完了しましたら、<strong>ご紹介用URL</strong>と<strong>マイページへのログイン方法</strong>を、改めてメールでご案内いたします。<br>
       今しばらくお待ちください。</p>

    <hr style="border:none; border-top:1px solid #eee; margin:20px 0;">
    <p style="color:#999; font-size: 12px;">
        本メールにお心当たりのない場合は、お手数ですが破棄してください。<br>
        自然派いぬ生活 アンバサダープログラム
    </p>
</body>
</html>
