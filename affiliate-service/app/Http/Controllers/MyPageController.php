<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * アフィリエイター本人向けマイページ（ログイン必須）。
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

        return view('mypage', [
            'affiliate' => $affiliate,
            'rewards' => $rewards,
            'totals' => $totals,
            'statusLabels' => Reward::$statusLabels,
        ]);
    }
}
