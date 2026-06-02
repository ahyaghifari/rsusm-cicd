<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE dokter     ADD FULLTEXT INDEX ft_search (nama)');
        DB::statement('ALTER TABLE poliklinik ADD FULLTEXT INDEX ft_search (nama, deskripsi)');
        DB::statement('ALTER TABLE promo      ADD FULLTEXT INDEX ft_search (judul, deskripsi)');
        DB::statement('ALTER TABLE faq        ADD FULLTEXT INDEX ft_search (judul, deskripsi)');
        DB::statement('ALTER TABLE halaman    ADD FULLTEXT INDEX ft_search (judul)');
        DB::statement('ALTER TABLE spesialis  ADD FULLTEXT INDEX ft_search (nama)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE dokter     DROP INDEX ft_search');
        DB::statement('ALTER TABLE poliklinik DROP INDEX ft_search');
        DB::statement('ALTER TABLE promo      DROP INDEX ft_search');
        DB::statement('ALTER TABLE faq        DROP INDEX ft_search');
        DB::statement('ALTER TABLE halaman    DROP INDEX ft_search');
        DB::statement('ALTER TABLE spesialis  DROP INDEX ft_search');
    }
};
