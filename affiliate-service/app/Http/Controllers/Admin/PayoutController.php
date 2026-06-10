<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Payout;
use App\Models\Reward;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * 報酬の一括支払い（アフィリエイター単位）。
 * 確定済(confirmed)報酬の合計が最低支払額(min_payout_amount)以上のアフィリエイターをまとめて支払う。
 */
class PayoutController extends Controller
{
    public function index(): View
    {
        $minPayout = (int) Setting::current()->min_payout_amount;

        // 確定済み（未払い）報酬を持つアフィリエイターを集計
        $targets = Affiliate::query()
            ->withSum(['rewards as confirmed_total' => fn ($q) => $q->where('status', Reward::STATUS_CONFIRMED)], 'reward_amount')
            ->withCount(['rewards as confirmed_count' => fn ($q) => $q->where('status', Reward::STATUS_CONFIRMED)])
            ->get()
            ->filter(fn ($a) => (int) $a->confirmed_total > 0)
            ->sortByDesc('confirmed_total')
            ->values();

        $payouts = Payout::with('affiliate')->orderByDesc('id')->limit(50)->get();

        return view('admin.payouts.index', [
            'targets' => $targets,
            'minPayout' => $minPayout,
            'payouts' => $payouts,
        ]);
    }

    public function pay(Affiliate $affiliate)
    {
        $minPayout = (int) Setting::current()->min_payout_amount;

        $rewards = $affiliate->rewards()
            ->where('status', Reward::STATUS_CONFIRMED)
            ->get();
        $amount = (int) $rewards->sum('reward_amount');

        if ($amount <= 0) {
            return back()->with('warning', '支払い対象の確定報酬がありません。');
        }
        if ($amount < $minPayout) {
            return back()->with('warning', '最低支払額（'.number_format($minPayout).'円）に達していません。');
        }

        DB::transaction(function () use ($affiliate, $rewards, $amount) {
            $payout = Payout::create([
                'affiliate_id' => $affiliate->id,
                'amount' => $amount,
                'reward_count' => $rewards->count(),
                'paid_at' => now(),
            ]);

            Reward::whereIn('id', $rewards->pluck('id'))->update([
                'status' => Reward::STATUS_PAID,
                'paid_at' => now(),
                'payout_id' => $payout->id,
            ]);
        });

        return back()->with('success', $affiliate->name.' さんに '.number_format($amount).'円 を支払い済みにしました（'.$rewards->count().'件）。');
    }
}
