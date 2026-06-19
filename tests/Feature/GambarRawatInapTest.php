<?php

namespace Tests\Feature;

use App\Models\Gedung;
use App\Models\GambarRawatInap;
use App\Models\RawatInap;
use App\Models\RumahSakit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GambarRawatInapTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_gambar_rawat_inap_and_relation_works(): void
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

        $gambar = GambarRawatInap::create([
            'rawat_inap_id' => $rawatInap->id,
            'gambar' => 'test-image.jpg',
            'deskripsi' => 'Deskripsi test gambar',
            'sort_order' => 1,
            'aktif' => true,
        ]);

        $this->assertDatabaseHas('gambar_rawat_inap', [
            'id' => $gambar->id,
            'gambar' => 'test-image.jpg',
        ]);

        $this->assertEquals($rawatInap->id, $gambar->rawatInap->id);
        $this->assertCount(1, $rawatInap->gambar);
        $this->assertEquals('test-image.jpg', $rawatInap->gambar->first()->gambar);
    }

    public function test_filament_resource_pages_requires_auth(): void
    {
        $response = $this->get($this->adminUrl('gambar-rawat-inaps'));
        $response->assertStatus(302); // Redirect to login
    }
}
