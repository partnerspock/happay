<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('item_master', function (Blueprint $table) {
            $table->id();
            $table->string('item_sku');
            $table->string('item_sku_variant')->nullable();
            $table->string('item_name');
            $table->string('item_variant')->nullable();
            $table->string('item_image')->nullable(); // Gambar utama item
            $table->string('item_variant_image')->nullable(); // Gambar spesifik varian item
            $table->decimal('item_price', 12, 2);
            $table->decimal('item_cogs', 12, 2);
            $table->timestamp('item_lifetime')->nullable();
            $table->enum('item_status', ['available', 'hold', 'discontinue'])->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_master');
    }
};