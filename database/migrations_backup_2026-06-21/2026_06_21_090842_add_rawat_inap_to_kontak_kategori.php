<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite tidak enforce enum, hanya MySQL yang perlu alter
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE kontak MODIFY COLUMN kategori ENUM('SOSIAL MEDIA', 'OPERASIONAL', 'PENDAFTARAN', 'RAWAT INAP') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE kontak MODIFY COLUMN kategori ENUM('SOSIAL MEDIA', 'OPERASIONAL', 'PENDAFTARAN') NOT NULL");
        }
    }
};
