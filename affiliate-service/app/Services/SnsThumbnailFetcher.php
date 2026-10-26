<?php

namespace App\Services;

use App\Models\SnsPost;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 公式サイト掲載用のサムネイルを取得して保存する。
 *
 * - Instagram: 投稿ページの共有用画像（og:image）。リンクプレビュー用の User-Agent でないと返ってこない
 * - TikTok: 公式の oEmbed（認証不要）の thumbnail_url
 * どちらも画像URLは数日で失効するため、取得した時点で storage/app/sns_thumbs/ に保存する。
 * 取得に失敗した場合は管理画面から手動でアップロードする。
 *
 * 安全のため:
 * - リダイレクトはたどらない（許可したホスト以外へ転送されないように）
 * - 受信量は読みながら上限で打ち切る
 * - 画像は GD で描き直して JPEG で保存する（位置情報などの付帯情報を残さない）
 * - 新しいファイルを保存してDBを切り替えてから古いファイルを消す（途中で失敗しても元の画像を失わない）
 */
class SnsThumbnailFetcher
{
    private const DIR = 'sns_thumbs';

    private const MAX_IMAGE_BYTES = 3 * 1024 * 1024;

    private const MAX_PAGE_BYTES = 2 * 1024 * 1024;

    private const MAX_PIXELS = 4000 * 4000;

    /** 保存時の長辺の上限（サイト表示は200px前後なので十分） */
    private const MAX_EDGE = 1080;

    /** 1回の取得（投稿ページ＋画像）全体にかける時間の上限（秒）。遅い相手に処理を占有されないように */
    private const DEADLINE_SECONDS = 15;

    /** 今回の取得全体の期限（microtime）。fetch() の開始時に決める */
    private float $deadline = 0.0;

