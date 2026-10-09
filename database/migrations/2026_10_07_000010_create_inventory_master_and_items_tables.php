<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_master', function (Blueprint $table) {
            $table->id();
            $table->string('inventory_id')->unique();
            $table->foreignId('warehouse_id')->constrained('warehouse_master')->onDelete('restrict');
            $table->enum('inventory_type', ['inbound', 'outbound']);
            $table->enum('inventory_status', ['created', 'arrived', 'completed'])->default('created');
            
            // Pengirim / Penerima / Ekspedisi
            $table->string('inventory_sender_name')->nullable();
            $table->string('inventory_sender_contact')->nullable();
            $table->string('inventory_sender_logistic_info')->nullable();
            $table->string('inventory_sender_recipient')->nullable();
            
            // Proof Media
            $table->string('inventory_sender_image')->nullable();
            $table->string('inventory_sender_video')->nullable();
            
            $table->text('inventory_notes')->nullable();
            $table->timestamp('inventory_date_arrived')->nullable();
            $table->timestamp('inventory_date_completed')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('admin_master')->nullOnDelete();
            $table->timestamps();
        });

        // 2. Inventory Master Item
        Schema::create('inventory_master_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventory_master')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('item_master')->onDelete('restrict');
            
            // Snapshot item metadata
            $table->string('item_sku');
            $table->string('item_name');
            $table->string('item_sku_variant')->nullable();
            $table->string('item_variant')->nullable();
            
            $table->integer('inventory_qty_sent')->default(0);
            $table->integer('inventory_qty_change')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_master_item');
        Schema::dropIfExists('inventory_master');
    }
};