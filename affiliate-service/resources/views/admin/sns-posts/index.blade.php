@extends('layouts.admin')
@section('title', 'SNS投稿')

@section('content')
<h1 class="h4 mb-3">SNS投稿</h1>

<ul class="nav nav-pills mb-3 flex-wrap gap-1">
    @foreach ($statusLabels as $key => $label)
        <li class="nav-item">
            <a class="nav-link {{ $status === $key ? 'active' : '' }}" href="{{ route('admin.sns-posts.index', ['status' => $key]) }}">
                {{ $label }} <span class="badge {{ $status === $key ? 'bg-light text-dark' : 'bg-secondary' }}">{{ $counts[$key] ?? 0 }}</span>
            </a>
        </li>
    @endforeach
    <li class="nav-item">
        <a class="nav-link {{ $status === 'all' ? 'active' : '' }}" href="{{ route('admin.sns-posts.index', ['status' => 'all']) }}">すべて</a>
    </li>
</ul>

<div class="alert alert-light border small">
    「確認する」を開くと投稿の実物が表示されます。<strong>#PR の記載を確認してチェックを入れないと承認できません</strong>（無料サンプルと引き換えの投稿は法律上 #PR が必要です）。
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width: 92px;">申告日</th>
                    <th style="width: 170px;">アンバサダー</th>
                    <th style="width: 90px;">SNS</th>
                    <th>投稿URL</th>
                    <th style="width: 80px;">#PR</th>
                    <th style="width: 96px;">状態</th>
                    <th style="width: 110px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($posts as $post)
                    <tr>
                        <td class="small text-nowrap">{{ $post->created_at->format('Y-m-d') }}</td>
                        <td>{{ $post->affiliate?->name }}</td>
                        <td class="text-nowrap">{{ $post->platformLabel() }}</td>
                        <td class="small"><a href="{{ $post->normalized_url }}" target="_blank" rel="noopener" style="word-break: break-all;">{{ $post->normalized_url }}</a></td>
                        <td>
                            @if ($post->isPrChecked())
                                <span class="badge bg-success">確認済</span>
                            @else
                                <span class="badge bg-warning text-dark">未確認</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            @if ($post->status === \App\Models\SnsPost::STATUS_APPROVED)
                                <span class="badge bg-success">掲載中</span>
                            @elseif ($post->status === \App\Models\SnsPost::STATUS_PENDING)
                                <span class="badge bg-info text-dark">確認中</span>
                            @elseif ($post->status === \App\Models\SnsPost::STATUS_CHECKED)
                                <span class="badge bg-primary">確認済み</span>
                            @elseif ($post->status === \App\Models\SnsPost::STATUS_REJECTED)
                                <span class="badge bg-danger">却下</span>
                            @else
                                <span class="badge bg-secondary">非表示</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.sns-posts.show', $post) }}" class="btn btn-sm btn-outline-primary">確認する</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">該当する投稿はありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $posts->links() }}</div>
@endsection
