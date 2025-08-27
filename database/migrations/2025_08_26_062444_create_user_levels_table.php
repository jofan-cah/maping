<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_levels', function (Blueprint $table) {
            $table->string('user_level_id')->primary(); // ID dengan format LVL25001
            $table->string('name')->unique(); // Nama Level (unique untuk avoid duplicate)
            $table->text('description')->nullable(); // Deskripsi level
            $table->json('permissions')->nullable(); // Permissions dalam format JSON
            $table->integer('priority')->default(0); // Priority level (semakin tinggi semakin powerful)
            $table->boolean('is_active')->default(true); // Status aktif/non-aktif
            $table->boolean('is_system')->default(false); // Apakah ini system level (tidak bisa dihapus)
            $table->timestamps();
            $table->softDeletes(); // Soft delete untuk user levels

            // Index untuk performance
            $table->index(['is_active', 'priority']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_levels');
    }
};
