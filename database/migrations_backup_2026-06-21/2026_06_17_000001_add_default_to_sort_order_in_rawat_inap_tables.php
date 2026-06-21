<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rawat_inap', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->change();
        });

        Schema::table('gambar_rawat_inap', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('rawat_inap', function (Blueprint $table) {
            $table->integer('sort_order')->change();
        });

        Schema::table('gambar_rawat_inap', function (Blueprint $table) {
            $table->integer('sort_order')->change();
        });
    }
};
