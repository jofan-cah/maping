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
        Schema::create('mitra_turunans', function (Blueprint $table) {
            $table->string('mitra_turunan_id')->primary();
            $table->string('mitra_id');
            $table->string('koordinat');
            $table->string('nama_point')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('nama_file')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('mitra_id')->references('mitra_id')->on('mitras')->onDelete('cascade');

            // Indexes
            $table->index('mitra_id', 'idx_mitra_turunans_mitra_id');
            $table->index('koordinat', 'idx_mitra_turunans_koordinat');
            $table->index('nama_point', 'idx_mitra_turunans_nama_point');
            $table->index(['mitra_id', 'koordinat'], 'idx_mitra_turunans_mitra_koordinat');
            $table->index('created_at', 'idx_mitra_turunans_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitra_turunans');
    }
};
