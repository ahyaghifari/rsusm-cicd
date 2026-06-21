<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data ketersediaan rawat inap tidak disimpan ke database — selalu diambil langsung
     * dari API/fixture Ranap saat halaman diakses. Lihat
     * issues/ketersediaan-rawat-inap-plan.md.
     */
    public function up(): void
    {
        Schema::dropIfExists('rawat_inap_ketersediaan');
    }

    public function down(): void
    {
        Schema::create('rawat_inap_ketersediaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rumah_sakit_id')->constrained('rumah_sakit')->cascadeOnDelete();
            $table->integer('external_id');
            $table->integer('ruang_kamar');
            $table->string('tempat_tidur', 100);
            $table->unsignedTinyInteger('status');
            $table->timestamp('tanggal_update_api')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('ruangan', 50);
            $table->string('nama_kamar', 150);
            $table->foreignId('kelas_rawat_inap_id')->nullable()->constrained('kelas_rawat_inap')->nullOnDelete();
            $table->timestamp('synced_at');
            $table->timestamps();

            $table->unique(['external_id', 'rumah_sakit_id']);
        });
    }
};
