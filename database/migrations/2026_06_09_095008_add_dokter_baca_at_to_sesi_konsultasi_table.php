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
            $table->timestamp('dokter_baca_at')->nullable()->after('push_subscription');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_konsultasi', function (Blueprint $table) {
            $table->dropColumn('dokter_baca_at');
        });
    }
};
