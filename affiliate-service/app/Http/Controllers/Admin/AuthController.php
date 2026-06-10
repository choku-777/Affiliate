<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

/**
 * 管理画面のGoogleアカウント認証。許可メール（ホワイトリスト）のみログイン可。
 */
class AuthController extends Controller
{
    public function login(): View
    {
        return view('admin.login');
    }

    public function redirect()
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('admin.login')->with('error', 'Google認証に失敗しました。');
        }

        $email = strtolower((string) $googleUser->getEmail());
        $allowed = (array) config('affiliate.admin_emails');

        if (!in_array($email, $allowed, true)) {
            return redirect()->route('admin.login')
                ->with('error', 'このアカウントには管理画面へのアクセス権がありません。');
        }

        $request->session()->regenerate();
        $request->session()->put('admin_authenticated', true);
        $request->session()->put('admin_email', $email);
        $request->session()->put('admin_name', $googleUser->getName() ?: $email);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['admin_authenticated', 'admin_email', 'admin_name']);
        $request->session()->regenerate();

        return redirect()->route('admin.login');
    }
}
