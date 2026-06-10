<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 成果（1注文＝1レコード）。
 */
class Reward extends Model
{
    const STATUS_PENDING = 'pending';     // 未確定
    const STATUS_CONFIRMED = 'confirmed'; // 確定（支払い対象）
    const STATUS_CANCELLED = 'cancelled'; // 取消（キャンセル/返品）
    const STATUS_PAID = 'paid';           // 支払済

    public static array $statusLabels = [
        self::STATUS_PENDING => '未確定',
        self::STATUS_CONFIRMED => '確定',
        self::STATUS_CANCELLED => '取消',
        self::STATUS_PAID => '支払済',
    ];

    protected $fillable = [
        'affiliate_id',
        'order_no',
        'order_total',
        'rate_applied',
        'reward_amount',
        'status',
        'order_status_id',
        'converted_at',
        'confirmed_at',
        'paid_at',
        'payout_id',
    ];

    protected $casts = [
        'order_total' => 'decimal:2',
        'rate_applied' => 'decimal:2',
        'reward_amount' => 'integer',
        'order_status_id' => 'integer',
        'converted_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(Payout::class);
    }

    public function statusLabel(): string
    {
        return self::$statusLabels[$this->status] ?? $this->status;
    }
}
