<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin      = Role::firstOrCreate(['name' => 'admin',       'guard_name' => 'web']);
        $humas      = Role::firstOrCreate(['name' => 'humas',       'guard_name' => 'web']);
        $informasi  = Role::firstOrCreate(['name' => 'informasi',   'guard_name' => 'web']);

        $allPermissions = Permission::all();

        // Admin: all resources except rumah_sakit, gedung, unit_layanan, and shield role management
        $adminExcluded = ['rumah::sakit', 'gedung', 'unit::layanan', 'role'];
        $adminPermissions = $allPermissions->filter(function (Permission $p) use ($adminExcluded) {
            foreach ($adminExcluded as $slug) {
                if (str_contains($p->name, $slug)) {
                    return false;
                }
            }
            return true;
        });

        // Humas: media & schedule focused
        $humasResources = [
            'poli::klinik', 'jadwal::layanan', 'banner', 'promo', 'halaman',
            'magazine', 'faq', 'layanan::unggulan', 'fasilitas::pendukung',
            'penunjang::medis', 'kontak',
        ];
        $humasPermissions = $allPermissions->filter(
            fn (Permission $p) => $this->matchesAny($p->name, $humasResources)
        );

        // Informasi: clinical information focused
        $informasiResources = [
            'jadwal::praktek', 'dokter', 'spesialis', 'rawat::inap', 'partner',
            'link::layanan', 'faq', 'layanan::unggulan', 'fasilitas::pendukung',
            'penunjang::medis', 'kontak',
        ];
        $informasiPermissions = $allPermissions->filter(
            fn (Permission $p) => $this->matchesAny($p->name, $informasiResources)
        );

        $admin->syncPermissions($adminPermissions);
        $humas->syncPermissions($humasPermissions);
        $informasi->syncPermissions($informasiPermissions);

        // Migrate existing users from legacy role column
        User::whereNotNull('role')->get()->each(function (User $user) use ($superAdmin, $admin) {
            if (! $user->hasAnyRole(['super_admin', 'admin', 'humas', 'informasi'])) {
                match ($user->role) {
                    'superadmin' => $user->assignRole($superAdmin),
                    'admin'      => $user->assignRole($admin),
                    default      => null,
                };
            }
        });
    }

    private function matchesAny(string $permissionName, array $resources): bool
    {
        foreach ($resources as $resource) {
            if (str_contains($permissionName, $resource)) {
                return true;
            }
        }
        return false;
    }
}
