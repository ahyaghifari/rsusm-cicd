<?php

namespace Tests\Feature\Resources;

use App\Models\RumahSakit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_unauthenticated_redirects(): void
    {
        $this->get($this->adminUrl('users'))->assertRedirect($this->adminUrl('login'));
    }

    public function test_super_admin_can_list_users(): void
    {
        $this->actingAs($this->superAdmin())
            ->get($this->adminUrl('users'))
            ->assertOk();
    }

    public function test_user_can_be_assigned_role(): void
    {
        $rs   = RumahSakit::factory()->create();
        $user = User::factory()->create(['rumah_sakit_id' => $rs->id]);
        $user->assignRole('admin');

        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('super_admin'));
    }

    public function test_user_can_sync_roles(): void
    {
        $rs   = RumahSakit::factory()->create();
        $user = User::factory()->create(['rumah_sakit_id' => $rs->id]);

        $user->assignRole('admin');
        $this->assertTrue($user->hasRole('admin'));

        $user->syncRoles(['humas']);
        $this->assertFalse($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('humas'));
    }

    public function test_super_admin_user_is_super_admin(): void
    {
        $user = $this->superAdmin();
        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->hasRole('super_admin'));
    }

    public function test_admin_user_has_rumah_sakit(): void
    {
        $rs   = RumahSakit::factory()->create();
        $user = $this->adminUser($rs->id);

        $this->assertNotNull($user->rumah_sakit_id);
        $this->assertEquals($rs->id, $user->rumah_sakit_id);
        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_super_admin_can_access_create_user_page(): void
    {
        $this->actingAs($this->superAdmin())
            ->get($this->adminUrl('users/create'))
            ->assertOk();
    }

    public function test_password_is_hashed(): void
    {
        $user = User::factory()->create();
        $this->assertNotEquals('password', $user->password);
    }
}
