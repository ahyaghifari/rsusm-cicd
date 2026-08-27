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

        $keepIds = DB::table($table)
            ->selectRaw('MAX(id) as id')
            ->groupBy('rumah_sakit_id', 'jenis', 'tanggal', 'halaman')
            ->pluck('id');

        DB::table($table)->whereNotIn('id', $keepIds)->delete();

        Schema::table($table, function (Blueprint $table) {
            $table->string('kategori_klinik', 20)->default('REGULER')->after('jenis');
            $table->unique(
                ['rumah_sakit_id', 'jenis', 'kategori_klinik', 'tanggal', 'halaman'],
                'poster_generate_unique_poster'
            );
        });
    }

    public function down(): void
    {
        Schema::table('poster_generate', function (Blueprint $table) {
            $table->dropUnique('poster_generate_unique_poster');
        });
    }
};