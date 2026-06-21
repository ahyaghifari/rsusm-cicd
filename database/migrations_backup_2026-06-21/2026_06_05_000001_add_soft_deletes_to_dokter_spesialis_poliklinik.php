<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokter', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('spesialis', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('poliklinik', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('dokter',     fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('spesialis',  fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('poliklinik', fn (Blueprint $t) => $t->dropSoftDeletes());
    }
};
