<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * クリックログ（任意）。
 */
class Click extends Model
{
    protected $fillable = [
        'affiliate_id',
        'affiliate_code',
        'ip',
        'referer',
        'landing_url',
        'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }
}
