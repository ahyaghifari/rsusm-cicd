<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rawat_inap', function (Blueprint $table) {
            $table->foreignId('kelas_rawat_inap_id')->nullable()->after('kelas')->constrained('kelas_rawat_inap')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rawat_inap', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kelas_rawat_inap_id');
        });
    }
};
