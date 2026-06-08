<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokter', function (Blueprint $table) {
            $table->dropForeign(['spesialis_id']);
        });

        Schema::table('dokter', function (Blueprint $table) {
            $table->unsignedBigInteger('spesialis_id')->nullable()->change();
        });

        Schema::table('dokter', function (Blueprint $table) {
            $table->foreign('spesialis_id')->references('id')->on('spesialis')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dokter', function (Blueprint $table) {
            $table->dropForeign(['spesialis_id']);
        });

        Schema::table('dokter', function (Blueprint $table) {
            $table->unsignedBigInteger('spesialis_id')->nullable(false)->change();
        });

        Schema::table('dokter', function (Blueprint $table) {
            $table->foreign('spesialis_id')->references('id')->on('spesialis')->cascadeOnDelete();
        });
    }
};
