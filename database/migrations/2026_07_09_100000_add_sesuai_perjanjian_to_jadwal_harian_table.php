<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('jadwal_harian', 'sesuai_perjanjian')) {
            Schema::table('jadwal_harian', function (Blueprint $table) {
                $table->boolean('sesuai_perjanjian')->default(false)->after('jam_selesai');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('jadwal_harian', 'sesuai_perjanjian')) {
            Schema::table('jadwal_harian', function (Blueprint $table) {
                $table->dropColumn('sesuai_perjanjian');
            });
        }
    }
};
