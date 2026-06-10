<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 報酬の一括支払い履歴。1回の支払い＝1 payout（対象の確定報酬をまとめて paid にする）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount');        // 支払い合計額（円）
            $table->unsignedInteger('reward_count');  // まとめた成果件数
            $table->timestamp('paid_at');
            $table->timestamps();
        });

        Schema::table('rewards', function (Blueprint $table) {
            // どの支払いでまとめられたか（未払いは NULL）
            $table->foreignId('payout_id')->nullable()->after('paid_at')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payout_id');
        });
        Schema::dropIfExists('payouts');
    }
};
