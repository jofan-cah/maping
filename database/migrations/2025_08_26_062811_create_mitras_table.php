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
        Schema::create('mitras', function (Blueprint $table) {
            $table->string('mitra_id')->primary();
            $table->string('nama_pt');
            $table->string('warna_pt')->nullable(); // Warna dalam format hex code, contoh: #FFFFFF
            $table->string('icon_pt')->nullable();   // Path atau URL ke icon PT
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitras');
    }
};
