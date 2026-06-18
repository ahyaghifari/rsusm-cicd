<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('dokter', function (Blueprint $table) {
                $table->dropUnique(['slug']);
            });
        } catch (\Exception) {
            // Index tidak ada (fresh install sudah pakai composite)
        }

        try {
            Schema::table('dokter', function (Blueprint $table) {
                $table->unique(['slug', 'rumah_sakit_id']);
            });
        } catch (\Exception) {
            // Composite unique sudah ada
        }
    }

    public function down(): void
    {
        try {
            Schema::table('dokter', function (Blueprint $table) {
                $table->dropUnique(['slug', 'rumah_sakit_id']);
            });
        } catch (\Exception) {
            // Index tidak ada
        }

        try {
            Schema::table('dokter', function (Blueprint $table) {
                $table->unique(['slug']);
            });
        } catch (\Exception) {
            // Index sudah ada
        }
    }
};
