<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesi_konsultasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rumah_sakit_id')->constrained('rumah_sakit')->cascadeOnDelete();
            $table->foreignId('dokter_id')->constrained('dokter')->cascadeOnDelete();
            $table->uuid('token')->unique();
            $table->string('nama_pasien', 100);
            $table->string('kontak_pasien', 100);
            $table->string('status', 20)->default('MENUNGGU');
            $table->unsignedInteger('durasi_menit');
            $table->foreignId('dibalas_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('mulai_at')->nullable();
            $table->timestamp('berakhir_at')->nullable();
            $table->timestamps();

            $table->index(['dokter_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesi_konsultasi');
    }
};
