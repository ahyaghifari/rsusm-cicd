<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konsolidasi dari 5 migrasi lama yang masing-masing cuma ubah 1-2 hal di tabel
     * `dokter` (lihat issues/migration-cleanup-plan.md). Tiap langkah dijaga
     * `hasColumn`/`hasIndex` agar idempotent — aman no-op di environment yang sudah
     * pernah jalankan migrasi lama (production), dan tetap lengkap membuat kolomnya
     * di environment baru/fresh yang tidak punya file migrasi lama itu lagi.
     */
    public function up(): void
    {
        Schema::table('dokter', function (Blueprint $table) {
            if (! Schema::hasColumn('dokter', 'dapat_konsultasi')) {
                $table->boolean('dapat_konsultasi')->default(false)->after('aktif');
            }
            if (! Schema::hasColumn('dokter', 'tersedia_konsultasi')) {
                $table->boolean('tersedia_konsultasi')->default(false)->after('aktif');
            }
            if (! Schema::hasColumn('dokter', 'durasi_sesi_menit')) {
                $table->unsignedInteger('durasi_sesi_menit')->default(30)->after('tersedia_konsultasi');
            }
            if (! Schema::hasColumn('dokter', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('durasi_sesi_menit')
                    ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('dokter', 'kuota_pasien')) {
                $table->text('kuota_pasien')->nullable()->after('deskripsi');
            }
            if (! Schema::hasColumn('dokter', 'nomor_poli_antrian')) {
                $table->integer('nomor_poli_antrian')->nullable()->after('kuota_pasien')
                    ->comment('Identifier poli dokter ini di sistem API antrian, untuk tampilan live antrian');
            }
        });

        // spesialis_id: awalnya NOT NULL + cascadeOnDelete, jadi nullable + nullOnDelete
        $kolomSpesialis = collect(Schema::getColumns('dokter'))->firstWhere('name', 'spesialis_id');
        if ($kolomSpesialis && ! $kolomSpesialis['nullable']) {
            Schema::table('dokter', function (Blueprint $table) {
                $table->dropForeign(['spesialis_id']);
            });
            Schema::table('dokter', function (Blueprint $table) {
                $table->unsignedBigInteger('spesialis_id')->nullable()->change();
            });
            Schema::table('dokter', function (Blueprint $table) {
                $table->foreign('spesialis_id')->references('id')->on('spesialis')->nullOnDelete();
            });
        }

        // slug: unique tunggal -> composite (slug, rumah_sakit_id)
        if (! Schema::hasIndex('dokter', ['slug', 'rumah_sakit_id'], 'unique')) {
            try {
                Schema::table('dokter', fn (Blueprint $table) => $table->dropUnique(['slug']));
            } catch (\Exception) {
                // index single-column sudah tidak ada
            }

            Schema::table('dokter', fn (Blueprint $table) => $table->unique(['slug', 'rumah_sakit_id']));
        }
    }

    public function down(): void
    {
        // Migrasi konsolidasi yang idempotent — tidak ada rollback simetris yang aman
        // (tidak bisa dibedakan kolom yang dibuat migrasi ini vs migrasi lama yang sudah
        // dihapus dari direktori ini). Lihat issues/migration-cleanup-plan.md.
    }
};
