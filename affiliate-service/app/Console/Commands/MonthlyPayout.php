<?php

namespace App\Console\Commands;

use App\Models\Payout;
use App\Models\Reward;
use App\Models\Setting;
use App\Services\DiscordNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 月次の支払いピックアップ（毎月1日想定）。
 * 締め月末までに「確定」した未払い報酬をアンバサダー別に集計し、
 * 規定額（最低支払額）以上の人を支払いリスト（payouts）に登録する。
 */
class MonthlyPayout extends Command
{
    protected $signature = 'affiliate:monthly-payout {--month= : 締め月(YYYY-MM)。省略時は前月}';

    protected $description = '月次の支払いリストを作成します（確定済みの報酬を締めてピックアップ）。';

    public function handle(DiscordNotifier $discord): int
    {
        $minPayout = (int) Setting::current()->min_payout_amount;

        // 締め月（省略時は前月）と、その月末
        $closingMonth = $this->option('month') ?: now()->subMonthNoOverflow()->format('Y-m');
        $closingEnd = Carbon::parse($closingMonth.'-01')->endOfMonth();

        // 確定済み・未ピックアップ（payout未紐付け）で、締め月末までに確定した報酬を集計
        $groups = Reward::query()
            ->select('affiliate_id', DB::raw('SUM(reward_amount) AS total'), DB::raw('COUNT(*) AS cnt'))
            ->where('status', Reward::STATUS_CONFIRMED)
            ->whereNull('payout_id')
            ->where('confirmed_at', '<=', $closingEnd)
            ->groupBy('affiliate_id')
            ->havingRaw('SUM(reward_amount) >= ?', [$minPayout])
            ->get();

        $count = 0;
        $sum = 0;
        foreach ($groups as $g) {
            DB::transaction(function () use ($g, $closingMonth, $closingEnd) {
                $payout = Payout::create([
                    'affiliate_id' => $g->affiliate_id,
                    'closing_month' => $closingMonth,
                    'amount' => (int) $g->total,
                    'reward_count' => (int) $g->cnt,
                ]);

                Reward::where('affiliate_id', $g->affiliate_id)
                    ->where('status', Reward::STATUS_CONFIRMED)
                    ->whereNull('payout_id')
                    ->where('confirmed_at', '<=', $closingEnd)
                    ->update(['payout_id' => $payout->id]);
            });

            $count++;
            $sum += (int) $g->total;
        }

        $this->info("締め月 {$closingMonth}：{$count}名 / 合計 ".number_format($sum).'円 をピックアップしました。');

        if ($count > 0) {
            $discord->monthlyPayoutCreated($closingMonth, $count, $sum);
        }

        return self::SUCCESS;
    }
}
