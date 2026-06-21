<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rumah_sakit', function (Blueprint $table) {
            $table->string('ranap_kode_api', 50)->nullable()
                ->comment('Identifier RS di URL API Ranap, contoh: "rsa" -> {base_url}/rsa/bed');
            $table->string('link_antrian')->nullable()
                ->comment('URL eksternal pantauan antrian poliklinik per RS');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rumah_sakit', function (Blueprint $table) {
            $table->dropColumn(['ranap_kode_api', 'link_antrian']);
        });
    }
};
