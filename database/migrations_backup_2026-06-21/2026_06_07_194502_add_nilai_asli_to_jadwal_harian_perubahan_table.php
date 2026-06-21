<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jadwal_harian_perubahan', function (Blueprint $table) {
            $table->time('jam_mulai_asli')->nullable()->after('jadwal_harian_id');
            $table->time('jam_selesai_asli')->nullable()->after('jam_mulai_asli');
            $table->enum('status_layanan_asli', ['BUKA', 'LIBUR'])->nullable()->after('jam_selesai_asli');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_harian_perubahan', function (Blueprint $table) {
            $table->dropColumn(['jam_mulai_asli', 'jam_selesai_asli', 'status_layanan_asli']);
        });
    }
};
