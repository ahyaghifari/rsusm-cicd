<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('poliklinik', 'sort_order')) {
            Schema::table('poliklinik', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(0)->after('aktif');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('poliklinik', 'sort_order')) {
            Schema::table('poliklinik', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
