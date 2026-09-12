@extends('layouts.admin')
@section('title', 'お知らせ')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">お知らせ</h1>
    <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">新規作成</a>
</div>

<div class="alert alert-light border small">
    ここで作ったお知らせは、アンバサダーの<strong>マイページの一番上</strong>に表示されます。<br>
    「公開する」にチェックを入れたものだけが表示されます。表示順は数字が小さいほど上に出ます。
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr><th>タイトル</th><th>状態</th><th class="text-end">表示順</th><th>更新日</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($announcements as $announcement)
                    <tr>
                        <td>{{ $announcement->title }}</td>
                        <td>
                            @if ($announcement->is_published)
                                <span class="badge bg-success">公開中</span>
                            @else
                                <span class="badge bg-secondary">非公開</span>
                            @endif
                        </td>
                        <td class="text-end">{{ $announcement->sort_order }}</td>
                        <td class="text-nowrap">{{ $announcement->updated_at->format('Y-m-d') }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-sm btn-outline-primary">編集</a>
                            <form method="post" action="{{ route('admin.announcements.destroy', $announcement) }}" class="d-inline"
                                  onsubmit="return confirm('このお知らせを削除します。よろしいですか？');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">削除</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted">まだお知らせはありません。</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $announcements->links() }}</div>
@endsection
