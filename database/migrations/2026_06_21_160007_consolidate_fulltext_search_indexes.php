<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konsolidasi dari 3 migrasi lama yang sama-sama bagian dari evolusi fitur
     * pencarian fulltext: add_fulltext_indexes_for_search,
     * add_kata_kunci_and_update_fulltext_faq_halaman, add_fulltext_indexes_for_artikel.
     * Lihat issues/migration-cleanup-plan.md.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('faq', 'kata_kunci')) {
            Schema::table('faq', function (Blueprint $table) {
                $table->string('kata_kunci', 500)->nullable()->after('deskripsi')
                    ->comment('Kata kunci pencarian, dipisah koma.');
            });
        }

        if (! Schema::hasColumn('halaman', 'kata_kunci')) {
            Schema::table('halaman', function (Blueprint $table) {
                $table->string('kata_kunci', 500)->nullable()->after('konten')
                    ->comment('Kata kunci pencarian, dipisah koma.');
            });
        }

        if (! Schema::hasColumn('artikel', 'deleted_at')) {
            Schema::table('artikel', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
            });
        }

        if (DB::getDriverName() !== 'mysql') return;

        // Hanya drop+recreate index kalau kolomnya beda dari yang seharusnya — supaya
        // di production yang index-nya sudah benar, ini benar-benar no-op (rebuild
        // fulltext index di tabel besar tidak murah, jangan dilakukan tanpa perlu).
        $indexes = [
            'dokter'     => ['nama'],
            'poliklinik' => ['nama', 'deskripsi'],
            'promo'      => ['judul', 'deskripsi'],
            'spesialis'  => ['nama'],
            'faq'        => ['judul', 'deskripsi', 'kata_kunci'],
            'halaman'    => ['judul', 'konten', 'kata_kunci'],
            'artikel'    => ['judul', 'ringkasan', 'konten'],
        ];

        foreach ($indexes as $table => $columns) {
            $existing = collect(Schema::getIndexes($table))->firstWhere('name', 'ft_search');

            if ($existing && $existing['columns'] === $columns) {
                continue;
            }

            if ($existing) {
                DB::statement("ALTER TABLE {$table} DROP INDEX ft_search");
            }

            DB::statement("ALTER TABLE {$table} ADD FULLTEXT INDEX ft_search (" . implode(', ', $columns) . ')');
        }
    }

    public function down(): void
    {
        // Lihat catatan di migrasi konsolidasi dokter — rollback simetris tidak aman
        // untuk migrasi idempotent seperti ini.
    }
};
