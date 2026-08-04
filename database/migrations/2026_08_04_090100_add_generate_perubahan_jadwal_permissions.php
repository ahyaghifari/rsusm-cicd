<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Halaman baru "Generate Perubahan Jadwal" mengikuti akses yang sama dengan
     * "Generate Poster" (page_GeneratePosterPage) — lihat migrasi
     * 2026_07_08_120000_update_jadwal_harian_and_generate_poster_permissions.php.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $generatePerubahan = Permission::firstOrCreate([
            'name' => 'page_GeneratePerubahanJadwalPage',
            'guard_name' => 'web',
        ]);

        $humas = Role::where('name', 'humas')->first();
        $humas?->givePermissionTo($generatePerubahan);

        foreach (['admin', 'super_admin'] as $roleName) {
            Role::where('name', $roleName)->first()?->givePermissionTo($generatePerubahan);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // no-op — perubahan permission/role tidak di-revert otomatis
    }
};
