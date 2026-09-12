<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * マイページに掲載するお知らせ。管理画面から編集し、公開中のものだけ表示する。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');                          // タイトル
            $table->text('body');                             // 本文（改行はそのまま表示）
            $table->boolean('is_published')->default(false);  // 公開/非公開
            $table->integer('sort_order')->default(0);        // 表示順（小さいほど上）
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
