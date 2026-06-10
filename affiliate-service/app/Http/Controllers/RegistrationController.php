<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * アフィリエイター登録（公開）。申請として受け付け、承認は管理画面で行う。
 */
class RegistrationController extends Controller
{
    public function create(): View
    {
        return view('register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:affiliates,email'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_branch' => ['nullable', 'string', 'max:255'],
            'account_type' => ['nullable', 'in:普通,当座'],
            'account_number' => ['nullable', 'string', 'max:32'],
            'account_holder' => ['nullable', 'string', 'max:255'],
        ], [], [
            'name' => 'お名前',
            'email' => 'メールアドレス',
        ]);

        Affiliate::create($data + [
            'affiliate_code' => Affiliate::generateCode(),
            'mypage_token' => Affiliate::generateToken(),
            'status' => Affiliate::STATUS_PENDING,
        ]);

        return redirect()->route('register.thanks');
    }

    public function thanks(): View
    {
        return view('register-thanks');
    }
}
