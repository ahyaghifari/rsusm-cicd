<?php

namespace Tests\Feature;

use App\Models\KelasRawatInap;
use App\Models\RawatInapKetersediaan;
use App\Models\RumahSakit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncKetersediaanRawatInapTest extends TestCase
{
    use RefreshDatabase;

    private RumahSakit $rs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rs = RumahSakit::create([
            'nama' => 'RS Test',
            'slug' => 'rs-test',
            'lokasi' => 'Test Lokasi',
            'alamat' => 'Test Alamat',
            'aktif' => true,
        ]);

        config(['services.ranap.rumah_sakit_id' => $this->rs->id]);
        config(['services.ranap.mock_path' => 'app/mock/ranap-ketersediaan-test.json']);
    }

    private function putFixture(array $records): void
    {
        $path = storage_path('app/mock/ranap-ketersediaan-test.json');
        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, json_encode($records));
    }

    private function tearDownFixture(): void
    {
        @unlink(storage_path('app/mock/ranap-ketersediaan-test.json'));
    }

    protected function tearDown(): void
    {
        $this->tearDownFixture();
        parent::tearDown();
    }

    public function test_sync_membuat_record_ketersediaan_dari_fixture(): void
    {
        $this->putFixture([
            [
                'id' => 3, 'ruangKamar' => 3, 'tempatTidur' => 'BED STROKE 01', 'status' => 3,
                'tanggal' => '2026-06-19T02:07:37.000Z', 'keterangan' => null,
                'ruangan' => '101020106', 'namaKamar' => 'STROKE CENTER', 'idKelas' => 7,
            ],
        ]);

        $this->artisan('rawat-inap:sync-ketersediaan')->assertExitCode(0);

        $this->assertDatabaseHas('rawat_inap_ketersediaan', [
            'rumah_sakit_id' => $this->rs->id,
            'external_id'    => 3,
            'tempat_tidur'   => 'BED STROKE 01',
            'status'         => 3,
            'nama_kamar'     => 'STROKE CENTER',
            'kelas_rawat_inap_id' => null,
        ]);
    }

    public function test_sync_resolve_kelas_rawat_inap_id_dari_idKelas(): void
    {
        $kelas = KelasRawatInap::create([
            'rumah_sakit_id' => $this->rs->id,
            'nama'           => 'Stroke Center',
            'id_kelas_api'   => 7,
        ]);

        $this->putFixture([
            [
                'id' => 3, 'ruangKamar' => 3, 'tempatTidur' => 'BED STROKE 01', 'status' => 3,
                'tanggal' => '2026-06-19T02:07:37.000Z', 'keterangan' => null,
                'ruangan' => '101020106', 'namaKamar' => 'STROKE CENTER', 'idKelas' => 7,
            ],
        ]);

        $this->artisan('rawat-inap:sync-ketersediaan');

        $this->assertDatabaseHas('rawat_inap_ketersediaan', [
            'external_id'         => 3,
            'kelas_rawat_inap_id' => $kelas->id,
        ]);
    }

    public function test_sync_kedua_kali_upsert_bukan_duplikat(): void
    {
        $this->putFixture([
            [
                'id' => 4, 'ruangKamar' => 3, 'tempatTidur' => 'BED STROKE 02', 'status' => 1,
                'tanggal' => '2026-06-19T02:07:37.000Z', 'keterangan' => null,
                'ruangan' => '101020106', 'namaKamar' => 'STROKE CENTER', 'idKelas' => 7,
            ],
        ]);

        $this->artisan('rawat-inap:sync-ketersediaan');

        $this->putFixture([
            [
                'id' => 4, 'ruangKamar' => 3, 'tempatTidur' => 'BED STROKE 02', 'status' => 6,
                'tanggal' => '2026-06-19T03:00:00.000Z', 'keterangan' => 'AC rusak',
                'ruangan' => '101020106', 'namaKamar' => 'STROKE CENTER', 'idKelas' => 7,
            ],
        ]);

        $this->artisan('rawat-inap:sync-ketersediaan');

        $this->assertDatabaseCount('rawat_inap_ketersediaan', 1);
        $this->assertDatabaseHas('rawat_inap_ketersediaan', [
            'external_id' => 4,
            'status'      => 6,
            'keterangan'  => 'AC rusak',
        ]);
    }

    public function test_sync_gagal_jika_rumah_sakit_id_belum_dikonfigurasi(): void
    {
        config(['services.ranap.rumah_sakit_id' => null]);

        $this->artisan('rawat-inap:sync-ketersediaan')->assertExitCode(1);
    }
}
