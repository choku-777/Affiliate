<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RewardController extends Controller
{
    public function index(Request $request): View
    {
        $query = Reward::query()->with(['affiliate', 'site'])->orderByDesc('id');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($siteId = $request->query('site_id')) {
            $query->where('site_id', $siteId);
        }
        if ($affiliateId = $request->query('affiliate_id')) {
            $query->where('affiliate_id', $affiliateId);
        }
        if ($start = $request->query('start')) {
            $query->whereDate('converted_at', '>=', $start);
        }
        if ($end = $request->query('end')) {
            $query->whereDate('converted_at', '<=', $end);
        }

        $totals = (clone $query)
            ->selectRaw('status, SUM(reward_amount) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.rewards.index', [
            'rewards' => $query->paginate(20)->withQueryString(),
            'totals' => $totals,
            'statusLabels' => Reward::$statusLabels,
            'sites' => Site::query()->orderByDesc('is_default')->orderBy('id')->get(),
            'filters' => $request->only('status', 'affiliate_id', 'start', 'end', 'site_id'),
        ]);
    }

    /**
     * 報酬を任意で取り消す（管理者の手動操作）。未確定・確定のみ対象。
     */
    public function cancel(Reward $reward)
    {
        if (! in_array($reward->status, [Reward::STATUS_PENDING, Reward::STATUS_CONFIRMED], true)) {
            return back()->with('warning', 'この報酬は取り消せません（支払済または取消済です）。');
        }

        $reward->update(['status' => Reward::STATUS_CANCELLED]);

        app(\App\Services\DiscordNotifier::class)->rewardCancelled($reward, '管理者による手動取消');

        return back()->with('success', "報酬（注文番号 {$reward->order_no}）を取り消しました。");
    }
}
