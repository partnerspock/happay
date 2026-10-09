<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hanya buat stock_history karena stock_master sudah dibuat di migration terdahulu
        Schema::create('stock_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouse_master')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('item_master')->cascadeOnDelete();
            $table->integer('history_before');
            $table->integer('history_change');
            $table->integer('history_now');
            $table->enum('history_change__type', ['increased', 'decreased']);
            $table->string('history_change_description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_history');
    }
};