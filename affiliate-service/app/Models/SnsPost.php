<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;

/**
 * アンバサダーが申告したSNS投稿。承認したものだけを公式サイトに掲載する。
 * URLの判定・正規化・埋め込みコードの生成をここに集約する。
 */
class SnsPost extends Model
{
    const PLATFORM_X = 'x';
    const PLATFORM_INSTAGRAM = 'instagram';
    const PLATFORM_TIKTOK = 'tiktok';

    public static array $platformLabels = [
        self::PLATFORM_X => 'X',
        self::PLATFORM_INSTAGRAM => 'Instagram',
        self::PLATFORM_TIKTOK => 'TikTok',
    ];

    const STATUS_PENDING = 'pending';   // 確認中
    const STATUS_CHECKED = 'checked';   // 確認済み（掲載は保留）
    const STATUS_APPROVED = 'approved'; // 掲載中
    const STATUS_REJECTED = 'rejected'; // 却下
    const STATUS_HIDDEN = 'hidden';     // 掲載停止

    public static array $statusLabels = [
        self::STATUS_PENDING => '確認中',
        self::STATUS_CHECKED => '確認済み',
        self::STATUS_APPROVED => '掲載中',
        self::STATUS_REJECTED => '却下',
        self::STATUS_HIDDEN => '非表示',
    ];

    /** アンバサダー本人に見せる表示（却下理由は見せない） */
    public static array $publicStatusLabels = [
        self::STATUS_PENDING => '確認中',
        self::STATUS_CHECKED => '確認済み',
        self::STATUS_APPROVED => '掲載中',
        self::STATUS_REJECTED => '非掲載',
        self::STATUS_HIDDEN => '非掲載',
    ];

    protected $fillable = [
        'affiliate_id',
        'sample_request_id',
        'platform',
        'post_url',
        'normalized_url',
        'post_id',
        'status',
        'note',
        'pr_checked_at',
        'pr_checked_by',
        'approved_at',
        'approved_by',
        'reject_reason',
        'sort_order',
        'site_id',
        'product_id',
        'product_name',
        'dog_breed',
        'dog_age',
        'dog_size',
        'dog_stage',
        'thumbnail_path',
        'thumbnail_fetched_at',
    ];

    protected $casts = [
        'pr_checked_at' => 'datetime',
        'approved_at' => 'datetime',
        'sort_order' => 'integer',
        'product_id' => 'integer',
        'thumbnail_fetched_at' => 'datetime',
    ];

    /**
     * アンバサダーが掲載に同意しているサイト（同意文が「うましっぽ」での掲載のため）。
     * ほかのサイトに広げる場合は、掲載先を明示した同意を取り直してから追加する。
     */
    public const CONSENTED_SITE_CODES = ['umashippo'];

    /** 公式サイト掲載用：犬の体格 */
    public static array $dogSizeLabels = [
        'small' => '小型犬',
        'medium' => '中型犬',
        'large' => '大型犬',
    ];

