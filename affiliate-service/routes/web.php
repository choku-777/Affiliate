<?php

use App\Http\Controllers\Admin\AffiliateController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\RewardController;
use App\Http\Controllers\Admin\SampleRequestController as AdminSampleRequestController;
use App\Http\Controllers\Admin\SnsPostController as AdminSnsPostController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\AffiliateAuthController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MyPageController;
use App\Http\Controllers\SampleRequestController;
use App\Http\Controllers\SnsPostController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

// 公開：トップ（アンバサダー募集LP）
Route::get('/', [LandingController::class, 'index'])->name('home');

// 公開：アフィリエイター登録
Route::get('/register', [RegistrationController::class, 'create'])->name('register.create');
Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');
Route::get('/register/thanks', [RegistrationController::class, 'thanks'])->name('register.thanks');

// 公開：お問い合わせ（未ログインでも可。ログイン中は名前・メールを自動入力）
Route::get('/inquiry', [InquiryController::class, 'create'])->name('inquiry.create');
Route::post('/inquiry', [InquiryController::class, 'store'])->name('inquiry.store');

// アフィリエイター：ログイン＆マイページ（ログイン必須に一本化）
Route::prefix('affiliate')->name('affiliate.')->group(function () {
    Route::get('login', [AffiliateAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AffiliateAuthController::class, 'login'])->name('login.post');

    Route::middleware('affiliate')->group(function () {
        Route::get('mypage', [MyPageController::class, 'show'])->name('mypage');

        // サンプル商品の申し込み（1人1回）
        Route::get('sample', [SampleRequestController::class, 'create'])->name('sample.create');
        Route::post('sample', [SampleRequestController::class, 'store'])->name('sample.store');

        // SNS投稿URLの申告（サンプル申込者のみ）
        Route::get('sns-posts', [SnsPostController::class, 'index'])->name('sns-posts.index');
        Route::post('sns-posts', [SnsPostController::class, 'store'])->name('sns-posts.store');
        Route::post('logout', [AffiliateAuthController::class, 'logout'])->name('logout');
    });
});

// 管理画面
Route::prefix('admin')->name('admin.')->group(function () {
    // Google認証
    Route::get('login', [AuthController::class, 'login'])->name('login');
    Route::get('auth/google/redirect', [AuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('auth/google/callback', [AuthController::class, 'callback'])->name('auth.google.callback');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // 認可が必要な領域
    Route::middleware('admin')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('affiliates', [AffiliateController::class, 'index'])->name('affiliates.index');
        Route::get('affiliates/{affiliate}', [AffiliateController::class, 'show'])->name('affiliates.show');
        Route::post('affiliates/{affiliate}/approve', [AffiliateController::class, 'approve'])->name('affiliates.approve');
        Route::post('affiliates/{affiliate}/reject', [AffiliateController::class, 'reject'])->name('affiliates.reject');
        Route::post('affiliates/{affiliate}/suspend', [AffiliateController::class, 'suspend'])->name('affiliates.suspend');
        Route::post('affiliates/{affiliate}/sample-sent', [AffiliateController::class, 'markSampleSent'])->name('affiliates.sample.mark');
        Route::post('affiliates/{affiliate}/sample-unsent', [AffiliateController::class, 'unmarkSampleSent'])->name('affiliates.sample.unmark');
        Route::post('affiliates/{affiliate}/notes', [AffiliateController::class, 'storeNote'])->name('affiliates.notes.store');
        Route::delete('affiliates/{affiliate}/notes/{note}', [AffiliateController::class, 'destroyNote'])->name('affiliates.notes.destroy');

        // お知らせ（運用担当・管理者とも編集可）
        Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
        Route::put('announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

        // サンプル発送
        Route::get('sample-requests', [AdminSampleRequestController::class, 'index'])->name('sample-requests.index');
        Route::get('sample-requests/csv', [AdminSampleRequestController::class, 'downloadCsv'])->name('sample-requests.csv');
        Route::post('sample-requests/import', [AdminSampleRequestController::class, 'import'])->name('sample-requests.import');
        Route::post('sample-requests/{sampleRequest}/tracking', [AdminSampleRequestController::class, 'storeTracking'])->name('sample-requests.tracking');
        Route::post('sample-requests/{sampleRequest}/ship', [AdminSampleRequestController::class, 'ship'])->name('sample-requests.ship');
        Route::post('sample-requests/{sampleRequest}/cancel', [AdminSampleRequestController::class, 'cancel'])->name('sample-requests.cancel');

        // SNS投稿の確認・承認
        Route::get('sns-posts', [AdminSnsPostController::class, 'index'])->name('sns-posts.index');
        Route::get('sns-posts/{snsPost}', [AdminSnsPostController::class, 'show'])->name('sns-posts.show');
        Route::post('sns-posts/{snsPost}/pr-check', [AdminSnsPostController::class, 'prCheck'])->name('sns-posts.pr-check');
        Route::post('sns-posts/{snsPost}/check', [AdminSnsPostController::class, 'check'])->name('sns-posts.check');
        Route::post('sns-posts/{snsPost}/approve', [AdminSnsPostController::class, 'approve'])->name('sns-posts.approve');
        Route::post('sns-posts/{snsPost}/reject', [AdminSnsPostController::class, 'reject'])->name('sns-posts.reject');
        Route::post('sns-posts/{snsPost}/hide', [AdminSnsPostController::class, 'hide'])->name('sns-posts.hide');
        Route::post('sns-posts/{snsPost}/sort', [AdminSnsPostController::class, 'sort'])->name('sns-posts.sort');

        Route::get('rewards', [RewardController::class, 'index'])->name('rewards.index');
        Route::post('rewards/{reward}/cancel', [RewardController::class, 'cancel'])->name('rewards.cancel');

        Route::get('payouts', [PayoutController::class, 'index'])->name('payouts.index');
        Route::get('payouts/csv/{month}', [PayoutController::class, 'downloadCsv'])->name('payouts.csv');
        Route::post('payouts/{payout}/paid', [PayoutController::class, 'markPaid'])->name('payouts.paid');

        // 設定は「管理」ロール限定
        Route::middleware('manager')->group(function () {
            Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        });
    });
});
