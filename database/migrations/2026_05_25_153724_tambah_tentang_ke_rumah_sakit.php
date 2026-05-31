<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rumah_sakit', function (Blueprint $table) {
            $table->text('tentang_kami')->nullable()->after('lokasi_google_map');
            $table->string('gambar_tentang', 255)->nullable()->after('tentang_kami');
        });
    }

    public function down(): void
    {
        Schema::table('rumah_sakit', function (Blueprint $table) {
            $table->dropColumn(['tentang_kami', 'gambar_tentang']);
        });
    }
};
