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
        Schema::create('artikel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rumah_sakit_id')->constrained('rumah_sakit')->cascadeOnDelete();
            $table->foreignId('kategori_artikel_id')->nullable()->constrained('kategori_artikel')->nullOnDelete();
            $table->string('judul', 255);
            $table->string('slug', 255);
            $table->text('ringkasan')->nullable();
            $table->longText('konten');
            $table->string('gambar', 255)->nullable();
            $table->string('penulis', 100)->nullable();
            $table->date('tanggal_publish')->default(now());
            $table->boolean('unggulan')->default(false);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['slug', 'rumah_sakit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artikel');
    }
};
