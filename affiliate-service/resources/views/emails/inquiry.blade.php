<p>アフィリエイターからお問い合わせがありました。</p>

<table cellpadding="4" style="border-collapse:collapse;">
    <tr><td style="color:#666;">お名前</td><td>{{ $affiliate->name }}</td></tr>
    <tr><td style="color:#666;">メール</td><td>{{ $affiliate->email }}</td></tr>
    <tr><td style="color:#666;">アフィリエイターID</td><td>#{{ $affiliate->id }}（{{ $affiliate->affiliate_code }}）</td></tr>
</table>

<p style="margin-top:16px;"><strong>件名：</strong>{{ $subjectLine }}</p>

<p><strong>お問い合わせ内容：</strong></p>
<p>{!! nl2br(e($body)) !!}</p>

<hr style="margin-top:24px;">
<p style="color:#888;font-size:12px;">このメールに返信すると、アフィリエイター本人（{{ $affiliate->email }}）に届きます。</p>
