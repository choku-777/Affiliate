<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 投稿の画像・動画を公式サイトに掲載することへの同意（アンバサダー単位）。
 * 旧来の「紹介への同意」には画像の保存・掲載が含まれるか曖昧なため、改めて同意をもらう。
 * media_consent_version には同意した文言の版を記録する（文言を変えたときに区別できるように）。
 * 既存データは書き換えない（全員が未同意から始まる）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            $table->timestamp('media_consent_at')->nullable();
            $table->string('media_consent_version', 16)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            $table->dropColumn(['media_consent_at', 'media_consent_version']);
        });
    }
};
