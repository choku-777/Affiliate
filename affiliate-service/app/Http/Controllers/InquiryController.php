<?php

namespace App\Http\Controllers;

use App\Mail\InquiryMail;
use App\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * 問い合わせ（公開）。ログイン中のアンバサダーは名前・メールを自動入力し、
 * 未ログイン（登録前）の場合は名前・メールも入力してもらう。
 * いずれも管理者(main@829109.jp)宛に送信し、返信先は問い合わせ者のメールにする。
 */
class InquiryController extends Controller
{
    /** 問い合わせ送信先（管理者） */
    private const ADMIN_EMAIL = 'main@829109.jp';

    public function create(Request $request): View
    {
        return view('inquiry', ['affiliate' => $this->currentAffiliate($request)]);
    }

    public function store(Request $request)
    {
        $affiliate = $this->currentAffiliate($request);

        $rules = [
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ];
        $attributes = ['subject' => '件名', 'body' => 'お問い合わせ内容'];

        // 未ログイン（登録前）の場合は名前・メールも入力してもらう
        if (! $affiliate) {
            $rules['name'] = ['required', 'string', 'max:100'];
            $rules['email'] = ['required', 'email', 'max:255'];
            $attributes['name'] = 'お名前';
            $attributes['email'] = 'メールアドレス';
        }

        $data = $request->validate($rules, [], $attributes);

        $name = $affiliate ? $affiliate->name : $data['name'];
        $email = $affiliate ? $affiliate->email : $data['email'];

        try {
            Mail::to(self::ADMIN_EMAIL)
                ->send(new InquiryMail($name, $email, $data['subject'], $data['body'], $affiliate));
        } catch (\Throwable $e) {
            Log::error('問い合わせメール送信失敗: '.$e->getMessage());

            return back()->withInput()
                ->with('error', '送信に失敗しました。お手数ですが時間をおいて再度お試しください。');
        }

        $redirect = $affiliate ? route('affiliate.mypage') : route('inquiry.create');

        return redirect($redirect)
            ->with('success', 'お問い合わせを送信しました。担当者より追ってご連絡いたします。');
    }

    private function currentAffiliate(Request $request): ?Affiliate
    {
        $id = $request->session()->get('affiliate_id');

        return $id ? Affiliate::find($id) : null;
    }
}
