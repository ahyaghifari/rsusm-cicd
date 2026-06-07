<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_harian_perubahan', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_harian_perubahan', 'jenis')) {
                $table->dropColumn('jenis');
            }
        });
    }

    public function down(): void
    {
        
    }
};
