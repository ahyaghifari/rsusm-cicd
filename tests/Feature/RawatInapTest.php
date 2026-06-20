<?php

namespace Tests\Feature;

use App\Models\Gedung;
use App\Models\RawatInap;
use App\Models\RumahSakit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RawatInapTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_rawat_inap_and_relationships_work(): void
    {
        $rs = RumahSakit::create([
            'nama' => 'RS Test',
            'slug' => 'rs-test',
            'lokasi' => 'Test Lokasi',
            'alamat' => 'Test Alamat',
            'aktif' => true,
        ]);

        $gedung = Gedung::create([
            'rumah_sakit_id' => $rs->id,
            'nama' => 'Gedung Test',
            'alias' => 'GT',
        ]);

        $rawatInap = RawatInap::create([
            'rumah_sakit_id' => $rs->id,
            'gedung_id' => $gedung->id,
            'nama' => 'Kamar Test',
            'harga' => 500000,
            'kapasitas' => 2,
            'sort_order' => 1,
            'aktif' => true,
        ]);

        $this->assertDatabaseHas('rawat_inap', [
            'id' => $rawatInap->id,
            'nama' => 'Kamar Test',
        ]);

        $this->assertEquals($rs->id, $rawatInap->rumahSakit->id);
        $this->assertEquals($gedung->id, $rawatInap->gedung->id);
        $this->assertCount(1, $rs->rawatInap);
        $this->assertCount(1, $gedung->rawatInap);
    }

    public function test_filament_resource_pages_requires_auth(): void
    {
        $response = $this->get($this->adminUrl('rawat-inaps'));
        $response->assertStatus(302); // Redirect to login
    }
}
