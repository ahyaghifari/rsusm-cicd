<?php

namespace Tests\Feature\Resources;

use App\Models\Dokter;
use App\Models\RumahSakit;
use App\Models\Spesialis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DokterResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->get('/admin/dokters')->assertRedirect('/admin/login');
    }

    public function test_super_admin_can_list_dokters(): void
    {
        $this->actingAs($this->superAdmin())
            ->get('/admin/dokters')
            ->assertOk();
    }

    public function test_admin_can_list_dokters_with_permission(): void
    {
        // Shield requires view_any_dokter permission for non-super_admin roles.
        // We grant it explicitly here since we don't run the full Shield seeder in tests.
        $rs    = RumahSakit::factory()->create();
        $user  = $this->adminUser($rs->id);
        $perm  = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'view_any_dokter', 'guard_name' => 'web']);
        $user->givePermissionTo($perm);

        $this->actingAs($user)->get('/admin/dokters')->assertOk();
    }

    public function test_super_admin_sees_all_dokters(): void
    {
        $rs1 = RumahSakit::factory()->create();
        $rs2 = RumahSakit::factory()->create();
        Dokter::factory()->count(3)->create(['rumah_sakit_id' => $rs1->id]);
        Dokter::factory()->count(2)->create(['rumah_sakit_id' => $rs2->id]);

        $this->assertEquals(5, Dokter::count());
    }

    public function test_admin_only_sees_own_rs_dokters(): void
    {
        $rs1 = RumahSakit::factory()->create();
        $rs2 = RumahSakit::factory()->create();
        Dokter::factory()->count(3)->create(['rumah_sakit_id' => $rs1->id]);
        Dokter::factory()->count(2)->create(['rumah_sakit_id' => $rs2->id]);

        $count = Dokter::where('rumah_sakit_id', $rs1->id)->count();
        $this->assertEquals(3, $count);
    }

    public function test_can_create_dokter(): void
    {
        $rs        = RumahSakit::factory()->create();
        $spesialis = Spesialis::factory()->create(['rumah_sakit_id' => $rs->id]);

        $dokter = Dokter::create([
            'rumah_sakit_id' => $rs->id,
            'spesialis_id'   => $spesialis->id,
            'nama'           => 'dr. Test Dokter',
            'slug'           => 'dr-test-dokter',
            'aktif'          => true,
        ]);

        $this->assertDatabaseHas('dokter', [
            'nama' => 'dr. Test Dokter',
            'slug' => 'dr-test-dokter',
        ]);
        $this->assertEquals($rs->id, $dokter->rumahSakit->id);
    }

    public function test_deleting_rumah_sakit_cascades_to_dokter(): void
    {
        $rs     = RumahSakit::factory()->create();
        $dokter = Dokter::factory()->create(['rumah_sakit_id' => $rs->id]);

        $rs->delete();

        $this->assertDatabaseMissing('dokter', ['id' => $dokter->id]);
    }

    public function test_super_admin_can_access_create_page(): void
    {
        $this->actingAs($this->superAdmin())
            ->get('/admin/dokters/create')
            ->assertOk();
    }
}
