<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * アフィリエイター登録（公開）。申請として受け付け、承認は管理画面で行う。
 * メールアドレスがログインID、パスワードは hashed キャストで自動ハッシュ化される。
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
            'last_name' => ['required', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name_kana' => ['required', 'string', 'max:50', 'regex:/^[ァ-ヶーｦ-ﾟ　\s]+$/u'],
            'first_name_kana' => ['required', 'string', 'max:50', 'regex:/^[ァ-ヶーｦ-ﾟ　\s]+$/u'],
            'email' => ['required', 'email', 'max:255', 'unique:affiliates,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['required', 'string', 'regex:/^[0-9]{10,11}$/'],
            'birth_date' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female'],
            'postal_code' => ['required', 'string', 'max:8'],
            'prefecture' => ['required', 'string', 'max:16'],
            'city' => ['required', 'string', 'max:255'],
            'address1' => ['required', 'string', 'max:255'],
            'address2' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_branch' => ['nullable', 'string', 'max:255'],
            'account_type' => ['nullable', 'in:普通,当座'],
            'account_number' => ['nullable', 'string', 'max:32'],
            'account_holder' => ['nullable', 'string', 'max:255'],
        ], [
            'phone.regex' => '電話番号はハイフン無しの数字10〜11桁で入力してください。',
            'last_name_kana.regex' => '姓（フリガナ）はカタカナで入力してください。',
            'first_name_kana.regex' => '名（フリガナ）はカタカナで入力してください。',
        ], [
            'last_name' => '姓',
            'first_name' => '名',
            'last_name_kana' => '姓（フリガナ）',
            'first_name_kana' => '名（フリガナ）',
            'email' => 'メールアドレス',
            'password' => 'パスワード',
            'phone' => '電話番号',
            'birth_date' => '生年月日',
            'gender' => '性別',
            'postal_code' => '郵便番号',
            'prefecture' => '都道府県',
            'city' => '市区町村',
            'address1' => '番地',
            'address2' => '建物名',
        ]);

        // 既存コード（表示・検索）互換のため name は姓+名の連結を保持する
        $data['name'] = $data['last_name'].' '.$data['first_name'];

        $affiliate = Affiliate::create($data + [
            'affiliate_code' => Affiliate::generateCode(),
            'mypage_token' => Affiliate::generateToken(),
            'status' => Affiliate::STATUS_PENDING,
        ]);

        // 登録受付メール（送信失敗で登録自体は止めない）
        try {
            \Illuminate\Support\Facades\Mail::to($affiliate->email)
                ->send(new \App\Mail\RegistrationReceived($affiliate));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('登録受付メール送信失敗: '.$e->getMessage());
        }

        return redirect()->route('register.thanks');
    }

    public function thanks(): View
    {
        return view('register-thanks');
    }
}
