<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Humas: Jadwal Harian jadi lihat-saja + akses Generate Poster.
     * Informasi: Jadwal Harian penuh (create/update/delete), tanpa Generate Poster.
     * Admin & Super Admin: pastikan tetap punya akses Generate Poster (halaman baru, belum pernah di-generate shield).
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $generatePoster = Permission::firstOrCreate([
            'name' => 'page_GeneratePosterPage',
            'guard_name' => 'web',
        ]);

        $humas = Role::where('name', 'humas')->first();
        if ($humas) {
            $humas->givePermissionTo(
                Permission::whereIn('name', [
                    'view_any_jadwal::harian',
                    'view_jadwal::harian',
                    'page_GeneratePosterPage',
                ])->get()
            );
        }

        $informasi = Role::where('name', 'informasi')->first();
        if ($informasi) {
            $informasi->givePermissionTo(
                Permission::where('name', 'like', '%jadwal::harian%')->get()
            );
        }

        foreach (['admin', 'super_admin'] as $roleName) {
            Role::where('name', $roleName)->first()?->givePermissionTo($generatePoster);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // no-op — perubahan permission/role tidak di-revert otomatis
    }
};
