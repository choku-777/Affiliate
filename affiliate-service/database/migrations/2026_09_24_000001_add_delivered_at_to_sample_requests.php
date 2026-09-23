<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * サンプルの到着日を記録し、SNS投稿の期限・リマインドを「お届け日」起点にする。
 * delivered_source: yamato（追跡ページから取得）/ self（本人が「届きました」を押した）/ estimate（取得できず推定）
 * 1通目のリマインドは「お届けから○日後」の意味に変わるため、初期値を3日に揃える。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_requests', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            $table->string('delivered_source', 16)->nullable()->after('delivered_at');
            $table->timestamp('delivery_checked_at')->nullable()->after('delivered_source');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->integer('sns_reminder_after_days')->default(3)->change();
        });
        DB::table('settings')->where('sns_reminder_after_days', 5)->update(['sns_reminder_after_days' => 3]);
    }

    public function down(): void
    {
        Schema::table('sample_requests', function (Blueprint $table) {
            $table->dropColumn(['delivered_at', 'delivered_source', 'delivery_checked_at']);
        });
        Schema::table('settings', function (Blueprint $table) {
            $table->integer('sns_reminder_after_days')->default(5)->change();
        });
    }
};
