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
        ]);

        // 共通設定
        Setting::current()->update([
            'confirm_after_days' => $data['confirm_after_days'],
            'min_payout_amount' => $data['min_payout_amount'],
            'cookie_lifetime_days' => $data['cookie_lifetime_days'],
        ]);

        // サイトごとの料率
        foreach ($data['site_rate'] ?? [] as $siteId => $rate) {
            Site::where('id', (int) $siteId)->update(['commission_rate' => $rate]);
        }

        return back()->with('success', '設定を保存しました。');
    }
}
