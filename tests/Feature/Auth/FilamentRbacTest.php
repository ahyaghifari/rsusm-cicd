<?php

namespace Tests\Feature\Auth;

use App\Models\RumahSakit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentRbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/dokters')->assertRedirect('/admin/login');
        $this->get('/admin/users')->assertRedirect('/admin/login');
    }

    public function test_user_without_role_cannot_access_panel(): void
    {
        // Filament returns 403 (not redirect) for authenticated users without panel access
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_super_admin_can_access_panel(): void
    {
        $this->actingAs($this->superAdmin())
            ->get('/admin')
            ->assertOk();
    }

    public function test_admin_can_access_panel(): void
    {
        $rs = RumahSakit::factory()->create();
        $this->actingAs($this->adminUser($rs->id))
            ->get('/admin')
            ->assertOk();
    }

    public function test_humas_can_access_panel(): void
    {
        $rs = RumahSakit::factory()->create();
        $this->actingAs($this->humasUser($rs->id))
            ->get('/admin')
            ->assertOk();
    }

    public function test_informasi_can_access_panel(): void
    {
        $rs = RumahSakit::factory()->create();
        $this->actingAs($this->informasiUser($rs->id))
            ->get('/admin')
            ->assertOk();
    }

    public function test_super_admin_can_access_rumah_sakit_resource(): void
    {
        $this->actingAs($this->superAdmin())
            ->get('/admin/rumah-sakits')
            ->assertOk();
    }

    public function test_super_admin_can_access_user_resource(): void
    {
        $this->actingAs($this->superAdmin())
            ->get('/admin/users')
            ->assertOk();
    }
}
