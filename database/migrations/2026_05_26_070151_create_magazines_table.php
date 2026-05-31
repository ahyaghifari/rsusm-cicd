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
        Schema::create('magazines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rumah_sakit_id')->constrained('rumah_sakit')->cascadeOnDelete();
            $table->string('judul', 255);
            $table->string('slug', 100);
            $table->string('cover')->nullable();
            $table->string('file_pdf');
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->date('published_at')->nullable();
            $table->timestamps();

            $table->unique(['rumah_sakit_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('magazines');
    }
};
