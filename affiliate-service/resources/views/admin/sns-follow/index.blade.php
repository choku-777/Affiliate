@extends('layouts.admin')
@section('title', '投稿フォロー')

@section('content')
<h1 class="h4 mb-3">投稿フォロー</h1>

<div class="row g-3 mb-3 text-center">
    <div class="col-6 col-md-3"><div class="card card-body">
        <div class="small text-muted">発送済み</div><div class="h4 mb-0">{{ $shippedCount }}</div>
    </div></div>
    <div class="col-6 col-md-3"><div class="card card-body">
        <div class="small text-muted">申告あり</div>
        <div class="h4 mb-0">{{ $postedCount }} <small class="fs-6 text-muted">({{ $shippedCount ? round($postedCount / $shippedCount * 100) : 0 }}%)</small></div>
    </div></div>
    <div class="col-6 col-md-3"><div class="card card-body">
        <div class="small text-muted">申告待ち</div><div class="h4 mb-0">{{ $pending->count() }}</div>
    </div></div>
    <div class="col-6 col-md-3"><div class="card card-body">
        <div class="small text-muted">期限切れ</div><div class="h4 mb-0 {{ $overdueCount ? 'text-danger' : '' }}">{{ $overdueCount }}</div>
    </div></div>
</div>

<div class="alert alert-light border small">
    自動リマインド：<strong>{{ $setting->sns_reminder_enabled ? 'ON' : 'OFF' }}</strong>
    （発送{{ $setting->sns_reminder_after_days }}日後／期限{{ $setting->sns_reminder_before_days }}日前／期限の翌日に、毎朝10時に自動送信）。<br>
    SNSのアカウント名を押すとプロフィールが開きます。投稿を見つけたら、URLを「代理登録」に貼って登録してください（登録後、「SNS投稿」画面で #PR を確認します）。
</div>

<div class="card mb-4">
    <div class="card-header">申告待ち（期限の近い順）</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead class="text-nowrap">
                <tr><th>アンバサダー</th><th>発送日</th><th>期限</th><th>自動リマインド</th><th>SNS</th><th style="min-width: 320px;">代理登録／催促</th></tr>
            </thead>
            <tbody>
                @forelse ($pending as $r)
                    @php
                        $deadline = $r->snsDeadline($days);
                        $left = (int) now()->startOfDay()->diffInDays($deadline->copy()->startOfDay(), false);
                        $a = $r->affiliate;
                        $profile = $a?->snsProfileUrl();
                    @endphp
                    <tr>
                        <td class="text-nowrap"><a href="{{ route('admin.affiliates.show', $a) }}">{{ $a?->name }}</a></td>
                        <td class="text-nowrap small">{{ optional($r->shipped_at)->format('Y-m-d') }}</td>
                        <td class="text-nowrap">
                            <div class="small">{{ $deadline->format('Y-m-d') }}</div>
                            @if ($left < 0)
                                <span class="badge bg-danger">期限切れ（{{ -$left }}日）</span>
                            @elseif ($left <= 3)
                                <span class="badge bg-warning text-dark">あと{{ $left }}日</span>
                            @else
                                <span class="badge bg-light text-dark border">あと{{ $left }}日</span>
                            @endif
                        </td>
                        <td class="text-nowrap small">
                            <div>{{ $r->reminder_arrival_at ? '✅' : '・' }} 到着確認</div>
                            <div>{{ $r->reminder_before_at ? '✅' : '・' }} 期限前</div>
                            <div>{{ $r->reminder_overdue_at ? '✅' : '・' }} 期限切れ</div>
                            @if ($r->reminder_manual_at)
                                <div class="text-muted">手動 {{ $r->reminder_manual_at->format('n/j') }}</div>
                            @endif
                        </td>
                        <td class="small">
                            <div class="text-nowrap">{{ $a?->sns }}</div>
                            @if ($profile)
                                <a href="{{ $profile }}" target="_blank" rel="noopener" class="text-nowrap">{{ $a->sns_account }} ↗</a>
                            @else
                                <span class="text-muted">{{ $a?->sns_account ?: '—' }}</span>
                            @endif
                        </td>
                        <td>
                            <form method="post" action="{{ route('admin.sns-follow.register') }}" class="d-flex gap-1 mb-1">
                                @csrf
                                <input type="hidden" name="affiliate_id" value="{{ $a?->id }}">
                                <input type="hidden" name="sample_request_id" value="{{ $r->id }}">
                                <input type="url" name="url" class="form-control form-control-sm" placeholder="見つけた投稿のURL" required>
                                <button type="submit" class="btn btn-sm btn-primary text-nowrap">代理登録</button>
                            </form>
                            <form method="post" action="{{ route('admin.sns-follow.remind', $r) }}"
                                  onsubmit="return confirm('{{ $a?->name }} 様へ催促メールを送ります。よろしいですか？');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary">催促メールを送る</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">申告待ちの人はいません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>仕組み導入前にサンプルを送った方（投稿は任意のお願い）</span>
        @php $legacyUnsent = $legacy->whereNull('sns_request_mail_sent_at')->count(); @endphp
        @if ($legacyUnsent > 0)
            <form method="post" action="{{ route('admin.sns-follow.remind-legacy-all') }}"
                  onsubmit="return confirm('未送信の {{ $legacyUnsent }} 名へ、投稿のお願いメールを一括で送ります。\n\n※メールは取り消せません。よろしいですか？');">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary">未送信の{{ $legacyUnsent }}名へお願いメールを一括送信</button>
            </form>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead class="text-nowrap">
                <tr><th>アンバサダー</th><th>送付日</th><th>お願いメール</th><th>SNS</th><th style="min-width: 320px;">代理登録／お願い</th></tr>
            </thead>
            <tbody>
                @forelse ($legacy as $a)
                    @php $profile = $a->snsProfileUrl(); @endphp
                    <tr>
                        <td class="text-nowrap"><a href="{{ route('admin.affiliates.show', $a) }}">{{ $a->name }}</a></td>
                        <td class="text-nowrap small">{{ $a->sample_sent_at->format('Y-m-d') }}</td>
                        <td class="text-nowrap small">
                            @if ($a->sns_request_mail_sent_at)
                                <span class="badge bg-success">送信済</span> {{ $a->sns_request_mail_sent_at->format('n/j') }}
                            @else
                                <span class="badge bg-secondary">未送信</span>
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
                            <form method="post" action="{{ route('admin.sns-follow.register') }}" class="d-flex gap-1 mb-1">
                                @csrf
                                <input type="hidden" name="affiliate_id" value="{{ $a->id }}">
                                <input type="url" name="url" class="form-control form-control-sm" placeholder="見つけた投稿のURL" required>
                                <button type="submit" class="btn btn-sm btn-primary text-nowrap">代理登録</button>
                            </form>
                            @unless ($a->sns_request_mail_sent_at)
                                <form method="post" action="{{ route('admin.sns-follow.remind-legacy', $a) }}"
                                      onsubmit="return confirm('{{ $a->name }} 様へお願いメールを送ります。よろしいですか？');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">お願いメールを送る</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted">対象の方はいません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
