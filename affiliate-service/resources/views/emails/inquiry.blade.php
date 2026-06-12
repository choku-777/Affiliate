<p>お問い合わせがありました。</p>

<table cellpadding="4" style="border-collapse:collapse;">
    <tr><td style="color:#666;">お名前</td><td>{{ $senderName }}</td></tr>
    <tr><td style="color:#666;">メール</td><td>{{ $senderEmail }}</td></tr>
    @if ($affiliate)
        <tr><td style="color:#666;">区分</td><td>登録済み会員（ID #{{ $affiliate->id }} / {{ $affiliate->affiliate_code }}）</td></tr>
    @else
        <tr><td style="color:#666;">区分</td><td>未登録（登録画面からのお問い合わせ）</td></tr>
    @endif
</table>

<p style="margin-top:16px;"><strong>件名：</strong>{{ $subjectLine }}</p>

<p><strong>お問い合わせ内容：</strong></p>
<p>{!! nl2br(e($body)) !!}</p>

<hr style="margin-top:24px;">
<p style="color:#888;font-size:12px;">このメールに返信すると、問い合わせ者（{{ $senderEmail }}）に届きます。</p>
