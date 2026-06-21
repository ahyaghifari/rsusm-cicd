<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artikel', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        if (DB::getDriverName() !== 'mysql') return;

        DB::statement('ALTER TABLE artikel ADD FULLTEXT INDEX ft_search (judul, ringkasan, konten)');
    }

    public function down(): void
    {
        Schema::table('artikel',     fn (Blueprint $t) => $t->dropSoftDeletes());

        if (DB::getDriverName() !== 'mysql') return;
        DB::statement('ALTER TABLE artikel     DROP INDEX ft_search');
    }
};
