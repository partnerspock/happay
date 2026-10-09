<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('region_master', function (Blueprint $table) {
            $table->id();
            $table->string('region_alias', 3);
            $table->string('region_name');
            $table->enum('region_status', ['active', 'inactive'])->default('active');
            $table->timestamp('region_deleted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('region_master');
    }
};