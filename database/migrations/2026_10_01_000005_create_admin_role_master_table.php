<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_role_master', function (Blueprint $table) {
            $table->id();
            $table->enum('role_name', ['spv', 'cashier', 'warehouse', 'manager']); // Ditambahkan manager sesuai diskusi sebelumnya jika diperlukan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_role_master');
    }
};