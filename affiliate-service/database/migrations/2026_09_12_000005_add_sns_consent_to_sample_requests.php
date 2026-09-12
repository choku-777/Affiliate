<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * サンプル申込時の「SNSに投稿する」「公式サイトでの引用に同意」の同意日時。
 * 投稿はサンプルの交換条件のため、同意の記録を残す。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_requests', function (Blueprint $table) {
            $table->timestamp('sns_consent_at')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('sample_requests', function (Blueprint $table) {
            $table->dropColumn('sns_consent_at');
        });
    }
};
