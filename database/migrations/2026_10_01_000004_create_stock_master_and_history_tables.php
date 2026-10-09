<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_master', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouse_master')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('item_master')->cascadeOnDelete();
            $table->string('item_name');
            $table->integer('stock_now')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_master');
    }
};