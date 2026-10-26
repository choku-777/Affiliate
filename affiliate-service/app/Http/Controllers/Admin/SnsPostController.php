<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SnsPost;
use App\Models\Site;
use App\Services\SnsThumbnailFetcher;
use App\Services\SnsTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
            'post' => $snsPost->load('affiliate', 'sampleRequest', 'site'),
            'sites' => Site::whereIn('code', SnsPost::CONSENTED_SITE_CODES)->orderBy('id')->get(),
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

    public function approve(Request $request, SnsPost $snsPost, SnsThumbnailFetcher $fetcher): RedirectResponse
    {
        if (!$snsPost->isPrChecked()) {
            return back()->with('error', '承認の前に「#PR の記載を確認した」にチェックを入れてください。');
        }

        if (!$snsPost->affiliate?->hasMediaConsent()) {
            return back()->with('error', 'このアンバサダーから、画像・動画の公式サイト掲載への同意をまだもらっていません。マイページで同意してもらうまで掲載できません。');
        }

        $missing = $snsPost->missingListingFields();
        if ($missing) {
            return back()->with('error', '掲載の前に「② 公式サイト掲載用の情報」を入力してください（未入力：'.implode('・', $missing).'）。');
        }

        // サムネイルは承認の時点で自動取得する（SNS側の画像URLは数日で失効するため、その場で保存）
        if (!$snsPost->hasThumbnail() && !$fetcher->fetch($snsPost)) {
            return back()->with('error', 'サムネイルを自動で取得できませんでした。「②」から画像をアップロードしてから、もう一度「掲載する」を押してください。');
        }

        $snsPost->update([
            'status' => SnsPost::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $this->adminLabel($request),
            'reject_reason' => null,
        ]);

        return back()->with('success', '掲載中にしました。公式サイトの掲載対象になります。');
    }

    /**
     * 本人から（LINE・メール等で）同意をもらった場合に、管理者が代理で同意を記録する。
     * 誰がいつ記録したかを、アンバサダーのメモ（追記のみの履歴）に自動で残す。
     */
    public function consentByProxy(Request $request, SnsPost $snsPost): RedirectResponse
    {
        $affiliate = $snsPost->affiliate;
        if (!$affiliate) {
            return back()->with('error', 'アンバサダーが見つかりません。');
        }
        if ($affiliate->hasMediaConsent()) {
            return back()->with('success', 'すでに同意が記録されています。');
        }

        DB::transaction(function () use ($affiliate, $request) {
            $affiliate->recordMediaConsent(byProxy: true);
            $affiliate->notes()->create([
                'body' => '画像・動画の公式サイト掲載への同意を、管理者が代理で記録しました（同意文の版 '.\App\Models\Affiliate::MEDIA_CONSENT_VERSION.'）。',
                'created_by' => $request->session()->get('admin_email'),
                'created_by_name' => $request->session()->get('admin_name'),
            ]);
        });

        return back()->with('success', '同意を代理で記録しました。');
    }

    /**
     * 公式サイト掲載用の情報（掲載先・商品・犬の情報）を保存する。
     * 商品は商品ページのURL（…/products/detail/19）でも、番号だけでも受け付ける。
     */
    public function listing(Request $request, SnsPost $snsPost): RedirectResponse
    {
        $data = $request->validate([
            'site_id' => ['required', 'integer', Rule::exists('sites', 'id')->whereIn('code', SnsPost::CONSENTED_SITE_CODES)],
            'product' => ['required', 'string', 'max:500'],
            'product_name' => ['required', 'string', 'max:255'],
            // 犬の情報は任意（空欄なら表示しない）
            'dog_breed' => ['nullable', 'string', 'max:64'],
            'dog_age' => ['nullable', 'string', 'max:32'],
            'dog_size' => ['nullable', 'in:'.implode(',', array_keys(SnsPost::$dogSizeLabels))],
            'dog_stage' => ['nullable', 'in:'.implode(',', array_keys(SnsPost::$dogStageLabels))],
        ], [], [
            'site_id' => '掲載先サイト',
            'product' => '商品',
            'product_name' => '商品名',
            'dog_breed' => '犬種',
            'dog_age' => '年齢',
            'dog_size' => '体格',
            'dog_stage' => '年代',
        ]);

        $productId = $this->parseProductId($data['product']);
        if (!$productId) {
            return back()->withInput()->with('error', '商品は、商品ページのURL（…/products/detail/数字）か商品番号で入力してください。');
        }

        $snsPost->update([
            'site_id' => (int) $data['site_id'],
            'product_id' => $productId,
            'product_name' => $data['product_name'],
            'dog_breed' => $data['dog_breed'] ?? null,
            'dog_age' => $data['dog_age'] ?? null,
            'dog_size' => $data['dog_size'] ?? null,
            'dog_stage' => $data['dog_stage'] ?? null,
        ]);

        return back()->with('success', '掲載用の情報を保存しました。');
    }

    /**
     * サムネイルを取り直す（画像を更新したいとき等）。
     */
    public function fetchThumbnail(SnsPost $snsPost, SnsThumbnailFetcher $fetcher): RedirectResponse
    {
        if (!$snsPost->affiliate?->hasMediaConsent()) {
            return back()->with('error', '画像・動画の掲載への同意をもらうまで、サムネイルは保存できません。');
        }
        if (!$fetcher->fetch($snsPost)) {
            return back()->with('error', 'サムネイルを自動で取得できませんでした。画像をアップロードしてください。');
        }

        return back()->with('success', 'サムネイルを取得しました。');
    }

    /**
     * 自動取得に失敗したときの保険。手元の画像をサムネイルとして登録する。
     */
    public function uploadThumbnail(Request $request, SnsPost $snsPost, SnsThumbnailFetcher $fetcher): RedirectResponse
    {
        if (!$snsPost->affiliate?->hasMediaConsent()) {
            return back()->with('error', '画像・動画の掲載への同意をもらうまで、サムネイルは保存できません。');
        }

        $request->validate([
            'thumbnail' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ], [], ['thumbnail' => 'サムネイル画像']);

        try {
            $stored = $fetcher->storeUploaded($snsPost, $request->file('thumbnail'));
        } catch (\Throwable $e) {
            $stored = false;
        }
        if (!$stored) {
            return back()->with('error', '画像を保存できませんでした。JPEG・PNG・WebP の画像を選んでください。');
        }

        return back()->with('success', 'サムネイルを登録しました。');
    }

    /**
     * 管理画面でのサムネイル確認用（掲載前の投稿も見られる）。
     */
    public function thumbnail(SnsPost $snsPost): BinaryFileResponse
    {
        abort_unless($snsPost->hasThumbnail(), 404);

        return response()->file(storage_path('app/'.$snsPost->thumbnail_path), [
            'Content-Type' => 'image/jpeg',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-cache',
        ]);
    }

    /** 商品IDの上限（DBの unsignedInteger の範囲） */
    private const MAX_PRODUCT_ID = 4294967295;

    private function parseProductId(string $value): ?int
    {
        $value = trim($value);
        if (preg_match('#/products/detail/(\d{1,10})(?:\D|$)#', $value, $m)) {
            $digits = $m[1];
        } elseif (preg_match('/^\d{1,10}$/', $value)) {
            $digits = $value;
        } else {
            return null;
        }
        $id = (int) $digits;

        return $id >= 1 && $id <= self::MAX_PRODUCT_ID ? $id : null;
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
