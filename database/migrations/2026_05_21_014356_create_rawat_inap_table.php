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
        Schema::create('rawat_inap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rumah_sakit_id')->constrained('rumah_sakit')->cascadeOnDelete();
            $table->foreignId('gedung_id')->nullable()->constrained('gedung')->nullOnDelete();
            $table->string('nama', 255);
            $table->string('kelas', 255);
            $table->decimal('harga', 10, 2);
            $table->smallInteger('kapasitas');
            $table->text('fasilitas');
            $table->string('thumbnail', 255)->nullable();
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
        Schema::dropIfExists('rawat_inap');
    }
};
