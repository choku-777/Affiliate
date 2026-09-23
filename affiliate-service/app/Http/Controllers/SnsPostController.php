<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Setting;
use App\Models\SnsPost;
use App\Services\DiscordNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * アンバサダー本人によるSNS投稿URLの申告（ログイン必須）。
 * サンプルの申込がある人だけが使える。URLを貼るだけで、SNSの判定・短縮URLの解決はこちらで行う。
 */
class SnsPostController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $affiliate = $this->currentAffiliate($request);
        if (!$affiliate || !$affiliate->isApproved()) {
            return redirect()->route('affiliate.login');
        }

        // 新方式の申込がある人、または仕組み導入前にサンプルを受け取った人が対象
        $sampleRequest = $affiliate->activeSampleRequest();
        if (!$sampleRequest && !$affiliate->hasSampleSent()) {
            return redirect()->route('affiliate.mypage')
                ->with('error', '投稿の申告は、サンプルをお受け取りいただいた方が対象です。');
        }

        $setting = Setting::current();

        return view('affiliate.sns-posts', [
            'affiliate' => $affiliate,
            'sampleRequest' => $sampleRequest,
            'setting' => $setting,
            'deadline' => $sampleRequest?->snsDeadline((int) $setting->sns_post_deadline_days),
            'posts' => $affiliate->snsPosts()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $affiliate = $this->currentAffiliate($request);
        if (!$affiliate || !$affiliate->isApproved()) {
            return redirect()->route('affiliate.login');
        }

        // 新方式の申込がある人、または仕組み導入前にサンプルを受け取った人が対象
        $sampleRequest = $affiliate->activeSampleRequest();
        if (!$sampleRequest && !$affiliate->hasSampleSent()) {
            return redirect()->route('affiliate.mypage')
                ->with('error', '投稿の申告は、サンプルをお受け取りいただいた方が対象です。');
        }

        $data = $request->validate([
            'url' => ['required', 'string', 'max:500', 'url'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'url.url' => '投稿のURLを正しく入力してください（https:// から始まるもの）。',
        ], [
            'url' => '投稿URL',
            'note' => 'ひとこと',
        ]);

        $parsed = SnsPost::analyze($data['url']);
        if (is_string($parsed)) {
            return back()->withInput()->with('error', $parsed);
        }

        $post = SnsPost::create([
            'affiliate_id' => $affiliate->id,
            'sample_request_id' => $sampleRequest?->id,
            'platform' => $parsed['platform'],
            'post_url' => $data['url'],
            'normalized_url' => $parsed['normalized_url'],
            'post_id' => $parsed['post_id'],
            'status' => SnsPost::STATUS_PENDING,
            'note' => $data['note'] ?? null,
        ]);

        try {
            app(DiscordNotifier::class)->snsPostSubmitted($post->load('affiliate'));
        } catch (\Throwable $e) {
            // 通知失敗で申告を失敗させない（DiscordNotifier内でログ済み）
        }

        return redirect()->route('affiliate.sns-posts.index')
            ->with('success', '投稿を受け付けました。内容を確認のうえ、公式サイトで紹介させていただきます。');
    }

    private function currentAffiliate(Request $request): ?Affiliate
    {
        $id = $request->session()->get('affiliate_id');

        return $id ? Affiliate::find($id) : null;
    }
}
