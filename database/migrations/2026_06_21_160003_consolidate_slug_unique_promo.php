<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konsolidasi bagian PROMO dari fix_slug_unique_to_composite_promo_poliklinik.
     * Bagian POLIKLINIK dari migrasi lama itu tidak direplikasi di sini karena sudah
     * digantikan sepenuhnya sehari kemudian oleh refactor_poliklinik_and_executive_clinic
     * (poliklinik berakhir di unique (slug, rumah_sakit_id), bukan (slug,
     * unit_layanan_id) — lihat consolidate_poliklinik_refactor_and_executive_clinic).
     * Detail: issues/migration-cleanup-plan.md.
     */
    public function up(): void
    {
        if (! Schema::hasIndex('promo', ['slug', 'rumah_sakit_id'], 'unique')) {
            try {
                Schema::table('promo', fn (Blueprint $table) => $table->dropUnique(['slug']));
            } catch (\Exception) {
                // index single-column sudah tidak ada
            }

            Schema::table('promo', fn (Blueprint $table) => $table->unique(['slug', 'rumah_sakit_id']));
        }
    }

    public function down(): void
    {
        // Lihat catatan di migrasi konsolidasi dokter — rollback simetris tidak aman
        // untuk migrasi idempotent seperti ini.
    }
};
