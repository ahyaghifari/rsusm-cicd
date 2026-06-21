<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokter', function (Blueprint $table) {
            $table->boolean('tersedia_konsultasi')->default(false)->after('aktif');
            $table->unsignedInteger('durasi_sesi_menit')->default(30)->after('tersedia_konsultasi');
            $table->foreignId('user_id')->nullable()->after('durasi_sesi_menit')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dokter', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['tersedia_konsultasi', 'durasi_sesi_menit', 'user_id']);
        });
    }
};
