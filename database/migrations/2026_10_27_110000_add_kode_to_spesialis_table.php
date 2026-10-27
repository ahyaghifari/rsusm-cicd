<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /** Kode acak unik per spesialis (lihat trait HasKode); spesialis lama di-backfill. */
    public function up(): void
    {
        if (! Schema::hasColumn('spesialis', 'kode')) {
            Schema::table('spesialis', function (Blueprint $table) {
                $table->string('kode', 8)->nullable()->unique()->after('id');
            });
        }

        foreach (DB::table('spesialis')->whereNull('kode')->pluck('id') as $id) {
            do {
                $kode = strtoupper(Str::random(8));
            } while (DB::table('spesialis')->where('kode', $kode)->exists());

            DB::table('spesialis')->where('id', $id)->update(['kode' => $kode]);
        }
    }

    public function down(): void
    {
        Schema::table('spesialis', function (Blueprint $table) {
            $table->dropUnique(['kode']);
            $table->dropColumn('kode');
        });
    }
};
