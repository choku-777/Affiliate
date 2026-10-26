<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Announcement;
use App\Models\Reward;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * アンバサダー本人向けマイページ（ログイン必須）。
 * ログインセッションの affiliate_id から本人を特定する。
 */
class MyPageController extends Controller
{
    public function show(Request $request): View
    {
        $affiliate = Affiliate::findOrFail($request->session()->get('affiliate_id'));

        $rewards = $affiliate->rewards()
            ->orderByDesc('id')
            ->paginate(20);

        $totals = $affiliate->rewards()
            ->selectRaw('status, SUM(reward_amount) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $payouts = $affiliate->payouts()->orderByDesc('id')->limit(20)->get();

        return view('mypage', [
            'affiliate' => $affiliate,
            'rewards' => $rewards,
            'totals' => $totals,
            'statusLabels' => Reward::$statusLabels,
            'payouts' => $payouts,
            'announcements' => Announcement::published()->get(),
            'setting' => Setting::current(),
            'sampleRequest' => $affiliate->activeSampleRequest(),
        ]);
    }

    /**
     * 投稿の画像・動画を公式サイトに掲載することへの同意を記録する（取り消しは問い合わせで受け付ける）。
     */
    public function consentMedia(Request $request): RedirectResponse
    {
        $request->validate(['agree' => ['accepted']], ['agree.accepted' => '同意のチェックを入れてください。']);

        Affiliate::findOrFail($request->session()->get('affiliate_id'))->recordMediaConsent();

        return redirect()->route('affiliate.mypage')->with('success', 'ご同意ありがとうございます。公式サイトでご紹介させていただきます。');
    }
}
