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
        'amount',
        'reward_count',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'reward_count' => 'integer',
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
}
