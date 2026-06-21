<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rumah_sakit', function (Blueprint $table) {
            $table->string('jadwal_poliklinik_gambar')->nullable()->after('tanya_dokter_aktif');
            $table->boolean('jadwal_poliklinik_aktif')->default(false)->after('jadwal_poliklinik_gambar');
        });
    }

    public function down(): void
    {
        Schema::table('rumah_sakit', function (Blueprint $table) {
            $table->dropColumn(['jadwal_poliklinik_gambar', 'jadwal_poliklinik_aktif']);
        });
    }
};
