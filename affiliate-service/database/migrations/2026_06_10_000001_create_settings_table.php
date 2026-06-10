<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('commission_rate', 5, 2)->default(5);
            $table->unsignedInteger('confirm_after_days')->default(30);
            $table->unsignedInteger('min_payout_amount')->default(5000);
            $table->unsignedInteger('cookie_lifetime_days')->default(30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
