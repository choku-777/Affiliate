<?php

namespace Plugin\Affiliate\Tests\Service;

use PHPUnit\Framework\TestCase;
use Plugin\Affiliate\Service\RewardCalculator;

class RewardCalculatorTest extends TestCase
{
    /** @var RewardCalculator */
    private $calculator;

    protected function setUp(): void
    {
        $this->calculator = new RewardCalculator();
    }

    public function testCalculateBasic(): void
    {
        // 10,000円 × 5% = 500円
        self::assertSame(500, $this->calculator->calculate(10000, 5));
    }

    public function testCalculateFloorsFraction(): void
    {
        // 9,999円 × 5% = 499.95 → 切り捨て 499円
        self::assertSame(499, $this->calculator->calculate(9999, 5));
    }

    public function testCalculateWithDecimalRate(): void
    {
        // 12,345円 × 2.5% = 308.625 → 308円
        self::assertSame(308, $this->calculator->calculate(12345, '2.50'));
    }

    public function testZeroOrderTotalReturnsZero(): void
    {
        self::assertSame(0, $this->calculator->calculate(0, 5));
    }

    public function testZeroRateReturnsZero(): void
    {
        self::assertSame(0, $this->calculator->calculate(10000, 0));
    }

    public function testNegativeValuesReturnZero(): void
    {
        self::assertSame(0, $this->calculator->calculate(-100, 5));
    }
}
