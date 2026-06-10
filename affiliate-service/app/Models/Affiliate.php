<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * アフィリエイター。
 */
class Affiliate extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_SUSPENDED = 'suspended';

    public static array $statusLabels = [
        self::STATUS_PENDING => '申請中',
        self::STATUS_APPROVED => '承認済み',
        self::STATUS_REJECTED => '却下',
        self::STATUS_SUSPENDED => '停止',
    ];

    protected $fillable = [
        'name',
        'email',
        'affiliate_code',
        'mypage_token',
        'bank_name',
        'bank_branch',
        'account_type',
        'account_number',
        'account_holder',
        'status',
        'commission_rate',
        'approved_at',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function statusLabel(): string
    {
        return self::$statusLabels[$this->status] ?? $this->status;
    }

    /**
     * 適用料率（個別設定があればそれ、なければ全体設定）。
     */
    public function effectiveRate(): float
    {
        if ($this->commission_rate !== null) {
            return (float) $this->commission_rate;
        }

        return (float) Setting::current()->commission_rate;
    }

    /**
     * ショップURL + ?affiliate=CODE。
     */
    public function affiliateUrl(): string
    {
        return config('affiliate.shop_url').'/?affiliate='.$this->affiliate_code;
    }

    public function mypageUrl(): string
    {
        return url('/mypage/'.$this->mypage_token);
    }

    /**
     * 一意なアフィリコードを生成。
     */
    public static function generateCode(): string
    {
        do {
            $code = Str::lower(Str::random(10));
        } while (static::where('affiliate_code', $code)->exists());

        return $code;
    }

    /**
     * 一意なマイページトークンを生成。
     */
    public static function generateToken(): string
    {
        do {
            $token = Str::random(40);
        } while (static::where('mypage_token', $token)->exists());

        return $token;
    }
}