    /** 公式サイト掲載用：犬の年代 */
    public static array $dogStageLabels = [
        'puppy' => '子犬',
        'adult' => '成犬',
        'senior' => 'シニア',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function sampleRequest(): BelongsTo
    {
        return $this->belongsTo(SampleRequest::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * 掲載先サイト（入力欄の初期値用）。未設定なら既定サイト（うましっぽ）を候補にする。
     * 公式サイトへの掲載判定には使わない（掲載には掲載先の明示的な保存が必要）。
     */
    public function effectiveSite(): ?Site
    {
        return $this->site ?? Site::default();
    }

    /**
     * 公式サイトへの掲載に足りない項目（表示用の名前）。空なら掲載できる。
     * サムネイルは承認時に自動取得するため、ここでは見ない。犬の情報は任意なので見ない。
     */
    public function missingListingFields(): array
    {
        $missing = [];
        if (!$this->product_id) {
            $missing[] = '商品';
        }
        // 犬の情報（犬種・年齢・体格・年代）は任意。入っていればサイトに表示する

        return $missing;
    }

    public function hasThumbnail(): bool
    {
        return $this->thumbnail_path !== null
            && \App\Services\SnsThumbnailFetcher::isOwnPath($this->thumbnail_path)
            && is_file(storage_path('app/'.$this->thumbnail_path));
    }

    /**
     * 公式サイトに実際に表示される状態か（掲載中・アンバサダーの掲載同意あり・掲載先あり・掲載用の情報が揃っている・サムネイルあり）。
     */
    public function isListable(): bool
    {
        return $this->status === self::STATUS_APPROVED
            && $this->isPrChecked()
            && $this->affiliate?->hasMediaConsent() === true
            && in_array($this->site?->code, self::CONSENTED_SITE_CODES, true)
            && $this->missingListingFields() === []
            && $this->hasThumbnail();
    }

    public function dogSizeLabel(): ?string
    {
        return self::$dogSizeLabels[$this->dog_size] ?? null;
    }

    public function dogStageLabel(): ?string
    {
        return self::$dogStageLabels[$this->dog_stage] ?? null;
    }

    /**
     * 公式サイトに出せる投稿（掲載中・アンバサダーの掲載同意あり・掲載先が明示されている・掲載情報が揃っている・サムネイルあり）を、指定サイト向けに。
     * 掲載先が未設定の投稿は、どのサイトにも出さない（商品IDはサイトごとに意味が違うため推測しない）。
     */
    public function scopeListedOn(Builder $query, Site $site): Builder
    {
        if (!in_array($site->code, self::CONSENTED_SITE_CODES, true)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->published()
            ->where('site_id', $site->id)
            ->whereNotNull('pr_checked_at')
            ->whereHas('affiliate', fn (Builder $q) => $q->whereNotNull('media_consent_at'))
            ->whereNotNull('product_id')
            ->whereNotNull('thumbnail_path');
    }

    /**
     * 掲載中の投稿を表示順（同順なら承認が新しい順）で。
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->orderBy('sort_order')
            ->orderByDesc('approved_at')
            ->orderByDesc('id'); // 同順位の並びを確定させる（分割取得で重複・取りこぼしが起きないように）
    }

    public function platformLabel(): string
    {
        return self::$platformLabels[$this->platform] ?? $this->platform;
    }

    public function statusLabel(): string
    {
        return self::$statusLabels[$this->status] ?? $this->status;
    }

    public function publicStatusLabel(): string
    {
        return self::$publicStatusLabels[$this->status] ?? $this->status;
    }

    public function isPrChecked(): bool
    {
        return $this->pr_checked_at !== null;
    }

    /**
     * 投稿URLを解析して platform / normalized_url / post_id を返す。対応外なら null。
     * クエリ文字列や末尾のスラッシュの違いを吸収し、同じ投稿を同じURLに揃える。
     */
    public static function parseUrl(string $url): ?array
    {
        $parts = parse_url(trim($url));
        if (!$parts || empty($parts['host'])) {
            return null;
        }
        $host = preg_replace('/^(www|m|mobile)\./', '', strtolower($parts['host']));
        $path = $parts['path'] ?? '';

        // X（旧Twitter）: https://x.com/ユーザー名/status/数字
        if (in_array($host, ['x.com', 'twitter.com'], true)
            && preg_match('#^/([A-Za-z0-9_]{1,15})/status(?:es)?/(\d+)#', $path, $m)) {
            return [
                'platform' => self::PLATFORM_X,
                'normalized_url' => "https://x.com/{$m[1]}/status/{$m[2]}",
                'post_id' => $m[2],
            ];
        }

        // Instagram: https://www.instagram.com/p/コード/ または /reel/コード/
        if ($host === 'instagram.com'
            && preg_match('#^/(p|reel|reels)/([A-Za-z0-9_-]+)#', $path, $m)) {
            $type = $m[1] === 'reels' ? 'reel' : $m[1];

            return [
                'platform' => self::PLATFORM_INSTAGRAM,
                'normalized_url' => "https://www.instagram.com/{$type}/{$m[2]}/",
                'post_id' => $m[2],
            ];
        }

        // TikTok: https://www.tiktok.com/@ユーザー名/video/数字
        if ($host === 'tiktok.com'
            && preg_match('#^/@([A-Za-z0-9_.]+)/video/(\d+)#', $path, $m)) {
            return [
                'platform' => self::PLATFORM_TIKTOK,
                'normalized_url' => "https://www.tiktok.com/@{$m[1]}/video/{$m[2]}",
                'post_id' => $m[2],
            ];
        }

        return null;
    }

    /**
     * TikTokアプリの「リンクをコピー」で出る短縮URLか。
     */
    public static function isTiktokShortUrl(string $url): bool
    {
        $host = strtolower(parse_url(trim($url), PHP_URL_HOST) ?? '');
        $path = parse_url(trim($url), PHP_URL_PATH) ?? '';

        return in_array($host, ['vt.tiktok.com', 'vm.tiktok.com'], true)
            || (in_array($host, ['tiktok.com', 'www.tiktok.com'], true) && str_starts_with($path, '/t/'));
    }

    /**
     * 短縮URLのリダイレクト先（本来の投稿URL）を返す。取得できなければ null。
     */
    public static function resolveRedirect(string $url): ?string
    {
        try {
            $response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->withOptions(['allow_redirects' => ['max' => 5]])
                ->timeout(8)
                ->get($url);

            return (string) $response->effectiveUri();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * 貼られたURLを解析する。TikTokの短縮URLは本来のURLに解決する。
     * 成功: ['platform','normalized_url','post_id'] / 失敗: エラーメッセージ（文字列）
     */
    public static function analyze(string $url): array|string
    {
        $url = trim($url);

        if (self::isTiktokShortUrl($url)) {
            $resolved = self::resolveRedirect($url);
            if (!$resolved) {
                return 'TikTokのリンクを確認できませんでした。動画ページのURL（tiktok.com/@…/video/…）を貼ってください。';
            }
            $url = $resolved;
        }

        $parsed = self::parseUrl($url);
        if (!$parsed) {
            return 'X・Instagram・TikTok の投稿URLのみ受け付けています。投稿ページのURLをそのまま貼ってください。';
        }

        if (self::where('normalized_url', $parsed['normalized_url'])->exists()) {
            return 'この投稿はすでに登録されています。';
        }

        return $parsed;
    }

    /**
     * 公式の埋め込みコード（各SNSのスクリプトは別途読み込む）。
     */
    public function embedHtml(): string
    {
        $url = e($this->normalized_url);

        return match ($this->platform) {
            self::PLATFORM_X => '<blockquote class="twitter-tweet"><a href="'.$url.'"></a></blockquote>',
            // data-instgrm-captioned を付けないと本文（キャプション）が表示されない。#PR の確認に必要
            self::PLATFORM_INSTAGRAM => '<blockquote class="instagram-media" data-instgrm-captioned data-instgrm-permalink="'.$url.'" data-instgrm-version="14" style="max-width:540px;width:100%;"></blockquote>',
            self::PLATFORM_TIKTOK => '<blockquote class="tiktok-embed" cite="'.$url.'" data-video-id="'.e($this->post_id).'" style="max-width:605px;min-width:325px;"><section></section></blockquote>',
            default => '',
        };
    }

    /**
     * 埋め込みに必要な各SNSのスクリプトURL。
     */
    public static array $embedScripts = [
        self::PLATFORM_X => 'https://platform.twitter.com/widgets.js',
        self::PLATFORM_INSTAGRAM => 'https://www.instagram.com/embed.js',
        self::PLATFORM_TIKTOK => 'https://www.tiktok.com/embed.js',
    ];
}
