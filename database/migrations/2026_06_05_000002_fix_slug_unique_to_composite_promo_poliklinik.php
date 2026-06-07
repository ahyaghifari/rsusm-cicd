<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Promo: drop single slug unique jika ada, add (slug, rumah_sakit_id)
        try {
            Schema::table('promo', function (Blueprint $table) {
                $table->dropUnique(['slug']);
            });
        } catch (\Exception) {
            // Index tidak ada (fresh install sudah pakai composite)
        }
        try {
            Schema::table('promo', function (Blueprint $table) {
                $table->unique(['slug', 'rumah_sakit_id']);
            });
        } catch (\Exception) {
            // Composite unique sudah ada
        }

        // Poliklinik: drop single slug unique jika ada, add (slug, unit_layanan_id)
        try {
            Schema::table('poliklinik', function (Blueprint $table) {
                $table->dropUnique(['slug']);
            });
        } catch (\Exception) {
            // Index tidak ada
        }
        try {
            Schema::table('poliklinik', function (Blueprint $table) {
                $table->unique(['slug', 'unit_layanan_id']);
            });
        } catch (\Exception) {
            // Composite unique sudah ada
        }
    }

    public function down(): void
    {
        Schema::table('promo', function (Blueprint $table) {
            $table->dropUnique(['slug', 'rumah_sakit_id']);
            $table->unique(['slug']);
        });

        Schema::table('poliklinik', function (Blueprint $table) {
            $table->dropUnique(['slug', 'unit_layanan_id']);
            $table->unique(['slug']);
        });
    }
};
