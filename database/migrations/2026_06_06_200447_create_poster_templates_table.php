<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poster_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rumah_sakit_id')
                  ->constrained('rumah_sakit')
                  ->cascadeOnDelete();
            $table->string('nama');
            $table->string('template_png');          // path PNG background
            $table->string('logo_header')->nullable();
            $table->string('shape_poli')->nullable(); // path PNG transparan
            $table->json('config')->nullable();       // zona + grid styling
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poster_templates');
    }
};