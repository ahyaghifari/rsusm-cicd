<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konsolidasi dari add_default_to_sort_order_in_rawat_inap_tables. Lihat
     * issues/migration-cleanup-plan.md.
     */
    public function up(): void
    {
        foreach (['rawat_inap', 'gambar_rawat_inap'] as $table) {
            $kolom = collect(Schema::getColumns($table))->firstWhere('name', 'sort_order');

            if ($kolom && $kolom['default'] === null) {
                Schema::table($table, function (Blueprint $t) {
                    $t->integer('sort_order')->default(0)->change();
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
