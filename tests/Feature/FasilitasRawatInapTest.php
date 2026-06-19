<?php

namespace Tests\Feature;

use App\Models\FasilitasRawatInap;
use App\Models\Gedung;
use App\Models\RawatInap;
use App\Models\RumahSakit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FasilitasRawatInapTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_fasilitas_rawat_inap_and_relation_works(): void
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

        $fasilitas = FasilitasRawatInap::create([
            'rawat_inap_id' => $rawatInap->id,
            'nama' => 'TV',
            'aktif' => true,
        ]);

        $this->assertDatabaseHas('fasilitas_rawat_inap', [
            'id' => $fasilitas->id,
            'nama' => 'TV',
        ]);

        $this->assertEquals($rawatInap->id, $fasilitas->rawatInap->id);
        $this->assertCount(1, $rawatInap->fasilitasRawatInap);
        $this->assertEquals('TV', $rawatInap->fasilitasRawatInap->first()->nama);
    }

    public function test_filament_resource_pages_requires_auth(): void
    {
        $response = $this->get($this->adminUrl('fasilitas-rawat-inaps'));
        $response->assertStatus(302); // Redirect to login
    }
}
