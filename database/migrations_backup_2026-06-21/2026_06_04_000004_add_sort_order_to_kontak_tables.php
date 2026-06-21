<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kontak', function (Blueprint $t) {
            $t->unsignedSmallInteger('sort_order')->default(0)->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('kontak', function (Blueprint $t) {
            $t->dropColumn('sort_order');
        });
    }
};
