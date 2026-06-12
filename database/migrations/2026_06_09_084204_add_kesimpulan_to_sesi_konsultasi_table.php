<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sesi_konsultasi', function (Blueprint $table) {
            $table->text('kesimpulan')->nullable()->after('berakhir_at');
        });
    }

    public function down(): void
    {
        Schema::table('sesi_konsultasi', function (Blueprint $table) {
            $table->dropColumn('kesimpulan');
        });
    }
};
