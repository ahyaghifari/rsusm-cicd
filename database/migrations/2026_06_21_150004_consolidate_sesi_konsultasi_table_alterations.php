<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konsolidasi dari 3 migrasi lama yang masing-masing nambah 1 kolom ke tabel
     * `sesi_konsultasi`. Lihat issues/migration-cleanup-plan.md.
     */
    public function up(): void
    {
        Schema::table('sesi_konsultasi', function (Blueprint $table) {
            if (! Schema::hasColumn('sesi_konsultasi', 'kesimpulan')) {
                $table->text('kesimpulan')->nullable()->after('berakhir_at');
            }
            if (! Schema::hasColumn('sesi_konsultasi', 'push_subscription')) {
                $table->text('push_subscription')->nullable()->after('kesimpulan');
            }
            if (! Schema::hasColumn('sesi_konsultasi', 'dokter_baca_at')) {
                $table->timestamp('dokter_baca_at')->nullable()->after('push_subscription');
            }
        });
    }

    public function down(): void
    {
        // Lihat catatan di migrasi konsolidasi dokter — rollback simetris tidak aman
        // untuk migrasi idempotent seperti ini.
    }
};
