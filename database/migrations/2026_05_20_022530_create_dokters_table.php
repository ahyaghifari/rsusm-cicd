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
        Schema::create('dokter', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 255);
            $table->string('slug', 255)->unique();
            $table->string('foto', 255)->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->text('pendidikan')->nullable();
            $table->text('pelatihan')->nullable();
            $table->foreignId('rumah_sakit_id')->constrained('rumah_sakit')->cascadeOnDelete();
            $table->foreignId('spesialis_id')->constrained('spesialis')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokter');
    }
};
