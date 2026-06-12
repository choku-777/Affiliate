<?php

namespace App\Http\Controllers;

use App\Mail\InquiryMail;
use App\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * アフィリエイター本人からの問い合わせ（ログイン必須）。
 * 管理者(main@829109.jp)宛にメール送信し、返信先は本人のメールにする。
 */
class InquiryController extends Controller
{
    /** 問い合わせ送信先（管理者） */
    private const ADMIN_EMAIL = 'main@829109.jp';

    public function create(Request $request): View
    {
        $affiliate = Affiliate::findOrFail($request->session()->get('affiliate_id'));

        return view('inquiry', ['affiliate' => $affiliate]);
    }

    public function store(Request $request)
    {
        $affiliate = Affiliate::findOrFail($request->session()->get('affiliate_id'));

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ], [], [
            'subject' => '件名',
            'body' => 'お問い合わせ内容',
        ]);

        try {
            Mail::to(self::ADMIN_EMAIL)
                ->send(new InquiryMail($affiliate, $data['subject'], $data['body']));
        } catch (\Throwable $e) {
            Log::error('問い合わせメール送信失敗: '.$e->getMessage());

            return back()->withInput()
                ->with('error', '送信に失敗しました。お手数ですが時間をおいて再度お試しください。');
        }

        return redirect()->route('affiliate.mypage')
            ->with('success', 'お問い合わせを送信しました。担当者より追ってご連絡いたします。');
    }
}
