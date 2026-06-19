<?php

namespace App\Console\Commands;

use App\Models\Reward;
use App\Models\Setting;
use Illuminate\Console\Command;

/**
 * 未確定の成果を確定・取消するバッチ（日次想定）。
 *
 * - 注文がキャンセル/返品 → cancelled
 * - 発生から confirm_after_days 経過＆正常 → confirmed
 */
class ConfirmRewards extends Command
{
    const ORDER_STATUS_CANCEL = 3;
    const ORDER_STATUS_RETURNED = 9;

    protected $signature = 'affiliate:confirm-rewards';

    protected $description = '未確定のアンバサダー成果を確定・取消します。';

    public function handle(): int
    {
        $days = Setting::current()->confirm_after_days;
        $threshold = now()->subDays($days);
        $cancelStatuses = [self::ORDER_STATUS_CANCEL, self::ORDER_STATUS_RETURNED];

        // キャンセル/返品の取消
        $cancelled = Reward::where('status', Reward::STATUS_PENDING)
            ->whereIn('order_status_id', $cancelStatuses)
            ->update([
                'status' => Reward::STATUS_CANCELLED,
            ]);

        // 猶予期間を過ぎた正常注文を確定（ステータス未連携=nullも対象）
        $confirmed = Reward::where('status', Reward::STATUS_PENDING)
            ->where('converted_at', '<=', $threshold)
            ->where(function ($q) use ($cancelStatuses) {
                $q->whereNull('order_status_id')
                    ->orWhereNotIn('order_status_id', $cancelStatuses);
            })
            ->update([
                'status' => Reward::STATUS_CONFIRMED,
                'confirmed_at' => now(),
            ]);

        $this->info(sprintf('確定: %d件 / 取消: %d件', $confirmed, $cancelled));

        return self::SUCCESS;
    }
}
