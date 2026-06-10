<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * アフィリエイター登録の項目拡張（姓名・フリガナ・連絡先・住所）と
 * ID/パスワード認証（メールをID、passwordを追加）。
 * 既存 name は連結値として残すため、追加カラムはすべて nullable。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            // 認証（メールをログインIDに流用するため username は持たない）
            $table->string('password')->nullable()->after('email');
            // 氏名（姓名分割＋フリガナ）
            $table->string('last_name')->nullable()->after('name');
            $table->string('first_name')->nullable()->after('last_name');
            $table->string('last_name_kana')->nullable()->after('first_name');
            $table->string('first_name_kana')->nullable()->after('last_name_kana');
            // 連絡先・属性
            $table->string('phone', 20)->nullable()->after('password');
            $table->date('birth_date')->nullable()->after('phone');
            $table->string('gender', 16)->nullable()->after('birth_date');
            // 住所
            $table->string('postal_code', 8)->nullable()->after('gender');
            $table->string('prefecture', 16)->nullable()->after('postal_code');
            $table->string('city')->nullable()->after('prefecture');
            $table->string('address1')->nullable()->after('city');
            $table->string('address2')->nullable()->after('address1');
        });
    }

    public function down(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            $table->dropColumn([
                'password',
                'last_name', 'first_name', 'last_name_kana', 'first_name_kana',
                'phone', 'birth_date', 'gender',
                'postal_code', 'prefecture', 'city', 'address1', 'address2',
            ]);
        });
    }
};
