<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 報酬の一括支払い記録。
 */
class Payout extends Model
{
    protected $fillable = [
        'affiliate_id',
        'closing_month',
        'amount',
        'reward_count',
        'csv_downloaded_at',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'reward_count' => 'integer',
        'csv_downloaded_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }

    /** CSVダウンロード済みか（①フラグ） */
    public function isCsvDownloaded(): bool
    {
        return $this->csv_downloaded_at !== null;
    }

    /** 入金済みか（②フラグ） */
    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }
}
