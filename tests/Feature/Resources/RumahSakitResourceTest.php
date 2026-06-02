<?php

namespace Tests\Feature\Resources;

use App\Models\RumahSakit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RumahSakitResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_unauthenticated_redirects(): void
    {
        $this->get('/admin/rumah-sakits')->assertRedirect('/admin/login');
    }

    public function test_super_admin_can_list(): void
    {
        $this->actingAs($this->superAdmin())
            ->get('/admin/rumah-sakits')
            ->assertOk();
    }

    public function test_admin_can_list(): void
    {
        $rs = RumahSakit::factory()->create();
        $this->actingAs($this->adminUser($rs->id))
            ->get('/admin/rumah-sakits')
            ->assertOk();
    }

    public function test_super_admin_can_access_create_page(): void
    {
        $this->actingAs($this->superAdmin())
            ->get('/admin/rumah-sakits/create')
            ->assertOk();
    }

    public function test_super_admin_can_create_rumah_sakit(): void
    {
        $rs = RumahSakit::factory()->create([
            'nama'   => 'RS Baru',
            'slug'   => 'rs-baru',
            'lokasi' => 'Jakarta',
            'alamat' => 'Jl. Test No. 1',
            'aktif'  => true,
        ]);

        $this->assertDatabaseHas('rumah_sakit', [
            'nama' => 'RS Baru',
            'slug' => 'rs-baru',
        ]);
    }

    public function test_super_admin_can_delete_rumah_sakit(): void
    {
        $rs = RumahSakit::factory()->create();
        $rs->delete();

        $this->assertDatabaseMissing('rumah_sakit', ['id' => $rs->id]);
    }

    public function test_super_admin_sees_all_rumah_sakit(): void
    {
        RumahSakit::factory()->count(5)->create();
        $this->assertEquals(5, RumahSakit::count());
    }

    public function test_admin_only_sees_own_rumah_sakit(): void
    {
        $rs1 = RumahSakit::factory()->create();
        RumahSakit::factory()->count(3)->create();

        $count = RumahSakit::where('id', $rs1->id)->count();
        $this->assertEquals(1, $count);
    }
}
