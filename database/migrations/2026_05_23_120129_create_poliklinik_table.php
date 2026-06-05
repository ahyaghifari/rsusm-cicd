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
        Schema::create('poliklinik', function (Blueprint $table) {
            $table->id();
            // Asumsi foreign key merujuk ke tabel unit_layanan
            $table->foreignId('unit_layanan_id')->constrained('unit_layanan')->onDelete('cascade');
            $table->string('nama', 255);
            $table->string('slug', 255);
            $table->unique(['slug', 'unit_layanan_id']);
            $table->string('gambar', 255)->nullable();
            $table->text('deskripsi');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poliklinik');
    }
};