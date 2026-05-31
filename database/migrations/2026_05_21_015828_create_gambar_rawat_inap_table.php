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
        Schema::create('gambar_rawat_inap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rawat_inap_id')->constrained('rawat_inap')->cascadeOnDelete();
            $table->string('gambar', 255);
            $table->text('deskripsi')->nullable();
            $table->integer('sort_order');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gambar_rawat_inap');
    }
};
