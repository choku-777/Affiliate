<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 月次締め支払いに対応：締め月・CSVダウンロード日時を追加し、
 * paid_at を「未入金=null」を表せるよう nullable に変更する。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->string('closing_month', 7)->nullable()->after('affiliate_id'); // 締め月 YYYY-MM
            $table->timestamp('csv_downloaded_at')->nullable()->after('reward_count'); // CSVダウンロード日時（①フラグ）
        });

        // 未入金を表せるよう paid_at を nullable に（自動セットの既定値も外す）
        Schema::table('payouts', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropColumn(['closing_month', 'csv_downloaded_at']);
        });
    }
};
