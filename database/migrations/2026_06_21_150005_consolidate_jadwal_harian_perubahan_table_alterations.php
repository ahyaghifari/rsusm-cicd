<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konsolidasi dari 2 migrasi lama untuk tabel `jadwal_harian_perubahan`. Lihat
     * issues/migration-cleanup-plan.md.
     */
    public function up(): void
    {
        Schema::table('jadwal_harian_perubahan', function (Blueprint $table) {
            // Kolom 'jenis' tidak pernah ada di migrasi create — guard ini cuma warisan
            // defensif dari migrasi lama, selalu no-op, dipertahankan untuk kesetiaan.
            if (Schema::hasColumn('jadwal_harian_perubahan', 'jenis')) {
                $table->dropColumn('jenis');
            }
            if (! Schema::hasColumn('jadwal_harian_perubahan', 'jam_mulai_asli')) {
                $table->time('jam_mulai_asli')->nullable()->after('jadwal_harian_id');
            }
            if (! Schema::hasColumn('jadwal_harian_perubahan', 'jam_selesai_asli')) {
                $table->time('jam_selesai_asli')->nullable()->after('jam_mulai_asli');
            }
            if (! Schema::hasColumn('jadwal_harian_perubahan', 'status_layanan_asli')) {
                $table->enum('status_layanan_asli', ['BUKA', 'LIBUR'])->nullable()->after('jam_selesai_asli');
            }
        });
    }

    public function down(): void
    {
        // Lihat catatan di migrasi konsolidasi dokter — rollback simetris tidak aman
        // untuk migrasi idempotent seperti ini.
    }
};
