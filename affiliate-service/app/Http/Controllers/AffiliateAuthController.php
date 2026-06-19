<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * アンバサダー本人のログイン（メールアドレス＋パスワード）。
 * 管理画面の Admin\AuthController と同じセッション方式。
 */
class AffiliateAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('affiliate.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [], [
            'email' => 'メールアドレス',
            'password' => 'パスワード',
        ]);

        $affiliate = Affiliate::where('email', $credentials['email'])->first();

        if (!$affiliate || !$affiliate->password || !Hash::check($credentials['password'], $affiliate->password)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'メールアドレスまたはパスワードが正しくありません。']);
        }

        if (!$affiliate->isApproved()) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'このアカウントはまだ承認されていません。承認のご連絡までお待ちください。']);
        }

        $request->session()->regenerate();
        $request->session()->put('affiliate_authenticated', true);
        $request->session()->put('affiliate_id', $affiliate->id);
        $request->session()->put('affiliate_name', $affiliate->name);

        return redirect()->route('affiliate.mypage');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['affiliate_authenticated', 'affiliate_id', 'affiliate_name']);
        $request->session()->regenerate();

        return redirect()->route('affiliate.login');
    }
}
