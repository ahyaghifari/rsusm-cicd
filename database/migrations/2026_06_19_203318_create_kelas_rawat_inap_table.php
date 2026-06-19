<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas_rawat_inap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rumah_sakit_id')->constrained('rumah_sakit')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->integer('id_kelas_api')->nullable();
            $table->boolean('is_vip')->default(false);
            $table->timestamps();

            $table->unique(['id_kelas_api', 'rumah_sakit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas_rawat_inap');
    }
};
