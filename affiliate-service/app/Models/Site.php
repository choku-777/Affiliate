<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * アンバサダー対象サイト（うましっぽ・馬肉特急 など）。
 */
class Site extends Model
{
    protected $fillable = [
        'code',
        'name',
        'shop_url',
        'commission_rate',
        'is_default',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'is_default' => 'boolean',
    ];

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }

    /**
     * このサイトの紹介URL（shop_url + ?affiliate=code）。
     */
    public function affiliateUrl(string $affiliateCode): string
    {
        return rtrim($this->shop_url, '/').'/?affiliate='.$affiliateCode;
    }

    /**
     * 既定サイト（無ければ最初の1件）。
     */
    public static function default(): ?self
    {
        return static::where('is_default', true)->first() ?? static::query()->orderBy('id')->first();
    }

    /**
     * site_code からサイトを引く（未知ならnull）。
     */
    public static function byCode(?string $code): ?self
    {
        return $code ? static::where('code', $code)->first() : null;
    }
}
