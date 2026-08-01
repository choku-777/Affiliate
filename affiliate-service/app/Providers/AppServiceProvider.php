<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** 本アプリが前提とするタイムゾーン（月次集計・締めがこの前提で動く） */
    private const EXPECTED_TIMEZONE = 'Asia/Tokyo';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->warnIfTimezoneMisconfigured();
    }

    /**
     * タイムゾーンが日本時間になっていなければ警告を残す。
     *
     * 素のLaravelの config/app.php は 'timezone' => 'UTC' と直書きされており、
     * .env の APP_TIMEZONE を見ない。その状態だと月次集計・締め月の算出・
     * 成果の日付絞り込みが9時間ずれるため、設定漏れに気づけるようにする。
     * 対処方法は DEPLOY-xserver.md を参照。
     *
     * ログは error レベルで出す。本番の .env が LOG_LEVEL=error のため、
     * warning では記録されずに捨てられてしまうため。
     */
    private function warnIfTimezoneMisconfigured(): void
    {
        $actual = config('app.timezone');

        if ($actual === self::EXPECTED_TIMEZONE) {
            return;
        }

        Log::error(sprintf(
            'タイムゾーン設定が想定と異なります（期待: %s / 実際: %s）。'
            .'config/app.php の timezone が env("APP_TIMEZONE") を参照しているか確認してください。'
            .'このままだと月次集計・締め月の算出がずれます。',
            self::EXPECTED_TIMEZONE,
            $actual ?: '(未設定)'
        ));
    }
}
