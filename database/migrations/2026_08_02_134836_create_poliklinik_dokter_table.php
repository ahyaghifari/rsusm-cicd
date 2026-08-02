<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poliklinik_dokter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poliklinik_id')->constrained('poliklinik')->cascadeOnDelete();
            $table->foreignId('dokter_id')->constrained('dokter')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['poliklinik_id', 'dokter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poliklinik_dokter');
    }
};
