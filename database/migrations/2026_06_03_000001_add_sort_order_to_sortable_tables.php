<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'magazines',
            'link_layanan',
            'layanan_unggulan',
            'fasilitas_pendukung',
            'penunjang_medis',
            'gedung',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedSmallInteger('sort_order')->default(0)->after('id');
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'magazines',
            'link_layanan',
            'layanan_unggulan',
            'fasilitas_pendukung',
            'penunjang_medis',
            'gedung',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('sort_order');
            });
        }
    }
};
