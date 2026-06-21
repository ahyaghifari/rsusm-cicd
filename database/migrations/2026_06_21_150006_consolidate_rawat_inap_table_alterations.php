<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konsolidasi dari 2 migrasi lama untuk tabel `rawat_inap`: tambah FK
     * `kelas_rawat_inap_id`, lalu drop kolom `kelas` (string) yang lama. Lihat
     * issues/migration-cleanup-plan.md.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('rawat_inap', 'kelas_rawat_inap_id')) {
            Schema::table('rawat_inap', function (Blueprint $table) {
                $table->foreignId('kelas_rawat_inap_id')->nullable()
                    ->after(Schema::hasColumn('rawat_inap', 'kelas') ? 'kelas' : 'gedung_id')
                    ->constrained('kelas_rawat_inap')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('rawat_inap', 'kelas')) {
            Schema::table('rawat_inap', function (Blueprint $table) {
                $table->dropColumn('kelas');
            });
        }
    }

    public function down(): void
    {
        // Lihat catatan di migrasi konsolidasi dokter — rollback simetris tidak aman
        // untuk migrasi idempotent seperti ini.
    }
};
