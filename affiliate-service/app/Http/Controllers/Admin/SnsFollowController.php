<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SnsReminderMail;
use App\Models\Affiliate;
use App\Models\SampleRequest;
use App\Models\Setting;
use App\Models\SnsPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * SNS投稿のフォロー。発送済みで申告がない人を期限の近い順に並べ、
 * 手動の催促メールと、管理側での代理登録（見つけた投稿URLの登録）を行う。
 * 期限切れでも自動で停止はしない（印を付けて人が判断する）。
 */
class SnsFollowController extends Controller
{
    public function index(): View
    {
        $setting = Setting::current();
        $days = (int) $setting->sns_post_deadline_days;

        // 新方式：発送済みの申込
        $shipped = SampleRequest::with(['affiliate', 'snsPosts'])
            ->where('status', SampleRequest::STATUS_SHIPPED)
            ->get();

        $pending = $shipped->reject(fn ($r) => $r->hasActiveSnsPost())
            ->sortBy(fn ($r) => $r->snsDeadline($days)?->timestamp ?? PHP_INT_MAX)
            ->values();

        return view('admin.sns-follow.index', [
            'setting' => $setting,
            'days' => $days,
            'pending' => $pending,
            'shippedCount' => $shipped->count(),
            'postedCount' => $shipped->filter(fn ($r) => $r->hasActiveSnsPost())->count(),
            'overdueCount' => $pending->filter(fn ($r) => now()->greaterThan($r->snsDeadline($days)))->count(),
            'legacy' => $this->legacyAffiliates(),
        ]);
    }

    /**
     * 手動の催促メール（新方式の申込）。
     */
    public function remind(SampleRequest $sampleRequest): RedirectResponse
    {
        $affiliate = $sampleRequest->affiliate;
        if (!$affiliate?->email) {
            return back()->with('error', 'メールアドレスが登録されていません。');
        }

        try {
            Mail::to($affiliate->email)->send(new SnsReminderMail($affiliate, SnsReminderMail::TYPE_MANUAL, $sampleRequest));
            $sampleRequest->update(['reminder_manual_at' => now()]);
        } catch (\Throwable $e) {
            Log::error('SNS催促メールの送信に失敗しました: '.$e->getMessage());

            return back()->with('error', 'メールの送信に失敗しました。');
        }

        return back()->with('success', $affiliate->name.' 様へ催促メールを送りました。');
    }

    /**
     * 仕組み導入前の送付者へのお願いメール（1人ずつ）。
     */
    public function remindLegacy(Affiliate $affiliate): RedirectResponse
    {
        return $this->sendLegacy(collect([$affiliate]))
            ? back()->with('success', $affiliate->name.' 様へお願いメールを送りました。')
            : back()->with('warning', '送信対象ではないか、すでに送信済みです。');
    }

    /**
     * 仕組み導入前の送付者へのお願いメール（未送信の人へ一括）。
     */
    public function remindLegacyAll(): RedirectResponse
    {
        $count = $this->sendLegacy($this->legacyAffiliates()->whereNull('sns_request_mail_sent_at'));

        return back()->with('success', "{$count}名へお願いメールを送りました。");
    }

    /**
     * 管理側での代理登録。見つけた投稿URLを、そのアンバサダーの申告として登録する（#PR確認は通常どおり必要）。
     */
    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'affiliate_id' => ['required', 'integer', 'exists:affiliates,id'],
            'sample_request_id' => ['nullable', 'integer', 'exists:sample_requests,id'],
            'url' => ['required', 'string', 'max:500', 'url'],
        ], [], ['url' => '投稿URL']);

        $result = SnsPost::analyze($data['url']);
        if (is_string($result)) {
            return back()->with('error', $result);
        }

        $adminName = $request->session()->get('admin_name') ?: ($request->session()->get('admin_email') ?: '管理者');

        SnsPost::create([
            'affiliate_id' => $data['affiliate_id'],
            'sample_request_id' => $data['sample_request_id'] ?? null,
            'platform' => $result['platform'],
            'post_url' => $data['url'],
            'normalized_url' => $result['normalized_url'],
            'post_id' => $result['post_id'],
            'status' => SnsPost::STATUS_PENDING,
            'note' => "管理画面から代理登録（{$adminName}）",
        ]);

        return back()->with('success', '投稿を登録しました。「SNS投稿」画面で #PR を確認してください。');
    }

    /**
     * 仕組み導入前にサンプルを送った人（新方式の発送記録がなく、有効な申告もない承認済みアンバサダー）。
     */
    private function legacyAffiliates(): Collection
    {
        return Affiliate::with('snsPosts')
            ->whereNotNull('sample_sent_at')
            ->where('status', Affiliate::STATUS_APPROVED)
            ->whereDoesntHave('sampleRequests', fn ($q) => $q->where('status', SampleRequest::STATUS_SHIPPED))
            ->orderBy('sample_sent_at')
            ->get()
            ->reject(fn ($a) => $a->snsPosts->whereIn('status', [
                SnsPost::STATUS_PENDING, SnsPost::STATUS_CHECKED, SnsPost::STATUS_APPROVED,
            ])->isNotEmpty())
            ->values();
    }

    private function sendLegacy(Collection $affiliates): int
    {
        $sent = 0;
        foreach ($affiliates as $affiliate) {
            if ($affiliate->sns_request_mail_sent_at || !$affiliate->email) {
                continue;
            }
            try {
                Mail::to($affiliate->email)->send(new SnsReminderMail($affiliate, SnsReminderMail::TYPE_LEGACY));
                $affiliate->update(['sns_request_mail_sent_at' => now()]);
                $sent++;
            } catch (\Throwable $e) {
                Log::error('お願いメールの送信に失敗しました: '.$e->getMessage(), ['affiliate_id' => $affiliate->id]);
            }
        }

        return $sent;
    }
}
