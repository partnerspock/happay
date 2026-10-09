<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_master', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->nullable()->constrained('region_master')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouse_master')->nullOnDelete();
            $table->string('admin_name');
            $table->string('admin_contact');
            $table->string('admin_password');
            $table->foreignId('admin_role')->constrained('admin_role_master')->cascadeOnDelete();
            $table->timestamp('admin_last_login')->nullable();
            $table->text('admin_sessions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_master');
    }
};