<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SNS投稿のリマインド管理。
 * - 申込ごとに自動リマインド3回（到着確認／期限前／期限切れ）と手動催促の送信日時を記録し、二重送信を防ぐ
 * - 仕組み導入前にサンプルを送った人への「お願いメール」の送信日時をアンバサダー側に記録する
 * - リマインドのON/OFFと日数を設定で変えられるようにする
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_requests', function (Blueprint $table) {
            $table->timestamp('reminder_arrival_at')->nullable()->after('sns_consent_at');   // 発送から○日後
            $table->timestamp('reminder_before_at')->nullable()->after('reminder_arrival_at'); // 期限の○日前
            $table->timestamp('reminder_overdue_at')->nullable()->after('reminder_before_at'); // 期限の翌日
            $table->timestamp('reminder_manual_at')->nullable()->after('reminder_overdue_at'); // 手動催促（最新）
        });

        Schema::table('affiliates', function (Blueprint $table) {
            $table->timestamp('sns_request_mail_sent_at')->nullable()->after('sample_sent_by');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('sns_reminder_enabled')->default(true)->after('sns_post_deadline_days');
            $table->integer('sns_reminder_after_days')->default(5)->after('sns_reminder_enabled');
            $table->integer('sns_reminder_before_days')->default(3)->after('sns_reminder_after_days');
        });
    }

    public function down(): void
    {
        Schema::table('sample_requests', function (Blueprint $table) {
            $table->dropColumn(['reminder_arrival_at', 'reminder_before_at', 'reminder_overdue_at', 'reminder_manual_at']);
        });
        Schema::table('affiliates', function (Blueprint $table) {
            $table->dropColumn('sns_request_mail_sent_at');
        });
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['sns_reminder_enabled', 'sns_reminder_after_days', 'sns_reminder_before_days']);
        });
    }
};
