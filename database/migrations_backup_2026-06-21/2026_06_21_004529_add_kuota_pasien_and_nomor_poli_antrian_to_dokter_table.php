<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokter', function (Blueprint $table) {
            $table->text('kuota_pasien')->nullable()->after('deskripsi');
            $table->integer('nomor_poli_antrian')->nullable()->after('kuota_pasien')
                ->comment('Identifier poli dokter ini di sistem API antrian, untuk tampilan live antrian');
        });
    }

    public function down(): void
    {
        Schema::table('dokter', function (Blueprint $table) {
            $table->dropColumn(['kuota_pasien', 'nomor_poli_antrian']);
        });
    }
};
