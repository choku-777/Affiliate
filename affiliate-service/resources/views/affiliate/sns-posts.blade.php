@extends('layouts.app')
@section('title', 'SNS投稿の申告')

@section('content')
<h1 class="h4 mb-3">SNS投稿の申告</h1>

<div class="mb-3">
    @include('partials.sns-post-rules', ['setting' => $setting, 'deadline' => $deadline])
</div>

<div class="card mb-4">
    <div class="card-header">投稿したURLを送る</div>
    <div class="card-body">
        <p class="small text-muted mb-3">
            投稿の「共有」→「リンクをコピー」で取れるURLを、そのまま貼ってください。<br>
            X・Instagram・TikTok のどれでも大丈夫です。複数投稿した場合は、1つずつ送ってください。
        </p>
        <form method="post" action="{{ route('affiliate.sns-posts.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">投稿URL <span class="text-danger">*</span></label>
                <input type="url" name="url" value="{{ old('url') }}" class="form-control" placeholder="https://www.instagram.com/p/…" required>
                @error('url')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">ひとこと <span class="text-muted small">(任意)</span></label>
                <input type="text" name="note" value="{{ old('note') }}" class="form-control" maxlength="500">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">この投稿を申告する</button>
                <a href="{{ route('affiliate.mypage') }}" class="btn btn-outline-secondary">マイページへ戻る</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">申告した投稿</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr><th>申告日</th><th>SNS</th><th>投稿</th><th>状態</th></tr>
            </thead>
            <tbody>
                @forelse ($posts as $post)
                    <tr>
                        <td class="text-nowrap small">{{ $post->created_at->format('Y-m-d') }}</td>
                        <td class="text-nowrap">{{ $post->platformLabel() }}</td>
                        <td class="small"><a href="{{ $post->normalized_url }}" target="_blank" rel="noopener" style="word-break: break-all;">{{ $post->normalized_url }}</a></td>
                        <td class="text-nowrap">
                            @if ($post->status === \App\Models\SnsPost::STATUS_APPROVED)
                                <span class="badge bg-success">掲載中</span>
                            @elseif ($post->status === \App\Models\SnsPost::STATUS_PENDING)
                                <span class="badge bg-info text-dark">確認中</span>
                            @elseif ($post->status === \App\Models\SnsPost::STATUS_CHECKED)
                                <span class="badge bg-primary">確認済み</span>
                            @else
                                <span class="badge bg-secondary">非掲載</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">まだ申告はありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
