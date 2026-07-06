<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Site;

/**
 * 公開トップ（アンバサダー募集LP）。
 * 料率・確定日数・最低支払額などは管理画面の設定（Setting / Site）から流し込む。
 */
class LandingController extends Controller
{
    public function index()
    {
        $setting = Setting::current();

        $inu = Site::byCode('umashippo');
        $uma = Site::byCode('baniku');

        // サイト個別料率がnullなら全体設定の料率にフォールバック
        $inuRate = (float) ($inu->commission_rate ?? $setting->commission_rate);
        $umaRate = (float) ($uma->commission_rate ?? $setting->commission_rate);

        // 料率の表示用整形（20.00→「20」、12.50→「12.5」）
        $fmtRate = fn (float $r) => rtrim(rtrim(number_format($r, 2), '0'), '.');

        // 報酬例（料率の高い方で、1,000円のご注文時）
        $exampleOrder = 1000;
        $exampleReward = (int) floor($exampleOrder * $inuRate / 100);

        return view('landing', [
            'inuName'       => $inu->name ?? 'うましっぽ',
            'umaName'       => $uma->name ?? '馬肉特急',
            'inuRateLabel'  => $fmtRate($inuRate),
            'umaRateLabel'  => $fmtRate($umaRate),
            'maxRateLabel'  => $fmtRate(max($inuRate, $umaRate)),
            'confirmDays'   => (int) $setting->confirm_after_days,
            'minPayoutLabel'=> number_format((int) $setting->min_payout_amount),
            'exampleOrder'  => number_format($exampleOrder),
            'exampleReward' => number_format($exampleReward),
        ]);
    }
}
