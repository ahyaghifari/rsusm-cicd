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
        Schema::create('layanan_unggulan', function (Blueprint $table) {
            $table->id(); // integer auto increment primary key
            
            // Foreign key ke tabel rumah_sakit (cascade delete jika rumah sakit dihapus)
            $table->foreignId('rumah_sakit_id')
                  ->constrained('rumah_sakit')
                  ->onDelete('cascade');
                  
            $table->string('nama', 255);
            $table->string('gambar', 255);
            $table->text('deskripsi');
            $table->boolean('aktif')->default(true);
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layanan_unggulan');
    }
};