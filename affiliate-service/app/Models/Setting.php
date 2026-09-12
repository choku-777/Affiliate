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
        'sample_product_name',
        'sample_product_url',
        'sample_invoice_item_name',
        'sample_request_enabled',
        'discord_sample_webhook_url',
        'sns_hashtag',
        'sns_account_x',
        'sns_account_instagram',
        'sns_account_tiktok',
        'sns_post_deadline_days',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'confirm_after_days' => 'integer',
        'min_payout_amount' => 'integer',
        'cookie_lifetime_days' => 'integer',
        'sample_request_enabled' => 'boolean',
        'sns_post_deadline_days' => 'integer',
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
            'sample_product_name' => '初回限定トライアル',
            'sample_product_url' => 'https://umashippo.jp/user_data/trial',
            'sample_invoice_item_name' => 'ペットフードトライアルセット',
            'sample_request_enabled' => true,
            'sns_hashtag' => '#うましっぽ',
            'sns_post_deadline_days' => 14,
        ]);
    }
}
