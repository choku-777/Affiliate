<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', [
            'setting' => Setting::current(),
            'sites' => Site::query()->orderByDesc('is_default')->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'confirm_after_days' => ['required', 'integer', 'min:0', 'max:365'],
            'min_payout_amount' => ['required', 'integer', 'min:0'],
            'cookie_lifetime_days' => ['required', 'integer', 'min:1', 'max:365'],
            'site_rate' => ['array'],
            'site_rate.*' => ['numeric', 'min:0', 'max:100'],
            'sample_product_name' => ['required', 'string', 'max:255'],
            'sample_product_url' => ['nullable', 'url', 'max:500'],
            'sample_invoice_item_name' => ['required', 'string', 'max:255'],
            'discord_sample_webhook_url' => ['nullable', 'url', 'max:500'],
            'sns_hashtag' => ['required', 'string', 'max:64'],
            'sns_account_x' => ['nullable', 'string', 'max:64'],
            'sns_account_instagram' => ['nullable', 'string', 'max:64'],
            'sns_account_tiktok' => ['nullable', 'string', 'max:64'],
            'sns_post_deadline_days' => ['required', 'integer', 'min:1', 'max:90'],
        ], [], [
            'sample_product_name' => 'サンプル商品名',
            'sample_product_url' => 'サンプル商品ページURL',
            'sample_invoice_item_name' => '送り状の品名',
            'discord_sample_webhook_url' => 'サンプル通知用のDiscord Webhook URL',
            'sns_hashtag' => 'ハッシュタグ',
            'sns_account_x' => 'Xの公式アカウント',
            'sns_account_instagram' => 'Instagramの公式アカウント',
            'sns_account_tiktok' => 'TikTokの公式アカウント',
            'sns_post_deadline_days' => '投稿期限（日数）',
        ]);

        // 共通設定
        Setting::current()->update([
            'confirm_after_days' => $data['confirm_after_days'],
            'min_payout_amount' => $data['min_payout_amount'],
            'cookie_lifetime_days' => $data['cookie_lifetime_days'],
            'sample_product_name' => $data['sample_product_name'],
            'sample_product_url' => $data['sample_product_url'] ?? null,
            'sample_invoice_item_name' => $data['sample_invoice_item_name'],
            // チェックボックスは未チェックだと送信されない
            'sample_request_enabled' => $request->boolean('sample_request_enabled'),
            'discord_sample_webhook_url' => $data['discord_sample_webhook_url'] ?? null,
            'sns_hashtag' => $data['sns_hashtag'],
            'sns_account_x' => $data['sns_account_x'] ?? null,
            'sns_account_instagram' => $data['sns_account_instagram'] ?? null,
            'sns_account_tiktok' => $data['sns_account_tiktok'] ?? null,
            'sns_post_deadline_days' => $data['sns_post_deadline_days'],
        ]);

        // サイトごとの料率
        foreach ($data['site_rate'] ?? [] as $siteId => $rate) {
            Site::where('id', (int) $siteId)->update(['commission_rate' => $rate]);
        }

        return back()->with('success', '設定を保存しました。');
    }
}
