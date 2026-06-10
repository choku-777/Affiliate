<?php

use App\Http\Controllers\Api\EventController;
use Illuminate\Support\Facades\Route;

// EC-CUBE プラグインからの postback 受信（X-Api-Key 認証）
Route::post('/affiliate/event', [EventController::class, 'store'])->middleware('apikey');
