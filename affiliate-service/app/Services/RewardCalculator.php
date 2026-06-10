<?php

namespace App\Services;

/**
 * 報酬額の計算ロジック（端数切り捨て）。
 */
class RewardCalculator
{
    public function calculate(int|float|string $orderTotal, int|float|string $rate): int
    {
        $orderTotal = (float) $orderTotal;
        $rate = (float) $rate;

        if ($orderTotal <= 0 || $rate <= 0) {
            return 0;
        }

        return (int) floor($orderTotal * $rate / 100);
    }
}
