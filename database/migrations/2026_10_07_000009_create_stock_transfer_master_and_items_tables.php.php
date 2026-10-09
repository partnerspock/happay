<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_transfer_master', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_id')->unique();
            $table->foreignId('warehouse_origin_id')->constrained('warehouse_master')->cascadeOnDelete();
            $table->foreignId('warehouse_destination_id')->constrained('warehouse_master')->cascadeOnDelete();
            $table->enum('transfer_status', ['draft', 'in_transit', 'completed', 'cancelled'])->default('draft');
            $table->foreignId('sender_admin_id')->nullable()->constrained('admin_master')->nullOnDelete();
            $table->foreignId('receiver_admin_id')->nullable()->constrained('admin_master')->nullOnDelete();
            $table->text('transfer_notes')->nullable();
            $table->timestamp('transfer_date_sent')->nullable();
            $table->timestamp('transfer_date_received')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_transfer_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('stock_transfer_master')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('item_master')->cascadeOnDelete();
            $table->string('item_sku');
            $table->string('item_name');
            $table->string('item_sku_variant')->nullable();
            $table->string('item_variant')->nullable();
            $table->integer('qty_sent');
            $table->integer('qty_received')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_item');
        Schema::dropIfExists('stock_transfer_master');
    }
};