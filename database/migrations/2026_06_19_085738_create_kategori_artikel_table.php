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
        Schema::create('kategori_artikel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rumah_sakit_id')->constrained('rumah_sakit')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->string('slug', 100);
            $table->timestamps();

            $table->unique(['slug', 'rumah_sakit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_artikel');
    }
};
