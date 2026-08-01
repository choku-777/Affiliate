<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * アンバサダーごとのメモ（備考）。追記型で、投稿ごとに日時と担当者を残す。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->text('body');                       // メモ本文
            $table->string('created_by')->nullable();   // 投稿した管理者のメール
            $table->string('created_by_name')->nullable(); // 表示用の名前
            $table->timestamps();

            $table->index(['affiliate_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_notes');
    }
};
