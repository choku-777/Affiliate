<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 公式サイト（うましっぽ等）への掲載に使う情報を投稿ごとに持たせる。
 * 犬の情報・商品は社内が承認時に入力し、サムネイルは承認時に自動取得して保存する。
 * 画像を保存・掲載するのは、アンバサダーから掲載の同意（affiliates.media_consent_at、次のマイグレーション）をもらった投稿だけ。
 * 既存データは書き換えない。site_id が NULL の投稿は「未設定」で、どのサイトにも掲載しない。
 * サイトの削除で掲載先が勝手に別サイトへ変わらないよう、掲載先に使われているサイトは削除できないようにする。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sns_posts', function (Blueprint $table) {
            $table->foreignId('site_id')->nullable()->after('affiliate_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('product_id')->nullable()->after('note');      // EC-CUBE の商品ID
            $table->string('product_name')->nullable()->after('product_id');       // 表示用の商品名
            $table->string('dog_breed', 64)->nullable()->after('product_name');    // 犬種
            $table->string('dog_age', 32)->nullable()->after('dog_breed');         // 年齢の表示（例: 12歳 / 8ヶ月）
            $table->string('dog_size', 16)->nullable()->after('dog_age');          // small / medium / large
            $table->string('dog_stage', 16)->nullable()->after('dog_size');        // puppy / adult / senior
            $table->string('thumbnail_path')->nullable()->after('dog_stage');      // storage/app 配下の相対パス
            $table->timestamp('thumbnail_fetched_at')->nullable()->after('thumbnail_path');
        });
    }

    public function down(): void
    {
        Schema::table('sns_posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('site_id');
            $table->dropColumn([
                'product_id', 'product_name',
                'dog_breed', 'dog_age', 'dog_size', 'dog_stage',
                'thumbnail_path', 'thumbnail_fetched_at',
            ]);
        });
    }
};
