<?php

namespace Plugin\Affiliate\Service;

/**
 * プラグイン設定を .env から読み取る。
 * services.yaml の env プロセッサに依存せず、環境差でコンテナ構築が壊れないようにする。
 */
class Config
{
    public function apiUrl(): ?string
    {
        $value = self::env('AFFILIATE_API_URL');

        return $value !== null ? rtrim($value, '/') : null;
    }

    public function apiKey(): ?string
    {
        return self::env('AFFILIATE_API_KEY');
    }

    public function cookieDays(): int
    {
        $days = (int) self::env('AFFILIATE_COOKIE_DAYS');

        return $days > 0 ? $days : 30;
    }

    public function trackClicks(): bool
    {
        $value = self::env('AFFILIATE_TRACK_CLICKS');

        return $value !== null && filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private static function env(string $key): ?string
    {
        if (array_key_exists($key, $_SERVER) && $_SERVER[$key] !== '') {
            return (string) $_SERVER[$key];
        }
        if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
            return (string) $_ENV[$key];
        }
        $value = getenv($key);

        return ($value !== false && $value !== '') ? $value : null;
    }
}
