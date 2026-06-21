<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konsolidasi dari add_sumber_to_jadwal_harian_table. Lihat
     * issues/migration-cleanup-plan.md.
     *
     * Catatan urutan: kolom `sumber` harus ada SEBELUM migrasi konsolidasi refactor
     * poliklinik/executive_clinic jalan (dia menaruh kolom `is_executive` dengan
     * `->after('sumber')`) — makanya timestamp file ini sengaja ditaruh lebih awal.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('jadwal_harian', 'sumber')) {
            Schema::table('jadwal_harian', function (Blueprint $table) {
                $table->enum('sumber', ['GENERATE', 'MANUAL'])->default('GENERATE')->after('catatan');
            });
        }
    }

    public function down(): void
    {
        // Lihat catatan di migrasi konsolidasi dokter — rollback simetris tidak aman
        // untuk migrasi idempotent seperti ini.
    }
};
