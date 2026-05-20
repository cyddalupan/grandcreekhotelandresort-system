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
            $table->string('hotel_name')->default('Grand Creek Hotel & Resort');
            $table->string('currency')->default('PHP');
            $table->integer('low_stock_threshold')->default(30);
            $table->integer('bill_alert_days')->default(7);
            $table->json('notifications')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
