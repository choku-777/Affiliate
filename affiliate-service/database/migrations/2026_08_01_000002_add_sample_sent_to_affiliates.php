<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * サンプル送付の記録を追加する。
 * sample_sent_at が null なら未送付。既存レコードはすべて未送付として扱う。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            $table->timestamp('sample_sent_at')->nullable()->after('approved_at'); // 送付日時
            $table->string('sample_sent_by')->nullable()->after('sample_sent_at'); // 記録した管理者
        });
    }

    public function down(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            $table->dropColumn(['sample_sent_at', 'sample_sent_by']);
        });
    }
};
