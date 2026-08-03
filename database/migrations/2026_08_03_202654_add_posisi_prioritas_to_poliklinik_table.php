<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poliklinik', function (Blueprint $table) {
            $table->string('posisi_prioritas', 5)->nullable()->after('prioritas_poster');
        });
    }

    public function down(): void
    {
        Schema::table('poliklinik', function (Blueprint $table) {
            $table->dropColumn('posisi_prioritas');
        });
    }
};