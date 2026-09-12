<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * サンプル申し込みの設定（商品名・商品URL・受付可否・通知先）を全体設定に追加する。
 * 商品は現状1種類のため専用テーブルは作らない。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // マイページに表示する商品名
            $table->string('sample_product_name')->default('初回限定トライアル')->after('cookie_lifetime_days');
            // 商品ページのURL
            $table->string('sample_product_url', 500)->default('https://umashippo.jp/user_data/trial')->after('sample_product_name');
            // 送り状に印字する品名（B2クラウド用CSVの品名1）
            $table->string('sample_invoice_item_name')->default('ペットフードトライアルセット')->after('sample_product_url');
            // 申し込み受付の可否（在庫切れ時に停止できる）
            $table->boolean('sample_request_enabled')->default(true)->after('sample_invoice_item_name');
            // サンプル申し込み通知用のDiscord Webhook（既存の通知とは別チャンネル）
            $table->string('discord_sample_webhook_url', 500)->nullable()->after('sample_request_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'sample_product_name',
                'sample_product_url',
                'sample_invoice_item_name',
                'sample_request_enabled',
                'discord_sample_webhook_url',
            ]);
        });
    }
};
