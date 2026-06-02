<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Tambah kolom kata_kunci ────────────────────────────────────
        Schema::table('faq', function (Blueprint $table) {
            $table->string('kata_kunci', 500)->nullable()->after('deskripsi')
                ->comment('Kata kunci pencarian, dipisah koma.');
        });

        Schema::table('halaman', function (Blueprint $table) {
            $table->string('kata_kunci', 500)->nullable()->after('konten')
                ->comment('Kata kunci pencarian, dipisah koma.');
        });

        // ── Perbarui FULLTEXT index (hanya MySQL) ─────────────────────
        if (DB::getDriverName() !== 'mysql') return;

        // Drop index lama (hanya judul/deskripsi)
        DB::statement('ALTER TABLE faq     DROP INDEX ft_search');
        DB::statement('ALTER TABLE halaman DROP INDEX ft_search');

        // Recreate dengan kata_kunci disertakan
        DB::statement('ALTER TABLE faq     ADD FULLTEXT INDEX ft_search (judul, deskripsi, kata_kunci)');
        DB::statement('ALTER TABLE halaman ADD FULLTEXT INDEX ft_search (judul, kata_kunci)');
    }

    public function down(): void
    {
        // ── Kembalikan FULLTEXT index ─────────────────────────────────
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE faq     DROP INDEX ft_search');
            DB::statement('ALTER TABLE halaman DROP INDEX ft_search');

            DB::statement('ALTER TABLE faq     ADD FULLTEXT INDEX ft_search (judul, deskripsi)');
            DB::statement('ALTER TABLE halaman ADD FULLTEXT INDEX ft_search (judul)');
        }

        // ── Hapus kolom ───────────────────────────────────────────────
        Schema::table('faq', function (Blueprint $table) {
            $table->dropColumn('kata_kunci');
        });

        Schema::table('halaman', function (Blueprint $table) {
            $table->dropColumn('kata_kunci');
        });
    }
};
