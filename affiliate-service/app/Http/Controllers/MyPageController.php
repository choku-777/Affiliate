<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Reward;
use Illuminate\View\View;

/**
 * アフィリエイター本人向けマイページ（トークンURLでアクセス・ログイン不要）。
 */
class MyPageController extends Controller
{
    public function show(string $token): View
    {
        $affiliate = Affiliate::where('mypage_token', $token)->firstOrFail();

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
