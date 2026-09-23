@extends('layouts.admin')
@section('title', 'SNS投稿')

@section('content')
<h1 class="h4 mb-3">SNS投稿</h1>

<ul class="nav nav-pills mb-3 flex-wrap gap-1">
    @foreach ($tabs as $key => $label)
        <li class="nav-item">
            <a class="nav-link {{ $tab === $key ? 'active' : '' }}" href="{{ route('admin.sns-posts.index', ['tab' => $key]) }}">
                {{ $label }}
                <span class="badge {{ $tab === $key ? 'bg-light text-dark' : ($key === 'action' && $counts[$key] > 0 ? 'bg-danger' : 'bg-secondary') }}">{{ $counts[$key] }}</span>
            </a>
        </li>
    @endforeach
</ul>

<div class="alert alert-light border small">
    @switch($tab)
        @case('action')
            <strong>今日やること</strong>はこのタブだけ見ればOKです。
            「確認中」は投稿を開いて <strong>#PR を確認</strong>、「期限切れ」はプロフィールを見て投稿を探すか催促してください。
            @break
        @case('waiting')
            投稿の申告がまだの方です（期限の近い順）。SNSのアカウント名を押すとプロフィールが開きます。
            投稿を見つけたら、URLを「代理登録」に貼ってください。
            @break
        @case('legacy')
            仕組みを作る前にサンプルを送った方です（投稿は任意のお願い）。お願いメールは1人1回です。
            @break
        @default
            サンプルを受け取った方ごとの、SNS投稿の状況です。
    @endswitch
    <div class="text-muted mt-1">
        自動リマインド：{{ $setting->sns_reminder_enabled ? 'ON' : 'OFF' }}（発送{{ $setting->sns_reminder_after_days }}日後／期限{{ $setting->sns_reminder_before_days }}日前／期限の翌日、毎朝10時）
    </div>
</div>

@if ($tab === 'legacy' && $legacyUnsent > 0)
    <form method="post" action="{{ route('admin.sns-follow.remind-legacy-all') }}" class="mb-3"
          onsubmit="return confirm('未送信の {{ $legacyUnsent }} 名へ、投稿のお願いメールを一括で送ります。\n\n※メールは取り消せません。よろしいですか？');">
        @csrf
        <button type="submit" class="btn btn-primary btn-sm">未送信の{{ $legacyUnsent }}名へお願いメールを一括送信</button>
    </form>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead class="text-nowrap">
                <tr>
                    <th>アンバサダー</th>
                    <th>発送日</th>
                    <th>期限</th>
                    <th>状態</th>
                    <th>最新の申告</th>
                    <th>リマインド</th>
                    <th>SNS</th>
                    <th style="min-width: 300px;">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    @php
                        $a = $row->affiliate;
                        $post = $row->latestPost;
                        $req = $row->sampleRequest;
                        $profile = $a->snsProfileUrl();
                    @endphp
                    <tr>
                        <td class="text-nowrap"><a href="{{ route('admin.affiliates.show', $a) }}">{{ $a->name }}</a></td>
                        <td class="text-nowrap small">{{ optional($row->shippedAt)->format('Y-m-d') ?? '—' }}</td>
                        <td class="text-nowrap small">
                            @if ($row->deadline)
                                <div>{{ $row->deadline->format('Y-m-d') }}</div>
                                @if (in_array($row->stage, ['unposted', 'overdue'], true))
                                    @if ($row->daysLeft < 0)
                                        <span class="badge bg-danger">{{ -$row->daysLeft }}日超過</span>
                                    @elseif ($row->daysLeft <= 3)
                                        <span class="badge bg-warning text-dark">あと{{ $row->daysLeft }}日</span>
                                    @else
                                        <span class="badge bg-light text-dark border">あと{{ $row->daysLeft }}日</span>
                                    @endif
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            @switch($row->stage)
                                @case('pending') <span class="badge bg-info text-dark">確認中</span> @break
                                @case('checked') <span class="badge bg-primary">確認済み</span> @break
                                @case('approved') <span class="badge bg-success">掲載中</span> @break
                                @case('rejected') <span class="badge bg-secondary">却下・非表示</span> @break
                                @case('overdue') <span class="badge bg-danger">期限切れ</span> @break
                                @case('legacy') <span class="badge bg-light text-dark border">導入前</span> @break
                                @default <span class="badge bg-warning text-dark">未申告</span>
                            @endswitch
                        </td>
                        <td class="small text-nowrap">
                            @if ($post)
                                {{ $post->platformLabel() }} / {{ $post->created_at->format('n/j') }}
                                @if ($row->postCount > 1)<span class="text-muted">（計{{ $row->postCount }}件）</span>@endif
                                <div>@if ($post->isPrChecked())<span class="badge bg-success">#PR確認済</span>@else<span class="badge bg-warning text-dark">#PR未確認</span>@endif</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="small text-nowrap">
                            @if ($req)
                                {{ $req->reminder_arrival_at ? '✅' : '・' }}到着
                                {{ $req->reminder_before_at ? '✅' : '・' }}期限前
                                {{ $req->reminder_overdue_at ? '✅' : '・' }}超過
                                @if ($req->reminder_manual_at)<div class="text-muted">手動 {{ $req->reminder_manual_at->format('n/j') }}</div>@endif
                            @elseif ($row->legacy)
                                @if ($a->sns_request_mail_sent_at)
                                    <span class="badge bg-success">お願い済</span> {{ $a->sns_request_mail_sent_at->format('n/j') }}
                                @else
                                    <span class="text-muted">お願い未送信</span>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="small">
                            <div class="text-nowrap">{{ $a->sns }}</div>
                            @if ($profile)
                                <a href="{{ $profile }}" target="_blank" rel="noopener" class="text-nowrap">{{ $a->sns_account }} ↗</a>
                            @else
                                <span class="text-muted">{{ $a->sns_account ?: '—' }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($post && $row->stage !== 'rejected')
                                <a href="{{ route('admin.sns-posts.show', $post) }}" class="btn btn-sm {{ $row->stage === 'pending' ? 'btn-primary' : 'btn-outline-primary' }}">
                                    {{ $row->stage === 'pending' ? '#PRを確認する' : '投稿を見る' }}
                                </a>
                            @else
                                <form method="post" action="{{ route('admin.sns-follow.register') }}" class="d-flex gap-1 mb-1">
                                    @csrf
                                    <input type="hidden" name="affiliate_id" value="{{ $a->id }}">
                                    @if ($req)<input type="hidden" name="sample_request_id" value="{{ $req->id }}">@endif
                                    <input type="url" name="url" class="form-control form-control-sm" placeholder="見つけた投稿のURL" required>
                                    <button type="submit" class="btn btn-sm btn-primary text-nowrap">代理登録</button>
                                </form>
                                <div class="d-flex gap-1 flex-wrap">
                                    @if ($req)
                                        <form method="post" action="{{ route('admin.sns-follow.remind', $req) }}"
                                              onsubmit="return confirm('{{ $a->name }} 様へ催促メールを送ります。よろしいですか？');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">催促メール</button>
                                        </form>
                                    @elseif ($row->legacy && !$a->sns_request_mail_sent_at)
                                        <form method="post" action="{{ route('admin.sns-follow.remind-legacy', $a) }}"
                                              onsubmit="return confirm('{{ $a->name }} 様へお願いメールを送ります。よろしいですか？');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">お願いメール</button>
                                        </form>
                                    @endif
                                    @if ($post)
                                        <a href="{{ route('admin.sns-posts.show', $post) }}" class="btn btn-sm btn-outline-secondary">却下した投稿</a>
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-muted">該当する方はいません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
