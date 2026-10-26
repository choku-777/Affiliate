<?php

use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\SnsPostListController;
use Illuminate\Support\Facades\Route;

// EC-CUBE プラグインからの postback 受信（X-Api-Key 認証）
Route::post('/affiliate/event', [EventController::class, 'store'])->middleware('apikey');

// 公式サイト（EC-CUBE）向け：掲載するアンバサダー投稿の一覧（X-Api-Key 認証・?site=umashippo）
Route::get('/sns-posts', [SnsPostListController::class, 'index'])->middleware('apikey');
