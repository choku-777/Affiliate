<?php

namespace Plugin\Affiliate\Service;

/**
 * 報酬額の計算ロジック。EC-CUBE 非依存にして単体テストしやすくする。
 */
class RewardCalculator
{
    /**
     * 報酬額を計算する（端数は切り捨て）。
     *
     * @param int|float|string $orderTotal 計算対象金額（注文合計＝送料・税込）
     * @param int|float|string $rate       料率（%）
     */
    public function calculate($orderTotal, $rate): int
    {
        $orderTotal = (float) $orderTotal;
        $rate = (float) $rate;

        if ($orderTotal <= 0 || $rate <= 0) {
            return 0;
        }

        return (int) floor($orderTotal * $rate / 100);
    }
}
