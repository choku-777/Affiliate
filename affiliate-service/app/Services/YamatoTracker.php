<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ヤマト運輸の荷物お問い合わせページから配達完了日時を読み取る。
 * 公式の照会APIが一般提供されていないため、お客様向けの追跡ページを読む。
 * 1日1回・未到着の荷物だけを対象にし、アクセス数を最小限にする。
 * ページ構成が変わって読めなくなっても null を返すだけで、呼び出し側は推定日で補う。
 */
class YamatoTracker
{
    const URL = 'https://toi.kuronekoyamato.co.jp/cgi-bin/tneko';

    /**
     * 配達完了の日時を返す。未配達・取得失敗なら null。
     *
     * @param  Carbon  $shippedAt  年の補完に使う（ページには月日しか出ないため）
     */
    public function deliveredAt(string $trackingNumber, Carbon $shippedAt): ?Carbon
    {
        $number = preg_replace('/\D/', '', $trackingNumber);
        if (strlen($number) !== 12) {
            return null;
        }

        try {
            $response = Http::asForm()
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->timeout(15)
                ->post(self::URL, ['number00' => '1', 'number01' => $number]);
        } catch (\Throwable $e) {
            Log::warning('ヤマト追跡の取得に失敗しました: '.$e->getMessage(), ['number' => $number]);

            return null;
        }

        if (!$response->successful()) {
            return null;
        }

        $html = $response->body();
        if (!mb_check_encoding($html, 'UTF-8')) {
            $html = mb_convert_encoding($html, 'UTF-8', 'SJIS-win');
        }

        // <div class="item">配達完了</div> <div class="date">09月15日 14:08</div>
        if (!preg_match('#class="item">\s*(?:配達完了|投函完了)\s*</div>\s*<div class="date">\s*(\d{1,2})月(\d{1,2})日\s*(\d{1,2}):(\d{2})#u', $html, $m)) {
            return null;
        }

        $delivered = Carbon::create($shippedAt->year, (int) $m[1], (int) $m[2], (int) $m[3], (int) $m[4]);
        // 年末に発送して年明けに届いた場合
        if ($delivered->lt($shippedAt->copy()->startOfDay())) {
            $delivered->addYear();
        }

        return $delivered;
    }
}
