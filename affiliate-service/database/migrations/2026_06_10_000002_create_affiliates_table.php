<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('affiliate_code', 64)->unique();
            $table->string('mypage_token', 64)->unique();
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('account_type', 32)->nullable();
            $table->string('account_number', 32)->nullable();
            $table->string('account_holder')->nullable();
            $table->string('status', 16)->default('pending')->index();
            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliates');
    }
};
