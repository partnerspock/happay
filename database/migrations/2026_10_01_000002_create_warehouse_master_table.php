<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('warehouse_master', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_region')->constrained('region_master')->cascadeOnDelete();
            $table->string('warehouse_name');
            $table->enum('warehouse_status', ['active', 'inactive'])->default('active');
            $table->timestamp('warehouse_deleted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_master');
    }
};