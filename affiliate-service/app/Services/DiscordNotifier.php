<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Payout;
use App\Models\Reward;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Discord（Webhook）へアフィリエイトの状態を通知する。
 * Webクライアントは副作用扱い：送信失敗してもメイン処理は止めない（内部でtry/catch）。
 * DISCORD_WEBHOOK_URL 未設定なら何もしない。
 */
class DiscordNotifier
{
    private const COLOR_REGISTER = 0x3498DB;   // 青：新規登録
    private const COLOR_CONVERSION = 0x2ECC71; // 緑：成果発生
    private const COLOR_PAYOUT = 0xF1C40F;     // 金：支払い
    private const COLOR_CANCEL = 0xE74C3C;     // 赤：取消
    private const COLOR_REMIND = 0xE67E22;     // 橙：リマインド

    /** 新規アフィリエイター登録（承認待ち） */
    public function affiliateRegistered(Affiliate $affiliate): void
    {
        $this->send('🆕 新規アフィリエイター登録', self::COLOR_REGISTER, [
            ['name' => '氏名', 'value' => (string) $affiliate->name, 'inline' => true],
            ['name' => 'メール', 'value' => (string) $affiliate->email, 'inline' => true],
            ['name' => 'ステータス', 'value' => '承認待ち', 'inline' => true],
        ], '管理画面で承認してください。');
    }

    /** 成果発生（コンバージョン） */
    public function conversionOccurred(Reward $reward): void
    {
        $reward->loadMissing(['affiliate', 'site']);
        $this->send('💰 成果発生', self::COLOR_CONVERSION, [
            ['name' => 'サイト', 'value' => optional($reward->site)->name ?? '—', 'inline' => true],
            ['name' => 'アフィリエイター', 'value' => optional($reward->affiliate)->name ?? '—', 'inline' => true],
            ['name' => '注文番号', 'value' => (string) $reward->order_no, 'inline' => true],
            ['name' => '注文金額', 'value' => number_format((float) $reward->order_total).'円', 'inline' => true],
            ['name' => '報酬', 'value' => number_format((int) $reward->reward_amount).'円', 'inline' => true],
        ]);
    }

    /** 成果の取消（自動／手動） */
    public function rewardCancelled(Reward $reward, string $reason): void
    {
        $reward->loadMissing(['affiliate', 'site']);
        $this->send('⚠️ 成果の取消', self::COLOR_CANCEL, [
            ['name' => 'サイト', 'value' => optional($reward->site)->name ?? '—', 'inline' => true],
            ['name' => 'アフィリエイター', 'value' => optional($reward->affiliate)->name ?? '—', 'inline' => true],
            ['name' => '注文番号', 'value' => (string) $reward->order_no, 'inline' => true],
            ['name' => '報酬', 'value' => number_format((int) $reward->reward_amount).'円', 'inline' => true],
            ['name' => '理由', 'value' => $reason, 'inline' => true],
        ]);
    }

    /** 支払い実行 */
    public function paymentExecuted(Payout $payout): void
    {
        $payout->loadMissing('affiliate');
        $this->send('🏦 支払い実行', self::COLOR_PAYOUT, [
            ['name' => 'アフィリエイター', 'value' => optional($payout->affiliate)->name ?? '—', 'inline' => true],
            ['name' => '金額', 'value' => number_format((int) $payout->amount).'円', 'inline' => true],
            ['name' => '件数', 'value' => $payout->reward_count.'件', 'inline' => true],
        ]);
    }

    /** 月次支払いリスト作成（毎月1日のピックアップ） */
    public function monthlyPayoutCreated(string $month, int $count, int $total): void
    {
        $this->send('📋 月次支払いリスト作成', self::COLOR_REMIND, [
            ['name' => '締め月', 'value' => $month, 'inline' => true],
            ['name' => '対象人数', 'value' => $count.'名', 'inline' => true],
            ['name' => '合計', 'value' => number_format($total).'円', 'inline' => true],
        ], '管理画面からCSVをダウンロードして振込してください（翌月10日まで）。');
    }

    /**
     * 埋め込みメッセージを送信する。
     */
    private function send(string $title, int $color, array $fields, ?string $footer = null): void
    {
        $url = config('services.discord.webhook_url');
        if (!$url) {
            return;
        }

        $embed = [
            'title' => $title,
            'color' => $color,
            'fields' => $fields,
            'timestamp' => now()->toIso8601String(),
        ];
        if ($footer !== null) {
            $embed['footer'] = ['text' => $footer];
        }

        try {
            Http::timeout(5)->post($url, ['embeds' => [$embed]]);
        } catch (\Throwable $e) {
            Log::warning('Discord通知の送信に失敗しました: '.$e->getMessage());
        }
    }
}
