<?php

namespace Tests\Feature;

use App\Filament\Resources\PromoResource\Pages\ManagePromo;
use App\Models\RumahSakit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PromoDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_create_promo(): void
    {
        $this->createRoles();
        foreach (['view_any_promo', 'view_promo', 'create_promo', 'update_promo'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $rs = RumahSakit::factory()->create(['aktif' => true]);
        $admin = $this->adminUser($rs->id);
        $admin->givePermissionTo(['view_any_promo', 'view_promo', 'create_promo', 'update_promo']);

        Livewire::actingAs($admin)
            ->test(ManagePromo::class)
            ->assertOk()
            ->callAction('create', data: [
                'judul' => 'Promo Test',
                'slug' => 'promo-test',
                'aktif' => true,
                'popup' => false,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('promo', [
            'judul' => 'Promo Test',
            'rumah_sakit_id' => $rs->id,
        ]);
    }

    public function test_humas_create_promo(): void
    {
        $this->createRoles();
        foreach (['view_any_promo', 'view_promo', 'create_promo', 'update_promo'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $rs = RumahSakit::factory()->create(['aktif' => true]);
        $humas = $this->humasUser($rs->id);
        $humas->givePermissionTo(['view_any_promo', 'view_promo', 'create_promo', 'update_promo']);

        Livewire::actingAs($humas)
            ->test(ManagePromo::class)
            ->assertOk()
            ->callAction('create', data: [
                'judul' => 'Promo Test Humas',
                'slug' => 'promo-test-humas',
                'aktif' => true,
                'popup' => false,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('promo', [
            'judul' => 'Promo Test Humas',
            'rumah_sakit_id' => $rs->id,
        ]);
    }

    public function test_informasi_create_promo(): void
    {
        $this->createRoles();
        foreach (['view_any_promo', 'view_promo', 'create_promo', 'update_promo'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $rs = RumahSakit::factory()->create(['aktif' => true]);
        $informasi = $this->informasiUser($rs->id);
        $informasi->givePermissionTo(['view_any_promo', 'view_promo', 'create_promo', 'update_promo']);

        Livewire::actingAs($informasi)
            ->test(ManagePromo::class)
            ->assertOk()
            ->callAction('create', data: [
                'judul' => 'Promo Test Informasi',
                'slug' => 'promo-test-informasi',
                'aktif' => true,
                'popup' => false,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('promo', [
            'judul' => 'Promo Test Informasi',
            'rumah_sakit_id' => $rs->id,
        ]);
    }

    public function test_superadmin_create_promo(): void
    {
        $this->createRoles();
        $rs = RumahSakit::factory()->create(['aktif' => true]);
        $sa = $this->superAdmin();

        Livewire::actingAs($sa)
            ->test(ManagePromo::class)
            ->callAction('create', data: [
                'rumah_sakit_id' => $rs->id,
                'judul' => 'Promo Test SA',
                'slug' => 'promo-test-sa',
                'aktif' => true,
                'popup' => false,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('promo', [
            'judul' => 'Promo Test SA',
            'rumah_sakit_id' => $rs->id,
        ]);
    }
}
