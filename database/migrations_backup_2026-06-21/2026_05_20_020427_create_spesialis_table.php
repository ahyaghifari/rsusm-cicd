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
        Schema::create('spesialis', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('slug', 100);
            $table->string('logo', 255)->nullable();
            $table->boolean('aktif')->default(true);
            $table->foreignId('rumah_sakit_id')->default(1)->constrained('rumah_sakit')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['nama', 'rumah_sakit_id']);
            $table->unique(['slug', 'rumah_sakit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spesialis');
    }
};
