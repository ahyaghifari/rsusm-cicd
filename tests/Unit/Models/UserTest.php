<?php

namespace Tests\Unit\Models;

use App\Models\RumahSakit;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_rumah_sakit_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new User)->rumahSakit());
    }

    public function test_is_super_admin_returns_true_for_super_admin_role(): void
    {
        $this->createRoles();
        $user = $this->superAdmin();
        $this->assertTrue($user->isSuperAdmin());
    }

    public function test_is_super_admin_returns_false_for_other_roles(): void
    {
        $this->createRoles();
        $rs   = RumahSakit::factory()->create();
        $user = $this->adminUser($rs->id);
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_can_access_panel_for_valid_roles(): void
    {
        $this->createRoles();
        $rs = RumahSakit::factory()->create();
        $adminPanel = Filament::getPanel('admin');

        foreach (['super_admin', 'admin', 'humas', 'informasi'] as $role) {
            $user = User::factory()->create(['rumah_sakit_id' => $rs->id]);
            $user->assignRole($role);
            $this->assertTrue(
                $user->canAccessPanel($adminPanel),
                "Role $role seharusnya bisa akses panel"
            );
        }
    }

    public function test_cannot_access_panel_without_role(): void
    {
        $this->createRoles();
        $user = User::factory()->create();
        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_dokter_role_can_access_dokter_panel_but_not_admin_panel(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'dokter', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole('dokter');

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('dokter')));
        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_belongs_to_rumah_sakit(): void
    {
        $this->createRoles();
        $rs   = RumahSakit::factory()->create();
        $user = User::factory()->create(['rumah_sakit_id' => $rs->id]);

        $this->assertEquals($rs->id, $user->rumahSakit->id);
    }

    public function test_rumah_sakit_nullable(): void
    {
        $user = User::factory()->create(['rumah_sakit_id' => null]);
        $this->assertNull($user->rumahSakit);
    }
}
