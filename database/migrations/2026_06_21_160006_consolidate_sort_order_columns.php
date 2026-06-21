<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konsolidasi dari add_sort_order_to_sortable_tables. Lihat
     * issues/migration-cleanup-plan.md.
     */
    public function up(): void
    {
        $tables = [
            'magazines',
            'link_layanan',
            'layanan_unggulan',
            'fasilitas_pendukung',
            'penunjang_medis',
            'gedung',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasColumn($table, 'sort_order')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedSmallInteger('sort_order')->default(0)->after('id');
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
