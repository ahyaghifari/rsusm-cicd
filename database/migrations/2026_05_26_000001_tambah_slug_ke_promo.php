<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo', function (Blueprint $table) {
            $table->string('slug', 255)->nullable()->after('judul');
        });

        // Populate existing rows
        DB::table('promo')->orderBy('id')->each(function ($promo) {
            $base = Str::slug($promo->judul) ?: 'promo';
            $slug = $base . '-' . $promo->id;
            DB::table('promo')->where('id', $promo->id)->update(['slug' => $slug]);
        });

        Schema::table('promo', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('promo', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
