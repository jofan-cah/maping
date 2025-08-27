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
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom user_level_id
            $table->string('user_level_id')->nullable()->after('is_active');

            // Tambahkan foreign key constraint
            $table->foreign('user_level_id')
                  ->references('user_level_id')
                  ->on('user_levels')
                  ->onDelete('set null');

            // Tambahkan index untuk performance
            $table->index(['is_active', 'user_level_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key dan index
            $table->dropForeign(['user_level_id']);
            $table->dropIndex(['is_active', 'user_level_id']);

            // Drop kolom
            $table->dropColumn('user_level_id');
        });
    }
};
