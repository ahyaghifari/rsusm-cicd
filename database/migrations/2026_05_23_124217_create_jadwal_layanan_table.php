<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poliklinik_id')->constrained('poliklinik')->cascadeOnDelete();
            $table->enum('hari', ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'MINGGU']);
            $table->foreignId('dokter_id')->nullable()->constrained('dokter')->nullOnDelete();
            $table->string('nama_dokter', 255)->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->enum('status_layanan', ['BUKA', 'LIBUR'])->default('BUKA');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_layanan');
    }
};
