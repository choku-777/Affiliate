<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Click;
use App\Models\Reward;
use App\Models\Site;
use App\Services\RewardCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * EC-CUBE プラグインからの postback 受信口。
 * type = conversion / order_status / click を判別して処理する。
 */
class EventController extends Controller
{
    // EC-CUBE の OrderStatus（キャンセル/返品）
    const ORDER_STATUS_CANCEL = 3;
    const ORDER_STATUS_RETURNED = 9;

    public function __construct(
        private RewardCalculator $calculator,
        private \App\Services\DiscordNotifier $discord,
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $type = (string) $request->input('type');
        $payload = (array) $request->input('payload', []);

        switch ($type) {
            case 'conversion':
                return $this->handleConversion($payload);
            case 'order_status':
                return $this->handleOrderStatus($payload);
            case 'click':
                return $this->handleClick($payload);
            default:
                // 未知のtypeでも再送ループを避けるため200で受ける
                return response()->json(['status' => 'ignored', 'reason' => 'unknown type']);
        }
    }

    private function handleConversion(array $payload): JsonResponse
    {
        $code = $payload['affiliate_code'] ?? null;
        $orderNo = $payload['order_no'] ?? null;

        if (!$code || !$orderNo) {
            return response()->json(['status' => 'ignored', 'reason' => 'missing fields']);
        }

        // 既に記録済みなら冪等に200
        if (Reward::where('order_no', $orderNo)->exists()) {
            return response()->json(['status' => 'duplicate']);
        }

        $affiliate = Affiliate::where('affiliate_code', $code)
            ->where('status', Affiliate::STATUS_APPROVED)
            ->first();

        // 未承認・存在しないコードは成果にしない（200で受信完了とする）
        if (!$affiliate) {
            return response()->json(['status' => 'ignored', 'reason' => 'affiliate not approved']);
        }

        $site = Site::byCode($payload['site_code'] ?? null) ?? Site::default();
        $orderTotal = (float) ($payload['order_total'] ?? 0);
        $rate = $affiliate->effectiveRate($site);
        $convertedAt = !empty($payload['order_date'])
            ? Carbon::parse($payload['order_date'])
            : now();

        $reward = Reward::create([
            'affiliate_id' => $affiliate->id,
            'site_id' => $site?->id,
            'order_no' => $orderNo,
            'order_total' => $orderTotal,
            'rate_applied' => $rate,
            'reward_amount' => $this->calculator->calculate($orderTotal, $rate),
            'status' => Reward::STATUS_PENDING,
            'converted_at' => $convertedAt,
        ]);

        $this->discord->conversionOccurred($reward);

        return response()->json(['status' => 'ok']);
    }

    private function handleOrderStatus(array $payload): JsonResponse
    {
        $orderNo = $payload['order_no'] ?? null;
        $statusId = isset($payload['order_status_id']) ? (int) $payload['order_status_id'] : null;

        if (!$orderNo) {
            return response()->json(['status' => 'ignored', 'reason' => 'missing order_no']);
        }

        $reward = Reward::where('order_no', $orderNo)->first();
        if (!$reward) {
            return response()->json(['status' => 'ignored', 'reason' => 'no reward']);
        }

        $reward->order_status_id = $statusId;

        $isCancelled = in_array($statusId, [self::ORDER_STATUS_CANCEL, self::ORDER_STATUS_RETURNED], true);
        $wasCancelled = $reward->status === Reward::STATUS_CANCELLED;

        // 支払済はそのまま（実支払い後の取消は手動対応）
        if ($reward->status !== Reward::STATUS_PAID) {
            if ($isCancelled) {
                $reward->status = Reward::STATUS_CANCELLED;
            } elseif ($reward->status === Reward::STATUS_CANCELLED) {
                // 取消からの復帰
                $reward->status = Reward::STATUS_PENDING;
            }
        }

        $reward->save();

        // 新たに取消になった場合のみ通知
        if ($isCancelled && ! $wasCancelled && $reward->status === Reward::STATUS_CANCELLED) {
            $this->discord->rewardCancelled($reward, 'EC-CUBEでキャンセル／返品');
        }

        return response()->json(['status' => 'ok']);
    }

    private function handleClick(array $payload): JsonResponse
    {
        $code = $payload['affiliate_code'] ?? null;
        if (!$code) {
            return response()->json(['status' => 'ignored']);
        }

        $affiliate = Affiliate::where('affiliate_code', $code)->first();
        $site = Site::byCode($payload['site_code'] ?? null) ?? Site::default();

        Click::create([
            'affiliate_id' => $affiliate?->id,
            'site_id' => $site?->id,
            'affiliate_code' => $code,
            'ip' => $payload['ip'] ?? null,
            'referer' => isset($payload['referer']) ? mb_substr((string) $payload['referer'], 0, 1000) : null,
            'landing_url' => isset($payload['landing_url']) ? mb_substr((string) $payload['landing_url'], 0, 1000) : null,
            'clicked_at' => !empty($payload['clicked_at']) ? Carbon::parse($payload['clicked_at']) : now(),
        ]);

        return response()->json(['status' => 'ok']);
    }
}
