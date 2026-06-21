<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konsultasi_pesan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_id')->constrained('sesi_konsultasi')->cascadeOnDelete();
            $table->string('pengirim', 20);
            $table->text('isi');
            $table->timestamp('dibaca_at')->nullable();
            $table->timestamps();

            $table->index('sesi_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konsultasi_pesan');
    }
};
