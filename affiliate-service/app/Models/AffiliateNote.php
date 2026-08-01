<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * アンバサダーのメモ（備考）。追記型で、編集はせず履歴として残す。
 */
class AffiliateNote extends Model
{
    protected $fillable = [
        'affiliate_id',
        'body',
        'created_by',
        'created_by_name',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    /**
     * 表示用の投稿者名（名前が無ければメール）。
     */
    public function authorLabel(): string
    {
        return $this->created_by_name ?: ($this->created_by ?: '不明');
    }
}
