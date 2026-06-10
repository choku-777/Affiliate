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
        'last_name',
        'first_name',
        'last_name_kana',
        'first_name_kana',
        'email',
        'password',
        'phone',
        'birth_date',
        'gender',
        'postal_code',
        'prefecture',
        'city',
        'address1',
        'address2',
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

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'approved_at' => 'datetime',
        'birth_date' => 'date',
        'password' => 'hashed',
    ];

    public static array $genderLabels = [
        'male' => '男性',
        'female' => '女性',
    ];

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }

    /**
     * 確定済み（未払い）報酬の合計額。
     */
    public function confirmedUnpaidTotal(): int
    {
        return (int) $this->rewards()
            ->where('status', Reward::STATUS_CONFIRMED)
            ->sum('reward_amount');
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
