<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Mirror Shield's Gate::before() so super_admin bypasses all resource
        // authorization checks even without the Filament panel booting plugins.
        Gate::before(function ($user, string $ability) {
            if ($user instanceof \App\Models\User && $user->isSuperAdmin()) {
                return true;
            }
            return null;
        });
    }

    protected function createRoles(): void
    {
        foreach (['super_admin', 'admin', 'humas', 'informasi'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    protected function superAdmin(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('super_admin');
        return $user;
    }

    protected function adminUser(int $rsId): \App\Models\User
    {
        $user = \App\Models\User::factory()->create(['rumah_sakit_id' => $rsId]);
        $user->assignRole('admin');
        return $user;
    }

    protected function humasUser(int $rsId): \App\Models\User
    {
        $user = \App\Models\User::factory()->create(['rumah_sakit_id' => $rsId]);
        $user->assignRole('humas');
        return $user;
    }

    protected function informasiUser(int $rsId): \App\Models\User
    {
        $user = \App\Models\User::factory()->create(['rumah_sakit_id' => $rsId]);
        $user->assignRole('informasi');
        return $user;
    }
}
