<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membedakan template poster jadwal harian (poster mingguan lengkap) dari
     * template poster perubahan jadwal (hanya menampilkan baris yang berubah).
     * Lihat knowledge/poster/POSTER-SYSTEM.md.
     */
    public function up(): void
    {
        Schema::table('poster_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('poster_templates', 'jenis')) {
                $table->enum('jenis', ['JADWAL_HARIAN', 'PERUBAHAN_JADWAL'])
                    ->default('JADWAL_HARIAN')
                    ->after('rumah_sakit_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('poster_templates', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
    }
};
