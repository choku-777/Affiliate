<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * アンバサダーが申告したSNS投稿（X / Instagram / TikTok）。
 * 承認したものだけを公式サイトに掲載する。#PRの確認を承認の前提にする。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sns_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sample_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('platform', 16);                    // x / instagram / tiktok
            $table->string('post_url', 500);                   // 申告されたURL（原文）
            $table->string('normalized_url', 500)->unique();   // 正規化後（重複判定・埋め込み用）
            $table->string('post_id', 64);                     // 投稿ID
            $table->string('status', 16)->default('pending');  // pending / approved / rejected / hidden
            $table->text('note')->nullable();                  // アンバサダーのひとこと
            $table->timestamp('pr_checked_at')->nullable();    // #PR を確認した日時
            $table->string('pr_checked_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('reject_reason')->nullable();
            $table->integer('sort_order')->default(0);        // 掲載の表示順
            $table->timestamps();

            $table->index(['status', 'sort_order']);
            $table->index('affiliate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sns_posts');
    }
};
