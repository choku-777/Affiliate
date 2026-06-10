<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', [
            'setting' => Setting::current(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'confirm_after_days' => ['required', 'integer', 'min:0', 'max:365'],
            'min_payout_amount' => ['required', 'integer', 'min:0'],
            'cookie_lifetime_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        Setting::current()->update($data);

        return back()->with('success', '設定を保存しました。');
    }
}
