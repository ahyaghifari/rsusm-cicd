<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poster_generate', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rumah_sakit_id')
                ->constrained('rumah_sakit')
                ->cascadeOnDelete();
            $table->foreignId('poster_template_id')
                ->nullable()
                ->constrained('poster_templates')
                ->nullOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('jenis', 50)->default('JADWAL_HARIAN');
            $table->string('kategori_klinik', 20)->default('REGULER');
            $table->date('tanggal');
            $table->string('nama_file');
            $table->string('path');
            $table->unsignedInteger('halaman')->default(1);
            $table->timestamps();

            $table->unique(['rumah_sakit_id', 'jenis', 'kategori_klinik', 'tanggal', 'halaman']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poster_generate');
    }
};
