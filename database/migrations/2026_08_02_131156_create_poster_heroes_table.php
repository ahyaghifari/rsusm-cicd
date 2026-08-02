<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poster_heroes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rumah_sakit_id')
                  ->constrained('rumah_sakit')
                  ->cascadeOnDelete();
            $table->string('nama');
            $table->string('foto');
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poster_heroes');
    }
};