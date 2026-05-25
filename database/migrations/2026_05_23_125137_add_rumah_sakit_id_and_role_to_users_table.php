<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->foreignId('rumah_sakit_id')
                ->nullable()
                ->after('id')
                ->constrained('rumah_sakit')
                ->nullOnDelete();

            $table->string('role')
                ->default('admin')
                ->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropForeign(['rumah_sakit_id']);

            $table->dropColumn([
                'rumah_sakit_id',
                'role',
            ]);
        });
    }
};