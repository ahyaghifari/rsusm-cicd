<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = 'poster_generate';

        if (! Schema::hasTable($table)) {
            return;
        }

        if (! Schema::hasColumn($table, 'kategori_klinik')) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('kategori_klinik', 20)
                    ->default('REGULER')
                    ->after('jenis');
            });
        }

        $indexes = collect(Schema::getIndexes($table))->pluck('name');

        if ($indexes->contains('poster_generate_unique_poster')) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropUnique('poster_generate_unique_poster');
            });
        }

        if (! $indexes->contains('poster_generate_unique_poster_with_category')) {
            Schema::table($table, function (Blueprint $table) {
                $table->unique(
                    ['rumah_sakit_id', 'jenis', 'kategori_klinik', 'tanggal', 'halaman'],
                    'poster_generate_unique_poster_with_category'
                );
            });
        }
    }

    public function down(): void
    {
        $table = 'poster_generate';

        if (! Schema::hasTable($table)) {
            return;
        }

        $indexes = collect(Schema::getIndexes($table))->pluck('name');

        if ($indexes->contains('poster_generate_unique_poster_with_category')) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropUnique('poster_generate_unique_poster_with_category');
            });
        }

        if (Schema::hasColumn($table, 'kategori_klinik')) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('kategori_klinik');
            });
        }
    }
};
