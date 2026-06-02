<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kontak', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rumah_sakit_id')->constrained('rumah_sakit')->cascadeOnDelete();
            $table->string('label', 255);
            $table->string('value', 255);
            $table->string('gambar', 255)->nullable();
            $table->longText('logo')->nullable();
            $table->longText('link')->nullable();
            $table->enum('kategori', ['SOSIAL MEDIA', 'OPERASIONAL', 'PENDAFTARAN']);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kontak');
    }
};
