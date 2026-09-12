<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\SampleRequest;
use App\Models\Setting;
use App\Services\DiscordNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * アンバサダー本人によるサンプル商品の申し込み（ログイン必須）。
 * お一人さま1回まで。送付先は登録住所を初期表示し、その場で変更できる。
 */
class SampleRequestController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $affiliate = $this->currentAffiliate($request);
        $setting = Setting::current();

        if ($redirect = $this->guard($affiliate, $setting)) {
            return $redirect;
        }

        return view('affiliate.sample', [
            'affiliate' => $affiliate,
            'setting' => $setting,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $affiliate = $this->currentAffiliate($request);
        $setting = Setting::current();

        if ($redirect = $this->guard($affiliate, $setting)) {
            return $redirect;
        }

        $data = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:8', 'regex:/\A\d{3}-?\d{4}\z/'],
            'prefecture' => ['required', 'string', 'max:16'],
            'city' => ['required', 'string', 'max:255'],
            'address1' => ['required', 'string', 'max:255'],
            'address2' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'regex:/\A[0-9\-]+\z/'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'postal_code.regex' => '郵便番号は数字7桁で入力してください。',
            'phone.regex' => '電話番号は数字とハイフンで入力してください。',
        ], [
            'recipient_name' => 'お名前',
            'postal_code' => '郵便番号',
            'prefecture' => '都道府県',
            'city' => '市区町村',
            'address1' => '番地',
            'address2' => '建物名・部屋番号',
            'phone' => '電話番号',
            'note' => 'ご要望',
        ]);

        $sampleRequest = SampleRequest::create($data + [
            'affiliate_id' => $affiliate->id,
            'product_name' => $setting->sample_product_name,
            'status' => SampleRequest::STATUS_REQUESTED,
            'requested_at' => now(),
        ]);

        // 通知の失敗で申し込み自体を失敗させない
        try {
            app(DiscordNotifier::class)->sampleRequested($sampleRequest->load('affiliate'));
        } catch (\Throwable $e) {
            // DiscordNotifier内でログ済み
        }

        return redirect()->route('affiliate.mypage')
            ->with('success', 'サンプルのお申し込みを受け付けました。発送までもうしばらくお待ちください。');
    }

    /**
     * 受付停止中・申し込み済みの場合はマイページへ戻す。
     */
    private function guard(?Affiliate $affiliate, Setting $setting): ?RedirectResponse
    {
        if (!$affiliate || !$affiliate->isApproved()) {
            return redirect()->route('affiliate.login');
        }

        if (!$setting->sample_request_enabled) {
            return redirect()->route('affiliate.mypage')
                ->with('error', '現在、サンプルのお申し込みを停止しています。');
        }

        if ($affiliate->activeSampleRequest()) {
            return redirect()->route('affiliate.mypage')
                ->with('error', 'サンプルのお申し込みは、お一人さま1回までとさせていただいております。');
        }

        return null;
    }

    private function currentAffiliate(Request $request): ?Affiliate
    {
        $id = $request->session()->get('affiliate_id');

        return $id ? Affiliate::find($id) : null;
    }
}
