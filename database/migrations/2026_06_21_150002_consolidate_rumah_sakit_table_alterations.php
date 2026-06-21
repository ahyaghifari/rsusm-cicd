<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konsolidasi dari 4 migrasi lama yang masing-masing cuma nambah 1-2 kolom ke
     * tabel `rumah_sakit` (lihat issues/migration-cleanup-plan.md). Idempotent lewat
     * `hasColumn` — aman no-op di production yang sudah punya kolom-kolom ini.
     */
    public function up(): void
    {
        Schema::table('rumah_sakit', function (Blueprint $table) {
            if (! Schema::hasColumn('rumah_sakit', 'tanya_dokter_aktif')) {
                $table->boolean('tanya_dokter_aktif')->default(false)->after('executive_clinic');
            }
            if (! Schema::hasColumn('rumah_sakit', 'jadwal_poliklinik_gambar')) {
                $table->string('jadwal_poliklinik_gambar')->nullable()->after('tanya_dokter_aktif');
            }
            if (! Schema::hasColumn('rumah_sakit', 'jadwal_poliklinik_aktif')) {
                $table->boolean('jadwal_poliklinik_aktif')->default(false)->after('jadwal_poliklinik_gambar');
            }
            if (! Schema::hasColumn('rumah_sakit', 'google_place_id')) {
                $table->string('google_place_id', 255)->nullable();
            }
            if (! Schema::hasColumn('rumah_sakit', 'ranap_kode_api')) {
                $table->string('ranap_kode_api', 50)->nullable()
                    ->comment('Identifier RS di URL API Ranap, contoh: "rsa" -> {base_url}/rsa/bed');
            }
            if (! Schema::hasColumn('rumah_sakit', 'link_antrian')) {
                $table->string('link_antrian')->nullable()
                    ->comment('URL eksternal pantauan antrian poliklinik per RS');
            }
        });
    }

    public function down(): void
    {
        // Lihat catatan di migrasi konsolidasi dokter — rollback simetris tidak aman
        // untuk migrasi idempotent seperti ini.
    }
};
