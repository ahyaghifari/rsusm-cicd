<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_harian_perubahan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('jadwal_harian_id')
                  ->unique()
                  ->constrained('jadwal_harian')
                  ->cascadeOnDelete();

            $table->enum('jenis', ['GENERATE', 'TAMBAH', 'UBAH']);

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Nilai BARU — diisi untuk jenis UBAH
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->enum('status_layanan', ['BUKA', 'LIBUR'])->nullable();

            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_harian_perubahan');
    }
};
