<?php

namespace Tests\Feature\Resources;

use App\Models\Dokter;
use App\Models\PoliKlinik;
use App\Models\RumahSakit;
use App\Models\Spesialis;
use App\Models\UnitLayanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    private RumahSakit $rs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
        $this->rs = RumahSakit::factory()->create(['aktif' => true]);
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function makeDokter(array $attrs = []): Dokter
    {
        $sp = Spesialis::factory()->create(['rumah_sakit_id' => $this->rs->id]);
        return Dokter::factory()->create(array_merge([
            'rumah_sakit_id' => $this->rs->id,
            'spesialis_id'   => $sp->id,
            'aktif'          => true,
        ], $attrs));
    }

    private function makeSpesialis(array $attrs = []): Spesialis
    {
        return Spesialis::factory()->create(array_merge([
            'rumah_sakit_id' => $this->rs->id,
            'aktif'          => true,
        ], $attrs));
    }

    private function makePoliKlinik(array $attrs = []): PoliKlinik
    {
        $unit = UnitLayanan::factory()->create(['rumah_sakit_id' => $this->rs->id]);
        return PoliKlinik::factory()->create(array_merge([
            'unit_layanan_id' => $unit->id,
            'aktif'           => true,
        ], $attrs));
    }

    // ── Dokter SoftDelete ─────────────────────────────────────────────────────

    public function test_dokter_soft_delete_tidak_langsung_hapus_dari_db(): void
    {
        $dokter = $this->makeDokter();
        $dokter->delete();

        $this->assertSoftDeleted('dokter', ['id' => $dokter->id]);
        $this->assertDatabaseHas('dokter', ['id' => $dokter->id]);
    }

    public function test_dokter_soft_deleted_tidak_muncul_di_query_biasa(): void
    {
        $dokter = $this->makeDokter(['nama' => 'dr. Tersembunyi']);
        $dokter->delete();

        $this->assertNull(Dokter::find($dokter->id));
        $this->assertCount(0, Dokter::where('nama', 'dr. Tersembunyi')->get());
    }

    public function test_dokter_dapat_di_restore(): void
    {
        $dokter = $this->makeDokter();
        $dokter->delete();

        $this->assertSoftDeleted('dokter', ['id' => $dokter->id]);

        $dokter->restore();

        $this->assertNotSoftDeleted('dokter', ['id' => $dokter->id]);
        $this->assertNotNull(Dokter::find($dokter->id));
    }

    public function test_dokter_dapat_di_force_delete(): void
    {
        $dokter = $this->makeDokter();
        $dokter->delete();
        $dokter->forceDelete();

        $this->assertDatabaseMissing('dokter', ['id' => $dokter->id]);
    }

    public function test_dokter_with_trashed_mengembalikan_soft_deleted(): void
    {
        $dokter = $this->makeDokter();
        $dokter->delete();

        $this->assertCount(1, Dokter::withTrashed()->where('id', $dokter->id)->get());
    }

    public function test_dokter_soft_deleted_punya_deleted_at(): void
    {
        $dokter = $this->makeDokter();
        $dokter->delete();

        $deleted = Dokter::withTrashed()->find($dokter->id);
        $this->assertNotNull($deleted->deleted_at);
    }

    // ── Spesialis SoftDelete ──────────────────────────────────────────────────

    public function test_spesialis_soft_delete_tidak_langsung_hapus(): void
    {
        $sp = $this->makeSpesialis();
        $sp->delete();

        $this->assertSoftDeleted('spesialis', ['id' => $sp->id]);
        $this->assertDatabaseHas('spesialis', ['id' => $sp->id]);
    }

    public function test_spesialis_soft_deleted_tidak_muncul_di_query_biasa(): void
    {
        $sp = $this->makeSpesialis(['nama' => 'Sp. Tersembunyi']);
        $sp->delete();

        $this->assertNull(Spesialis::find($sp->id));
    }

    public function test_spesialis_dapat_di_restore(): void
    {
        $sp = $this->makeSpesialis();
        $sp->delete();

        $sp->restore();

        $this->assertNotSoftDeleted('spesialis', ['id' => $sp->id]);
        $this->assertNotNull(Spesialis::find($sp->id));
    }

    public function test_spesialis_dapat_di_force_delete(): void
    {
        $sp = $this->makeSpesialis();
        $sp->delete();
        $sp->forceDelete();

        $this->assertDatabaseMissing('spesialis', ['id' => $sp->id]);
    }

    public function test_spesialis_only_trashed_hanya_mengembalikan_deleted(): void
    {
        $aktif   = $this->makeSpesialis(['nama' => 'Aktif']);
        $deleted = $this->makeSpesialis(['nama' => 'Deleted']);
        $deleted->delete();

        $trashed = Spesialis::onlyTrashed()->where('rumah_sakit_id', $this->rs->id)->get();

        $this->assertCount(1, $trashed);
        $this->assertEquals('Deleted', $trashed->first()->nama);
    }

    // ── PoliKlinik SoftDelete ─────────────────────────────────────────────────

    public function test_poliklinik_soft_delete_tidak_langsung_hapus(): void
    {
        $poli = $this->makePoliKlinik();
        $poli->delete();

        $this->assertSoftDeleted('poliklinik', ['id' => $poli->id]);
        $this->assertDatabaseHas('poliklinik', ['id' => $poli->id]);
    }

    public function test_poliklinik_soft_deleted_tidak_muncul_di_query_biasa(): void
    {
        $poli = $this->makePoliKlinik(['nama' => 'Poli Tersembunyi']);
        $poli->delete();

        $this->assertNull(PoliKlinik::find($poli->id));
    }

    public function test_poliklinik_dapat_di_restore(): void
    {
        $poli = $this->makePoliKlinik();
        $poli->delete();

        $poli->restore();

        $this->assertNotSoftDeleted('poliklinik', ['id' => $poli->id]);
        $this->assertNotNull(PoliKlinik::find($poli->id));
    }

    public function test_poliklinik_dapat_di_force_delete(): void
    {
        $poli = $this->makePoliKlinik();
        $poli->delete();
        $poli->forceDelete();

        $this->assertDatabaseMissing('poliklinik', ['id' => $poli->id]);
    }

    public function test_poliklinik_with_trashed_mengembalikan_semua(): void
    {
        $poli = $this->makePoliKlinik();
        $poli->delete();

        $all = PoliKlinik::withTrashed()->where('id', $poli->id)->get();
        $this->assertCount(1, $all);
    }

    // ── Admin route access (Filament) ─────────────────────────────────────────

    public function test_superadmin_akses_halaman_dokter(): void
    {
        $this->actingAs($this->superAdmin())
            ->get($this->adminUrl('dokters'))
            ->assertOk();
    }

    public function test_superadmin_akses_halaman_spesialis(): void
    {
        $this->actingAs($this->superAdmin())
            ->get($this->adminUrl('spesialis'))
            ->assertOk();
    }

    public function test_superadmin_akses_halaman_poliklinik(): void
    {
        $this->actingAs($this->superAdmin())
            ->get($this->adminUrl('poliklinik'))
            ->assertOk();
    }

    // ── Relasi tidak rusak setelah soft delete ────────────────────────────────

    public function test_dokter_soft_deleted_relasi_spesialis_masih_ada(): void
    {
        $sp     = $this->makeSpesialis(['nama' => 'Sp. Kandungan']);
        $dokter = Dokter::factory()->create([
            'rumah_sakit_id' => $this->rs->id,
            'spesialis_id'   => $sp->id,
        ]);
        $dokter->delete();

        // Spesialis tidak ikut terhapus
        $this->assertNotSoftDeleted('spesialis', ['id' => $sp->id]);
        $this->assertNotNull(Spesialis::find($sp->id));
    }

    public function test_spesialis_soft_deleted_tidak_hapus_dokter(): void
    {
        $sp     = $this->makeSpesialis();
        $dokter = Dokter::factory()->create([
            'rumah_sakit_id' => $this->rs->id,
            'spesialis_id'   => $sp->id,
        ]);
        $sp->delete();

        // Dokter tidak ikut terhapus
        $this->assertNotSoftDeleted('dokter', ['id' => $dokter->id]);
    }

    // ── Factory dengan SoftDeletes ────────────────────────────────────────────

    public function test_dokter_factory_default_tidak_soft_deleted(): void
    {
        $dokter = $this->makeDokter();
        $this->assertNull($dokter->deleted_at);
        $this->assertNotSoftDeleted('dokter', ['id' => $dokter->id]);
    }

    public function test_spesialis_factory_default_tidak_soft_deleted(): void
    {
        $sp = $this->makeSpesialis();
        $this->assertNull($sp->deleted_at);
    }

    public function test_poliklinik_factory_default_tidak_soft_deleted(): void
    {
        $poli = $this->makePoliKlinik();
        $this->assertNull($poli->deleted_at);
    }
}
