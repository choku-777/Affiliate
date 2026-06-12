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
}
