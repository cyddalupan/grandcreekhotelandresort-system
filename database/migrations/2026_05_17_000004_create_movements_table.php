<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['IN', 'OUT', 'TRANSFER']);
            $table->integer('quantity');
            $table->foreignId('from_department')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('to_department')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->string('user');
            $table->text('notes')->nullable();
            $table->dateTime('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
