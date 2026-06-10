<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();
            $table->string('order_no', 64)->unique();
            $table->decimal('order_total', 12, 2)->default(0);
            $table->decimal('rate_applied', 5, 2)->default(0);
            $table->unsignedInteger('reward_amount')->default(0);
            $table->string('status', 16)->default('pending')->index();
            $table->unsignedInteger('order_status_id')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};
