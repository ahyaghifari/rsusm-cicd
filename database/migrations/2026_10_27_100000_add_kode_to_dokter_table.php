<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Kode acak unik per dokter (dibuat otomatis oleh Dokter::creating),
     * dipakai sebagai identitas stabil di API publik. Dokter lama di-backfill.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('dokter', 'kode')) {
            Schema::table('dokter', function (Blueprint $table) {
                $table->string('kode', 8)->nullable()->unique()->after('id');
            });
        }

        foreach (DB::table('dokter')->whereNull('kode')->pluck('id') as $id) {
            do {
                $kode = strtoupper(Str::random(8));
            } while (DB::table('dokter')->where('kode', $kode)->exists());

            DB::table('dokter')->where('id', $id)->update(['kode' => $kode]);
        }
    }

    public function down(): void
    {
        Schema::table('dokter', function (Blueprint $table) {
            $table->dropUnique(['kode']);
            $table->dropColumn('kode');
        });
    }
};
