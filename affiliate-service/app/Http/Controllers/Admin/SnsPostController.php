<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SnsPost;
use App\Services\SnsTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * 申告されたSNS投稿の確認・承認。
 * #PR の確認チェックを入れないと承認できない（ステマ規制対策）。
 */
class SnsPostController extends Controller
{
    /**
     * サンプル受取者ごとのSNS投稿の状況（申告待ち〜掲載までを1画面で）。
     */
    public function index(Request $request, SnsTracker $tracker): View
    {
        $tab = (string) $request->query('tab', 'action');
        if (!array_key_exists($tab, SnsTracker::TABS)) {
            $tab = 'action';
        }

        $rows = $tracker->rows();
        $setting = Setting::current();

        return view('admin.sns-posts.index', [
            'tab' => $tab,
            'tabs' => SnsTracker::TABS,
            'counts' => $tracker->counts($rows),
            'rows' => $tracker->filter($rows, $tab),
            'setting' => $setting,
            'legacyUnsent' => $rows->where('stage', 'legacy')->filter(fn ($r) => !$r->affiliate->sns_request_mail_sent_at)->count(),
        ]);
    }

    /**
     * 1件の確認画面。実際の投稿を埋め込みで表示し、その場で承認・却下する。
     */
    public function show(SnsPost $snsPost): View
    {
        return view('admin.sns-posts.show', [
            'post' => $snsPost->load('affiliate', 'sampleRequest'),
        ]);
    }

    public function prCheck(Request $request, SnsPost $snsPost): RedirectResponse
    {
        if ($request->boolean('checked')) {
            $snsPost->update([
                'pr_checked_at' => now(),
                'pr_checked_by' => $this->adminLabel($request),
            ]);

            return back()->with('success', '#PR の記載を確認済みにしました。');
        }

        $snsPost->update(['pr_checked_at' => null, 'pr_checked_by' => null]);

        return back()->with('success', '#PR の確認を取り消しました。');
    }

    /**
     * 内容は確認したが掲載は保留、の状態にする（#PR確認が条件）。
     */
    public function check(Request $request, SnsPost $snsPost): RedirectResponse
    {
        if (!$snsPost->isPrChecked()) {
            return back()->with('error', '先に「#PR の記載を確認した」にチェックを入れてください。');
        }

        $snsPost->update([
            'status' => SnsPost::STATUS_CHECKED,
            'reject_reason' => null,
        ]);

        return back()->with('success', '確認済みにしました（掲載は保留中です）。');
    }

    public function approve(Request $request, SnsPost $snsPost): RedirectResponse
    {
        if (!$snsPost->isPrChecked()) {
            return back()->with('error', '承認の前に「#PR の記載を確認した」にチェックを入れてください。');
        }

        $snsPost->update([
            'status' => SnsPost::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $this->adminLabel($request),
            'reject_reason' => null,
        ]);

        return back()->with('success', '掲載中にしました。公式サイトの掲載対象になります。');
    }

    public function reject(Request $request, SnsPost $snsPost): RedirectResponse
    {
        $data = $request->validate([
            'reject_reason' => ['required', 'string', 'max:255'],
        ], [], ['reject_reason' => '却下理由']);

        $snsPost->update([
            'status' => SnsPost::STATUS_REJECTED,
            'reject_reason' => $data['reject_reason'],
        ]);

        return back()->with('success', '却下しました。');
    }

    public function hide(Request $request, SnsPost $snsPost): RedirectResponse
    {
        $snsPost->update(['status' => SnsPost::STATUS_HIDDEN]);

        return back()->with('success', '掲載を停止しました。');
    }

    public function sort(Request $request, SnsPost $snsPost): RedirectResponse
    {
        $data = $request->validate([
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ], [], ['sort_order' => '表示順']);

        $snsPost->update(['sort_order' => $data['sort_order']]);

        return back()->with('success', '表示順を保存しました。');
    }

    private function adminLabel(Request $request): string
    {
        $name = $request->session()->get('admin_name');
        $email = $request->session()->get('admin_email');

        return $name ?: ($email ?: '不明');
    }
}
