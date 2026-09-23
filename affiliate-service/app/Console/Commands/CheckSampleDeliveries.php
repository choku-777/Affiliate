<?php

namespace App\Console\Commands;

use App\Models\SampleRequest;
use App\Services\YamatoTracker;
use Illuminate\Console\Command;

/**
 * 発送済みで到着日が未確定のサンプルについて、ヤマトの追跡から到着日を取得する（日次）。
 * 発送から一定日数たっても取得できない場合は「発送日＋2日」を推定の到着日とする
 * （リマインドと投稿期限が止まらないようにするため）。
 */
class CheckSampleDeliveries extends Command
{
    /** この日数を過ぎても取得できなければ推定日を入れる */
    const GIVE_UP_DAYS = 7;

    /** 推定の到着日（発送日からの日数）。ネコポスの通常の所要日数 */
    const ESTIMATE_DAYS = 2;

    protected $signature = 'affiliate:check-deliveries {--dry-run : 記録せず結果だけ表示する}';

    protected $description = 'サンプルの到着日をヤマトの追跡から取得する';

    public function handle(YamatoTracker $tracker): int
    {
        $dry = (bool) $this->option('dry-run');
        $targets = SampleRequest::with('affiliate')
            ->where('status', SampleRequest::STATUS_SHIPPED)
            ->whereNotNull('shipped_at')
            ->whereNull('delivered_at')
            ->orderBy('shipped_at')
            ->get();

        foreach ($targets as $i => $request) {
            if ($i > 0) {
                sleep(1); // 追跡ページへの連続アクセスを避ける
            }

            $delivered = $request->tracking_number
                ? $tracker->deliveredAt($request->tracking_number, $request->shipped_at)
                : null;

            $name = $request->affiliate?->name;
            if ($delivered) {
                $this->line("[到着] {$name}: ".$delivered->format('Y-m-d H:i'));
                $dry || $request->update(['delivered_at' => $delivered, 'delivered_source' => 'yamato', 'delivery_checked_at' => now()]);
                continue;
            }

            if ($request->shipped_at->copy()->addDays(self::GIVE_UP_DAYS)->isPast()) {
                $estimate = $request->shipped_at->copy()->addDays(self::ESTIMATE_DAYS)->setTime(12, 0);
                $this->line("[推定] {$name}: ".$estimate->format('Y-m-d').'（取得できないため）');
                $dry || $request->update(['delivered_at' => $estimate, 'delivered_source' => 'estimate', 'delivery_checked_at' => now()]);
                continue;
            }

            $this->line("[配達中] {$name}");
            $dry || $request->update(['delivery_checked_at' => now()]);
        }

        $this->info(($dry ? '確認のみ：' : '').$targets->count().'件を確認しました。');

        return self::SUCCESS;
    }
}
