<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * アンバサダーの全体設定（単一レコード id=1）。
 */
class Setting extends Model
{
    protected $fillable = [
        'commission_rate',
        'confirm_after_days',
        'min_payout_amount',
        'cookie_lifetime_days',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'confirm_after_days' => 'integer',
        'min_payout_amount' => 'integer',
        'cookie_lifetime_days' => 'integer',
    ];

    /**
     * 設定（単一レコード）を取得。なければ既定値で作成。
     */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'commission_rate' => 5.00,
            'confirm_after_days' => 30,
            'min_payout_amount' => 5000,
            'cookie_lifetime_days' => 30,
        ]);
    }
}
