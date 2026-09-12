<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SNS投稿キャンペーンの設定（ハッシュタグ・公式アカウント・投稿期限）。
 * 公式アカウントは未作成のため空を許容し、空の間は案内に表示しない。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('sns_hashtag', 64)->default('#うましっぽ')->after('discord_sample_webhook_url');
            $table->string('sns_account_x', 64)->nullable()->after('sns_hashtag');
            $table->string('sns_account_instagram', 64)->nullable()->after('sns_account_x');
            $table->string('sns_account_tiktok', 64)->nullable()->after('sns_account_instagram');
            $table->integer('sns_post_deadline_days')->default(14)->after('sns_account_tiktok');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['sns_hashtag', 'sns_account_x', 'sns_account_instagram', 'sns_account_tiktok', 'sns_post_deadline_days']);
        });
    }
};
