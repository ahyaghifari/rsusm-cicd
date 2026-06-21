<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Tambah executive_clinic ke rumah_sakit ─────────────────────────
        Schema::table('rumah_sakit', function (Blueprint $table) {
            $table->boolean('executive_clinic')->default(false)->after('aktif');
        });

        // ── 2. Tambah rumah_sakit_id ke poliklinik (tanpa FK dulu) ──────────
        Schema::table('poliklinik', function (Blueprint $table) {
            $table->unsignedBigInteger('rumah_sakit_id')
                  ->nullable()
                  ->after('id');
        });

        // ── 3. Isi rumah_sakit_id dari relasi unit_layanan ────────────────────
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

        // ── 4. Jadikan NOT NULL (MySQL only — SQLite tidak support MODIFY COLUMN) ──
        if ($driver === 'mysql') {
            DB::statement('
                ALTER TABLE poliklinik
                MODIFY COLUMN rumah_sakit_id BIGINT UNSIGNED NOT NULL
            ');
        }

        // ── 4b. Pasang FK setelah kolom sudah NOT NULL ────────────────────────
        Schema::table('poliklinik', function (Blueprint $table) {
            $table->foreign('rumah_sakit_id')
                  ->references('id')
                  ->on('rumah_sakit')
                  ->cascadeOnDelete();
        });

        // ── 5. Ganti unique constraint (slug, unit_layanan_id)
        //       → (slug, rumah_sakit_id) ─────────────────────────────────────
        Schema::table('poliklinik', function (Blueprint $table) {
            try {
                $table->dropUnique('poliklinik_slug_unit_layanan_id_unique');
            } catch (\Exception) {}

            try {
                $table->unique(['slug', 'rumah_sakit_id']);
            } catch (\Exception) {}
        });

        // ── 6. Drop FK dan kolom unit_layanan_id ──────────────────────────────
        Schema::table('poliklinik', function (Blueprint $table) {
            $table->dropForeign(['unit_layanan_id']);
            $table->dropColumn('unit_layanan_id');
        });

        // ── 7. Tambah is_executive ke jadwal_praktek ──────────────────────────
        Schema::table('jadwal_praktek', function (Blueprint $table) {
            $table->boolean('is_executive')->default(false)->after('sesuai_perjanjian');
        });

        // ── 8. Tambah is_executive ke jadwal_harian ───────────────────────────
        Schema::table('jadwal_harian', function (Blueprint $table) {
            $table->boolean('is_executive')->default(false)->after('sumber');
        });
    }

    public function down(): void
    {
        // Hapus is_executive dari jadwal
        Schema::table('jadwal_harian', function (Blueprint $table) {
            $table->dropColumn('is_executive');
        });

        Schema::table('jadwal_praktek', function (Blueprint $table) {
            $table->dropColumn('is_executive');
        });

        // Kembalikan unit_layanan_id ke poliklinik (nilai NULL — tidak bisa dipulihkan)
        Schema::table('poliklinik', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_layanan_id')
                  ->nullable()
                  ->after('id');

            $table->foreign('unit_layanan_id')
                  ->references('id')
                  ->on('unit_layanan')
                  ->cascadeOnDelete();

            try {
                $table->dropUnique(['slug', 'rumah_sakit_id']);
            } catch (\Exception) {}

            $table->unique(['slug', 'unit_layanan_id']);
        });

        Schema::table('poliklinik', function (Blueprint $table) {
            $table->dropForeign(['rumah_sakit_id']);
            $table->dropColumn('rumah_sakit_id');
        });

        // Hapus executive_clinic dari rumah_sakit
        Schema::table('rumah_sakit', function (Blueprint $table) {
            $table->dropColumn('executive_clinic');
        });
    }
};
