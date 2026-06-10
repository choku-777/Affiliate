<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Reward;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totals = Reward::selectRaw('status, SUM(reward_amount) AS total, COUNT(*) AS cnt')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return view('admin.dashboard', [
            'pendingAffiliates' => Affiliate::where('status', Affiliate::STATUS_PENDING)->count(),
            'approvedAffiliates' => Affiliate::where('status', Affiliate::STATUS_APPROVED)->count(),
            'totals' => $totals,
            'statusLabels' => Reward::$statusLabels,
        ]);
    }
}
