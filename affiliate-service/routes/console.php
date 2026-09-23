<?php

use Illuminate\Support\Facades\Schedule;

// 成果の確定/取消を日次実行
Schedule::command('affiliate:confirm-rewards')->dailyAt('04:00');

// 月次の支払いピックアップ（毎月1日 05:00）
Schedule::command('affiliate:monthly-payout')->monthlyOn(1, '05:00');

// サンプルの到着日をヤマトの追跡から取得（リマインドの前に実行）
Schedule::command('affiliate:check-deliveries')->dailyAt('09:00');

// SNS投稿の自動リマインド（毎日10:00。朝早すぎる時間を避ける）
Schedule::command('affiliate:sns-reminders')->dailyAt('10:00');
