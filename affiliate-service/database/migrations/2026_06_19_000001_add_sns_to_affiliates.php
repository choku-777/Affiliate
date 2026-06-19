<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 登録項目に「主に使うSNS」「アカウント名」を追加する。
 * 既存レコードは null（新規登録のみ必須）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            $table->string('sns')->nullable()->after('phone'); // 主に使うSNS
            $table->string('sns_account')->nullable()->after('sns'); // アカウント名
        });
    }

    public function down(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            $table->dropColumn(['sns', 'sns_account']);
        });
    }
};
