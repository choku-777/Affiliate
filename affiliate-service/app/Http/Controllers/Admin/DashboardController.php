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

        // 直近12ヶ月の月次集計
        $now = now();
        // 月初を基準にして引く（月末日から引くと存在しない日付になり翌月へ繰り上がるため）
        $monthStart = $now->copy()->startOfMonth();
        $since = $monthStart->copy()->subMonths(11);

        // アンバサダー経由の売上（注文金額・取消を除く）を月別集計
        $salesByMonth = Reward::where('status', '!=', Reward::STATUS_CANCELLED)
            ->where('converted_at', '>=', $since)
            ->selectRaw("DATE_FORMAT(converted_at, '%Y-%m') AS ym, SUM(order_total) AS total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        // 新規登録者数を月別集計
        $regByMonth = Affiliate::where('created_at', '>=', $since)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS cnt")
            ->groupBy('ym')
            ->pluck('cnt', 'ym');

        // 直近12ヶ月の系列に整形（データの無い月は0埋め）
        $labels = $salesSeries = $regSeries = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = $monthStart->copy()->subMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->format('Y/n');
            $salesSeries[] = (int) ($salesByMonth[$key] ?? 0);
            $regSeries[] = (int) ($regByMonth[$key] ?? 0);
        }

        $thisKey = $now->format('Y-m');
        $lastKey = $now->copy()->subMonthsNoOverflow()->format('Y-m');

        return view('admin.dashboard', [
            'pendingAffiliates' => Affiliate::where('status', Affiliate::STATUS_PENDING)->count(),
            'approvedAffiliates' => Affiliate::where('status', Affiliate::STATUS_APPROVED)->count(),
            'totals' => $totals,
            'statusLabels' => Reward::$statusLabels,
            'salesThisMonth' => (int) ($salesByMonth[$thisKey] ?? 0),
            'salesLastMonth' => (int) ($salesByMonth[$lastKey] ?? 0),
            'regThisMonth' => (int) ($regByMonth[$thisKey] ?? 0),
            'regLastMonth' => (int) ($regByMonth[$lastKey] ?? 0),
            'chartLabels' => $labels,
            'chartSales' => $salesSeries,
            'chartReg' => $regSeries,
        ]);
    }
}
