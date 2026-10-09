<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pos_transaction_master', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique(); // Format: INV-[region]-[yyyymm001]
            $table->timestamp('transaction_date')->nullable();
            $table->enum('transaction_status', ['completed', 'draft', 'void', 'cancel'])->default('draft');
            $table->foreignId('warehouse_id')->constrained('warehouse_master')->cascadeOnDelete();
            $table->foreignId('cashier_id')->nullable()->constrained('admin_master')->nullOnDelete();
            $table->string('transaction_info_name')->nullable(); // Nama Customer
            $table->string('transaction_info_number')->nullable(); // Kontak Customer / No Meja
            $table->decimal('transaction_pricetotal', 15, 2)->default(0);
            $table->decimal('transaction_tax', 15, 2)->default(0); // 11% dari total price
            $table->decimal('transaction_margin', 15, 2)->default(0); // Selisih harga jual - COGS
            $table->timestamps();
        });

        Schema::create('pos_transaction_item_master', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('pos_transaction_master', 'id')->cascadeOnDelete();
            $table->foreignId('stock_master_id')->constrained('stock_master')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('item_master')->cascadeOnDelete(); // Relasi ke item master untuk ambil COGS & nama
            $table->integer('transaction_item_qty');
            $table->string('transaction_item_variant')->nullable();
            $table->decimal('transaction_item_priceperitem', 15, 2);
            $table->decimal('transaction_item_pricesubtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_transaction_item_master');
        Schema::dropIfExists('pos_transaction_master');
    }
};