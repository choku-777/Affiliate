<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * サンプル商品の申し込み（1件＝1申込）。
 * 送付先は申込時点の内容を保存し、発送後は伝票番号を記録する。
 */
class SampleRequest extends Model
{
    const STATUS_REQUESTED = 'requested';         // 未発送（申込受付）
    const STATUS_CSV_EXPORTED = 'csv_exported';   // B2用CSV出力済
    const STATUS_SHIPPED = 'shipped';             // 発送済
    const STATUS_CANCELLED = 'cancelled';         // 取消

    public static array $statusLabels = [
        self::STATUS_REQUESTED => '未発送',
        self::STATUS_CSV_EXPORTED => 'CSV出力済',
        self::STATUS_SHIPPED => '発送済',
        self::STATUS_CANCELLED => '取消',
    ];

    /** お客様管理番号の接頭辞（B2の発行済データから自分の申込を見分けるために使う） */
    const CODE_PREFIX = 'SMP-';

    protected $fillable = [
        'affiliate_id',
        'product_name',
        'recipient_name',
        'postal_code',
        'prefecture',
        'city',
        'address1',
        'address2',
        'phone',
        'note',
        'status',
        'tracking_number',
        'requested_at',
        'csv_downloaded_at',
        'shipped_at',
        'shipped_mail_sent_at',
        'shipped_by',
        'sns_consent_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'csv_downloaded_at' => 'datetime',
        'shipped_at' => 'datetime',
        'shipped_mail_sent_at' => 'datetime',
        'sns_consent_at' => 'datetime',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    /**
     * この申込に対するSNS投稿の申告。新しい順。
     */
    public function snsPosts(): HasMany
    {
        return $this->hasMany(SnsPost::class)->latest('id');
    }

    /**
     * SNS投稿の期限（発送日＋設定日数）。未発送なら null。
     */
    public function snsDeadline(int $days): ?Carbon
    {
        return $this->shipped_at ? $this->shipped_at->copy()->addDays($days)->endOfDay() : null;
    }

    /**
     * SNS投稿の状況。管理画面の「投稿」列とマイページの案内に使う。
     * none=未発送 / unposted=未申告 / overdue=期限切れ / pending / approved / rejected
     */
    public function snsStatus(int $days): string
    {
        $latest = $this->snsPosts->first();
        if ($latest) {
            return match ($latest->status) {
                SnsPost::STATUS_APPROVED => 'approved',
                SnsPost::STATUS_CHECKED => 'checked',
                SnsPost::STATUS_PENDING => 'pending',
                SnsPost::STATUS_HIDDEN => 'approved',
                default => 'rejected',
            };
        }
        if ($this->status !== self::STATUS_SHIPPED) {
            return 'none';
        }
        $deadline = $this->snsDeadline($days);

        return ($deadline && now()->greaterThan($deadline)) ? 'overdue' : 'unposted';
    }

    /**
     * B2クラウドに渡すお客様管理番号（例: SMP-000123）。
     * 発行済データを取り込むとき、この番号で申込を特定する。
     */
    public function managementCode(): string
    {
        return self::CODE_PREFIX.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * お客様管理番号から申込IDを取り出す。該当形式でなければ null。
     */
    public static function idFromManagementCode(string $code): ?int
    {
        $code = trim($code);
        if (!preg_match('/\A'.preg_quote(self::CODE_PREFIX, '/').'(\d{1,10})\z/', $code, $m)) {
            return null;
        }

        return (int) $m[1];
    }

    /**
     * 発送済みとして扱うか（未発送・CSV出力済も「申込あり」に含める）。
     */
    public function isActive(): bool
    {
        return $this->status !== self::STATUS_CANCELLED;
    }

    public function statusLabel(): string
    {
        return self::$statusLabels[$this->status] ?? $this->status;
    }

    /**
     * 伝票番号を読みやすく4桁区切りにする（3910-5932-1623）。
     */
    public function formattedTrackingNumber(): ?string
    {
        if (!$this->tracking_number) {
            return null;
        }

        return implode('-', str_split($this->tracking_number, 4));
    }

    /**
     * ヤマト運輸の荷物追跡URL（伝票番号はハイフンなしで渡す）。
     */
    public function trackingUrl(): ?string
    {
        if (!$this->tracking_number) {
            return null;
        }

        return 'https://toi.kuronekoyamato.co.jp/cgi-bin/tneko?number='.$this->tracking_number;
    }

    /**
     * 送付先の住所（都道府県＋市区町村＋番地）。建物名は別項目として扱う。
     */
    public function fullAddress(): string
    {
        return $this->prefecture.$this->city.$this->address1;
    }
}
