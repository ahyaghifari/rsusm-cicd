<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rumah_sakit', function (Blueprint $table) {
            $table->boolean('tanya_dokter_aktif')->default(false)->after('executive_clinic');
        });
    }

    public function down(): void
    {
        Schema::table('rumah_sakit', function (Blueprint $table) {
            $table->dropColumn('tanya_dokter_aktif');
        });
    }
};