    private const PREVIEW_UA = 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)';

    /** 画像の取得を許可するホスト（末尾一致）。投稿ページに書かれた任意のURLを取りに行かないための制限 */
    private const ALLOWED_IMAGE_HOSTS = [
        'cdninstagram.com',
        'fbcdn.net',
        'tiktokcdn.com',
        'tiktokcdn-us.com',
        'ibyteimg.com',
    ];

    /**
     * 自動取得して保存する。成功すれば true。
     */
    public function fetch(SnsPost $post): bool
    {
        $this->deadline = microtime(true) + self::DEADLINE_SECONDS;

        try {
            $imageUrl = match ($post->platform) {
                SnsPost::PLATFORM_INSTAGRAM => $this->instagramImageUrl($post->normalized_url),
                SnsPost::PLATFORM_TIKTOK => $this->tiktokImageUrl($post->normalized_url),
                default => null,
            };
            if (!$imageUrl || !$this->isAllowedImageUrl($imageUrl)) {
                return false;
            }

            $bytes = $this->boundedGet($imageUrl, self::MAX_IMAGE_BYTES);

            return $bytes !== null && $this->store($post, $bytes);
        } catch (\Throwable $e) {
            Log::error('[SnsThumbnail] 取得に失敗しました', ['sns_post_id' => $post->id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * 手動でアップロードされた画像を保存する。
     */
    public function storeUploaded(SnsPost $post, UploadedFile $file): bool
    {
        if ($file->getSize() > self::MAX_IMAGE_BYTES) {
            return false;
        }

        return $this->store($post, (string) file_get_contents($file->getRealPath()));
    }

    private function instagramImageUrl(string $postUrl): ?string
    {
        $html = $this->boundedGet($postUrl, self::MAX_PAGE_BYTES, ['User-Agent' => self::PREVIEW_UA]);
        if ($html === null) {
            return null;
        }

        // 属性の順序・引用符の違いに左右されないよう、meta タグを1つずつ見る
        if (!preg_match_all('/<meta\b[^>]*>/i', $html, $tags)) {
            return null;
        }
        foreach ($tags[0] as $tag) {
            if (preg_match('/\bproperty\s*=\s*["\']og:image["\']/i', $tag)
                && preg_match('/\bcontent\s*=\s*["\']([^"\']+)["\']/i', $tag, $m)) {
                return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5);
            }
        }

        return null;
    }

    private function tiktokImageUrl(string $postUrl): ?string
    {
        $body = $this->boundedGet('https://www.tiktok.com/oembed?url='.rawurlencode($postUrl), self::MAX_PAGE_BYTES);
        $json = $body !== null ? json_decode($body, true) : null;

        return is_array($json) && is_string($json['thumbnail_url'] ?? null) ? $json['thumbnail_url'] : null;
    }

    /**
     * リダイレクトをたどらず、受信しながら上限で打ち切って本文を返す。失敗・超過は null。
     */
    private function boundedGet(string $url, int $maxBytes, array $headers = []): ?string
    {
        // 待ち時間は、取得全体の期限までの残り時間に収める
        $remaining = $this->deadline - microtime(true);
        if ($remaining <= 1) {
            return null;
        }
        $wait = min(10.0, $remaining);

        $response = Http::withHeaders($headers)
            ->withoutRedirecting()
            ->connectTimeout((int) max(1, min(5, floor($wait))))
            ->timeout((int) max(1, floor($wait)))
            ->withOptions(['stream' => true, 'read_timeout' => $wait])
            ->get($url);

        $stream = $response->toPsrResponse()->getBody();
        try {
            if ($response->status() !== 200) {
                return null;
            }

            $length = $response->header('Content-Length');
            if ($length !== '' && (int) $length > $maxBytes) {
                return null;
            }

            $buffer = '';
            while (!$stream->eof()) {
                $buffer .= $stream->read(65536);
                if (strlen($buffer) > $maxBytes || microtime(true) > $this->deadline) {
                    return null;
                }
            }

            return $buffer;
        } finally {
            $stream->close();
        }
    }

    private function isAllowedImageUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (($parts['scheme'] ?? '') !== 'https' || empty($parts['host']) || isset($parts['port'])) {
            return false;
        }
        $host = strtolower($parts['host']);
        foreach (self::ALLOWED_IMAGE_HOSTS as $allowed) {
            if ($host === $allowed || str_ends_with($host, '.'.$allowed)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 画像として読み込めることを確認し、縮小・描き直して JPEG で保存する。
     */
    private function store(SnsPost $post, string $bytes): bool
    {
        if ($bytes === '' || strlen($bytes) > self::MAX_IMAGE_BYTES) {
            return false;
        }
        if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
            Log::error('[SnsThumbnail] PHP の GD 拡張が無いため画像を保存できません');

            return false;
        }

        $info = @getimagesizefromstring($bytes);
        if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)
            || $info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > self::MAX_PIXELS) {
            return false;
        }

        $src = @imagecreatefromstring($bytes);
        if ($src === false) {
            return false;
        }

        // 長辺を上限まで縮小し、透過部分は白で埋める（JPEGは透過を持てないため）
        $w = imagesx($src);
        $h = imagesy($src);
        $scale = min(1, self::MAX_EDGE / max($w, $h));
        $dw = max(1, (int) round($w * $scale));
        $dh = max(1, (int) round($h * $scale));
        $dst = imagecreatetruecolor($dw, $dh);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $dw, $dh, $w, $h);
        imagedestroy($src);

        ob_start();
        $ok = imagejpeg($dst, null, 85);
        $jpeg = (string) ob_get_clean();
        imagedestroy($dst);
        if (!$ok || $jpeg === '') {
            return false;
        }

        File::ensureDirectoryExists(storage_path('app/'.self::DIR));

        // 毎回新しい名前で保存し、DBを切り替えてから古いファイルを消す
        $relative = self::DIR.'/'.$post->id.'_'.now()->format('YmdHis').'_'.bin2hex(random_bytes(4)).'.jpg';
        $absolute = storage_path('app/'.$relative);
        if (File::put($absolute, $jpeg) !== strlen($jpeg)) {
            File::delete($absolute);

            return false;
        }

        $old = $post->thumbnail_path;
        try {
            $post->update([
                'thumbnail_path' => $relative,
                'thumbnail_fetched_at' => now(),
            ]);
        } catch (\Throwable $e) {
            File::delete($absolute);
            throw $e;
        }

        if ($old && $old !== $relative && self::isOwnPath($old)) {
            File::delete(storage_path('app/'.$old));
        }

        return true;
    }

    /**
     * このクラスが保存したファイルのパスか（sns_thumbs 直下・パス区切りや .. を含まない）。
     */
    public static function isOwnPath(string $path): bool
    {
        return (bool) preg_match('#^'.self::DIR.'/[A-Za-z0-9_]+\.jpg$#', $path);
    }
}
