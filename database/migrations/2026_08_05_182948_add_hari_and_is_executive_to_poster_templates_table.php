<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poster_templates', function (Blueprint $table) {
            $table->string('hari', 10)->nullable()->after('jenis');
            $table->boolean('is_executive')->default(false)->after('hari');
            $table->dropColumn('is_default');
        });
    }

    public function down(): void
    {
        Schema::table('poster_templates', function (Blueprint $table) {
            $table->dropColumn(['hari', 'is_executive']);
            $table->boolean('is_default')->default(false);
        });
    }
};
