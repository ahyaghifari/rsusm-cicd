<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konsolidasi dari add_rumah_sakit_id_and_role_to_users_table. Lihat
     * issues/migration-cleanup-plan.md.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'rumah_sakit_id')) {
                $table->foreignId('rumah_sakit_id')->nullable()->after('id')
                    ->constrained('rumah_sakit')->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('admin')->after('password');
            }
        });
    }

    public function down(): void
    {
        // Lihat catatan di migrasi konsolidasi dokter — rollback simetris tidak aman
        // untuk migrasi idempotent seperti ini.
    }
};
