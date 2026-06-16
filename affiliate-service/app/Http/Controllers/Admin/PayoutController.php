<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\Reward;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * 月次締め支払い。
 * 毎月1日のバッチ(affiliate:monthly-payout)が確定報酬を締めて payouts に登録する。
 * 管理画面ではその月次リストを確認し、CSVダウンロード→振込→入金済みマークの順で進める。
 */
class PayoutController extends Controller
{
    public function index(): View
    {
        $payouts = Payout::with('affiliate')
            ->orderByDesc('closing_month')
            ->orderByDesc('id')
            ->paginate(50);

        // 締め月ごとのまとめ（CSVダウンロード・進捗表示用）
        $months = Payout::query()
            ->select(
                'closing_month',
                DB::raw('COUNT(*) AS cnt'),
                DB::raw('SUM(amount) AS total'),
                DB::raw('SUM(paid_at IS NOT NULL) AS paid_cnt')
            )
            ->groupBy('closing_month')
            ->orderByDesc('closing_month')
            ->get();

        return view('admin.payouts.index', [
            'payouts' => $payouts,
            'months' => $months,
        ]);
    }

    /**
     * 指定した締め月の支払いリストをCSVでダウンロード（Excel向けBOM付きUTF-8）。
     * ダウンロード時に csv_downloaded_at を記録する（①フラグ）。
     */
    public function downloadCsv(string $month)
    {
        $payouts = Payout::with('affiliate')
            ->where('closing_month', $month)
            ->orderBy('id')
            ->get();

        if ($payouts->isEmpty()) {
            return back()->with('warning', 'その締め月の支払いデータがありません。');
        }

        $rows = [['締め月', '氏名', '銀行名', '支店名', '口座種別', '口座番号', '口座名義', '金額']];
        foreach ($payouts as $p) {
            $a = $p->affiliate;
            $rows[] = [
                $p->closing_month,
                $a?->name ?? '',
                $a?->bank_name ?? '',
                $a?->bank_branch ?? '',
                $a?->account_type ?? '',
                $a?->account_number ?? '',
                $a?->account_holder ?? '',
                (string) $p->amount,
            ];
        }

        $handle = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = "\xEF\xBB\xBF".stream_get_contents($handle); // ExcelでUTF-8を正しく開くためBOM付与
        fclose($handle);

        // ①ダウンロード済みフラグを記録
        Payout::where('closing_month', $month)->update(['csv_downloaded_at' => now()]);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="payout_'.$month.'.csv"',
        ]);
    }

    /**
     * 入金済みにする（②フラグ）。対象の報酬を支払済に更新する。
     */
    public function markPaid(Payout $payout)
    {
        if ($payout->isPaid()) {
            return back()->with('warning', 'すでに入金済みです。');
        }

        DB::transaction(function () use ($payout) {
            $payout->update(['paid_at' => now()]);
            Reward::where('payout_id', $payout->id)->update([
                'status' => Reward::STATUS_PAID,
                'paid_at' => now(),
            ]);
        });

        return back()->with('success', optional($payout->affiliate)->name.' さんへの入金を記録しました（'.number_format($payout->amount).'円）。');
    }
}
