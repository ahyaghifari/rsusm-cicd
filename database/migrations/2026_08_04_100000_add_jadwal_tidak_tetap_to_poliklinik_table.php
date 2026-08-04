<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menandai poliklinik yang jadwal dokternya tidak mengikuti pola mingguan
     * tetap (mis. poli umum) — diisi lewat halaman Jadwal Prioritas, bukan
     * Jadwal Praktek/Jadwal Harian biasa. Flag ini dipakai untuk mengecualikan
     * poliklinik tsb dari scope replace-all "Simpan"/"Kosongkan"/"Muat dari
     * Jadwal Mingguan" di Jadwal Harian, supaya kedua halaman benar-benar
     * tidak saling sentuh datanya.
     */
    public function up(): void
    {
        Schema::table('poliklinik', function (Blueprint $table) {
            if (! Schema::hasColumn('poliklinik', 'jadwal_tidak_tetap')) {
                $table->boolean('jadwal_tidak_tetap')->default(false)->after('aktif');
            }
        });
    }

    public function down(): void
    {
        Schema::table('poliklinik', function (Blueprint $table) {
            $table->dropColumn('jadwal_tidak_tetap');
        });
    }
};
