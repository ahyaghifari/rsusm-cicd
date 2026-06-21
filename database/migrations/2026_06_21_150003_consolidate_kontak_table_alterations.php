<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konsolidasi dari 3 migrasi lama (sort_order + 2x penambahan kategori enum) untuk
     * tabel `kontak`. Lihat issues/migration-cleanup-plan.md.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('kontak', 'sort_order')) {
            Schema::table('kontak', function (Blueprint $table) {
                $table->unsignedSmallInteger('sort_order')->default(0)->after('id');
            });
        }

        // SQLite tidak enforce enum (base create_kontak_table sudah memuat kategori
        // final). MySQL MODIFY COLUMN aman diulang — tidak error walau enum sudah sesuai.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE kontak MODIFY COLUMN kategori ENUM('SOSIAL MEDIA', 'OPERASIONAL', 'PENDAFTARAN', 'RAWAT INAP') NOT NULL");
        }
    }

    public function down(): void
    {
        // Lihat catatan di migrasi konsolidasi dokter — rollback simetris tidak aman
        // untuk migrasi idempotent seperti ini.
    }
};
