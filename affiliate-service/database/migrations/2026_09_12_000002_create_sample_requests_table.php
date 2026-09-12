<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * アンバサダーからのサンプル商品申し込み。
 * 送付先は「申込時点の内容」を保存する（後から登録住所が変わっても発送時の住所を残すため）。
 * 発送の記録は既存の affiliates.sample_sent_at とも連動させる。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');                   // 申込時点の商品名

            // 送付先（申込時点のスナップショット）
            $table->string('recipient_name');                 // 宛名
            $table->string('postal_code', 8);
            $table->string('prefecture', 16);
            $table->string('city');
            $table->string('address1');
            $table->string('address2')->nullable();           // 建物名
            $table->string('phone', 20);
            $table->text('note')->nullable();                 // ご要望

            // 状態: requested / csv_exported / shipped / cancelled
            $table->string('status', 16)->default('requested');
            $table->string('tracking_number', 32)->nullable(); // 伝票番号（ハイフンなしで保存）

            $table->timestamp('requested_at');                 // 申込日時
            $table->timestamp('csv_downloaded_at')->nullable();// B2用CSVを出力した日時
            $table->timestamp('shipped_at')->nullable();       // 発送日時
            $table->timestamp('shipped_mail_sent_at')->nullable(); // 発送完了メールの送信日時
            $table->string('shipped_by')->nullable();          // 発送を記録した管理者
            $table->timestamps();

            $table->index(['status', 'requested_at']);
            $table->index('tracking_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_requests');
    }
};
