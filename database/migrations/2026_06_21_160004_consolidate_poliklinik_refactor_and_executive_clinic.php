<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konsolidasi dari refactor_poliklinik_and_executive_clinic. Migrasi ini punya
     * backfill data (poliklinik.rumah_sakit_id diisi dari unit_layanan.rumah_sakit_id)
     * yang BUTUH kolom unit_layanan_id masih ada saat backfill jalan — makanya seluruh
     * blok poliklinik digerbang satu kondisi besar (`! hasColumn('poliklinik',
     * 'rumah_sakit_id')`), bukan per-langkah, supaya tidak pernah mencoba membaca
     * unit_layanan_id di environment yang sudah pernah jalankan migrasi lama (kolom itu
     * sudah lama didrop di sana). Lihat issues/migration-cleanup-plan.md.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('rumah_sakit', 'executive_clinic')) {
            Schema::table('rumah_sakit', function (Blueprint $table) {
                $table->boolean('executive_clinic')->default(false)->after('aktif');
            });
        }

        if (! Schema::hasColumn('poliklinik', 'rumah_sakit_id')) {
            Schema::table('poliklinik', function (Blueprint $table) {
                $table->unsignedBigInteger('rumah_sakit_id')->nullable()->after('id');
            });

            $driver = DB::connection()->getDriverName();

            if ($driver === 'mysql') {
                DB::statement('
                    UPDATE poliklinik p
                    INNER JOIN unit_layanan ul ON ul.id = p.unit_layanan_id
                    SET p.rumah_sakit_id = ul.rumah_sakit_id
                ');
            } else {
                DB::statement('
                    UPDATE poliklinik
                    SET rumah_sakit_id = (
                        SELECT unit_layanan.rumah_sakit_id
                        FROM unit_layanan
                        WHERE unit_layanan.id = poliklinik.unit_layanan_id
                    )
                    WHERE unit_layanan_id IS NOT NULL
                ');
            }

            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE poliklinik MODIFY COLUMN rumah_sakit_id BIGINT UNSIGNED NOT NULL');
            }

            Schema::table('poliklinik', function (Blueprint $table) {
                $table->foreign('rumah_sakit_id')->references('id')->on('rumah_sakit')->cascadeOnDelete();
            });
        }

        // Unique key final: (slug, rumah_sakit_id) — lompat langsung, tidak lewat
        // langkah antara (slug, unit_layanan_id) yang sudah usang (lihat
        // consolidate_slug_unique_promo untuk konteks).
        if (! Schema::hasIndex('poliklinik', ['slug', 'rumah_sakit_id'], 'unique')) {
            try {
                Schema::table('poliklinik', fn (Blueprint $table) => $table->dropUnique(['slug']));
            } catch (\Exception) {
            }
            try {
                Schema::table('poliklinik', fn (Blueprint $table) => $table->dropUnique(['slug', 'unit_layanan_id']));
            } catch (\Exception) {
            }

            Schema::table('poliklinik', fn (Blueprint $table) => $table->unique(['slug', 'rumah_sakit_id']));
        }

        if (Schema::hasColumn('poliklinik', 'unit_layanan_id')) {
            Schema::table('poliklinik', function (Blueprint $table) {
                try {
                    $table->dropForeign(['unit_layanan_id']);
                } catch (\Exception) {
                }
                $table->dropColumn('unit_layanan_id');
            });
        }

        if (! Schema::hasColumn('jadwal_praktek', 'is_executive')) {
            Schema::table('jadwal_praktek', function (Blueprint $table) {
                $table->boolean('is_executive')->default(false)->after('sesuai_perjanjian');
            });
        }

        if (! Schema::hasColumn('jadwal_harian', 'is_executive')) {
            Schema::table('jadwal_harian', function (Blueprint $table) {
                $table->boolean('is_executive')->default(false)->after('sumber');
            });
        }
    }

    public function down(): void
    {
        // Lihat catatan di migrasi konsolidasi dokter — rollback simetris tidak aman
        // untuk migrasi idempotent seperti ini.
    }
};
