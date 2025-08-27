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
        Schema::table('mitra_turunans', function (Blueprint $table) {
            //
               $table->string('type_point')->nullable()->after('nama_file');
            $table->index('type_point', 'idx_mitra_turunans_type_point');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mitra_turunans', function (Blueprint $table) {
            //
             $table->dropIndex('idx_mitra_turunans_type_point');
            $table->dropColumn('type_point');
        });
    }
};
