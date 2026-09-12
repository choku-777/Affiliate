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
    ];

    protected $casts = [
        'pr_checked_at' => 'datetime',
        'approved_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function sampleRequest(): BelongsTo
    {
        return $this->belongsTo(SampleRequest::class);
    }

    /**
     * 掲載中の投稿を表示順（同順なら承認が新しい順）で。
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->orderBy('sort_order')
            ->orderByDesc('approved_at');
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
