<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konsolidasi dari add_soft_deletes_to_dokter_spesialis_poliklinik. Lihat
     * issues/migration-cleanup-plan.md.
     */
    public function up(): void
    {
        foreach (['dokter', 'spesialis', 'poliklinik'] as $table) {
            if (! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes()->after('updated_at');
                });
            }
        }
    }

    public function down(): void
    {
        // Lihat catatan di migrasi konsolidasi dokter — rollback simetris tidak aman
        // untuk migrasi idempotent seperti ini.
    }
};
