<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * アンバサダー。
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
        'sns',
        'sns_account',
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
        'sample_sent_at',
        'sample_sent_by',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'approved_at' => 'datetime',
        'sample_sent_at' => 'datetime',
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
     * メモ（備考）。新しい順。
     */
    public function notes(): HasMany
    {
        return $this->hasMany(AffiliateNote::class)->latest();
    }

    /**
     * サンプル申し込み。新しい順。
     */
    public function sampleRequests(): HasMany
    {
        return $this->hasMany(SampleRequest::class)->latest('id');
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
     * サンプル送付済みか。
     */
    public function hasSampleSent(): bool
    {
        return $this->sample_sent_at !== null;
    }

    /**
     * 有効なサンプル申し込み（取消以外）。1人1回のため最新の1件だけを見る。
     * これが存在する間は再申し込みできない。
     */
    public function activeSampleRequest(): ?SampleRequest
    {
        return $this->sampleRequests()
            ->where('status', '!=', SampleRequest::STATUS_CANCELLED)
            ->first();
    }

    /**
     * 適用料率（個別設定があればそれ、なければ全体設定）。
     */
    public function effectiveRate(?Site $site = null): float
    {
        if ($this->commission_rate !== null) {
            return (float) $this->commission_rate;
        }

        $site = $site ?? Site::default();

        return (float) ($site?->commission_rate ?? Setting::current()->commission_rate);
    }

    /**
     * 全サイトの紹介URL一覧（各要素 ['site' => Site, 'url' => string]）。
     */
    public function affiliateUrls(): array
    {
        return Site::query()->orderByDesc('is_default')->orderBy('id')->get()
            ->map(fn (Site $s) => ['site' => $s, 'url' => $s->affiliateUrl($this->affiliate_code)])
            ->all();
    }

    /**
     * 既定サイトの紹介URL（後方互換）。
     */
    public function affiliateUrl(): string
    {
        $site = Site::default();

        return $site
            ? $site->affiliateUrl($this->affiliate_code)
            : config('affiliate.shop_url').'/?affiliate='.$this->affiliate_code;
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
