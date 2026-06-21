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
            $table->text('push_subscription')->nullable()->after('kesimpulan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_konsultasi', function (Blueprint $table) {
            $table->dropColumn('push_subscription');
        });
    }
};
