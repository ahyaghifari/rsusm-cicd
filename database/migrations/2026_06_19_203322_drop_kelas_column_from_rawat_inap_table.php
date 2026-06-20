<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rawat_inap', function (Blueprint $table) {
            $table->dropColumn('kelas');
        });
    }

    public function down(): void
    {
        Schema::table('rawat_inap', function (Blueprint $table) {
            $table->string('kelas', 255)->nullable()->after('nama');
        });
    }
};
