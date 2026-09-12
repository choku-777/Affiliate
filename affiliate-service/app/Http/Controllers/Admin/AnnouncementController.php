<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * マイページに掲載するお知らせの管理。
 * 運用担当・管理者のどちらも編集できる。
 */
class AnnouncementController extends Controller
{
    public function index(): View
    {
        return view('admin.announcements.index', [
            'announcements' => Announcement::orderBy('sort_order')->orderByDesc('id')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.announcements.form', [
            'announcement' => new Announcement(['is_published' => false, 'sort_order' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Announcement::create($this->validated($request));

        return redirect()->route('admin.announcements.index')
            ->with('success', 'お知らせを追加しました。');
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.form', ['announcement' => $announcement]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update($this->validated($request));

        return redirect()->route('admin.announcements.index')
            ->with('success', 'お知らせを更新しました。');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'お知らせを削除しました。');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ], [], [
            'title' => 'タイトル',
            'body' => '本文',
            'sort_order' => '表示順',
        ]);

        // チェックボックスは未チェックだと送信されないため明示的に解決する
        $data['is_published'] = $request->boolean('is_published');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
