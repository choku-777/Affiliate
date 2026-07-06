<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * マルチサイト対応：サイトマスタ(sites)を追加し、成果・クリックにどのサイト経由かを記録する。
 * 料率はサイトごと（sites.commission_rate）。アフィリエイターは両サイト共通。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();      // 例: shizenha-inu / baniku
            $table->string('name');                     // サイト名
            $table->string('shop_url');                 // ショップのベースURL
            $table->decimal('commission_rate', 5, 2)->default(5); // サイト別の既定料率(%)
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::table('rewards', function (Blueprint $table) {
            $table->foreignId('site_id')->nullable()->after('affiliate_id')->constrained()->nullOnDelete();
        });
        Schema::table('clicks', function (Blueprint $table) {
            $table->foreignId('site_id')->nullable()->after('affiliate_id')->constrained()->nullOnDelete();
        });

        // 初期サイト（既存設定の料率を引き継ぐ）
        $rate = (float) (DB::table('settings')->value('commission_rate') ?? 5);
        $now = now();
        DB::table('sites')->insert([
            ['code' => 'umashippo', 'name' => 'うましっぽ', 'shop_url' => 'https://umashippo.jp', 'commission_rate' => $rate, 'is_default' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'baniku', 'name' => '馬肉特急', 'shop_url' => 'https://www.829109.jp', 'commission_rate' => $rate, 'is_default' => 0, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 既存の成果・クリックは既定サイトに紐付け
        $defaultId = DB::table('sites')->where('is_default', 1)->value('id');
        DB::table('rewards')->whereNull('site_id')->update(['site_id' => $defaultId]);
        DB::table('clicks')->whereNull('site_id')->update(['site_id' => $defaultId]);
    }

    public function down(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->dropConstrainedForeignId('site_id');
        });
        Schema::table('clicks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('site_id');
        });
        Schema::dropIfExists('sites');
    }
};
