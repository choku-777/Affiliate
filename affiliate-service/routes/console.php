<?php

use Illuminate\Support\Facades\Schedule;

// 成果の確定/取消を日次実行
Schedule::command('affiliate:confirm-rewards')->dailyAt('04:00');

// 月次の支払いピックアップ（毎月1日 05:00）
Schedule::command('affiliate:monthly-payout')->monthlyOn(1, '05:00');
