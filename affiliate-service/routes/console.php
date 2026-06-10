<?php

use Illuminate\Support\Facades\Schedule;

// 成果の確定/取消を日次実行
Schedule::command('affiliate:confirm-rewards')->dailyAt('04:00');
