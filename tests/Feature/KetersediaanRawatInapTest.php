<?php

namespace Tests\Feature;

use App\Livewire\Pages\KetersediaanRawatInap;
use App\Models\KelasRawatInap;
use App\Models\RumahSakit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KetersediaanRawatInapTest extends TestCase
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

        app()->instance('currentRumahSakit', $this->rs);

        config(['services.ranap.rumah_sakit_id' => $this->rs->id]);
        config(['services.ranap.mock_path' => 'app/mock/ranap-ketersediaan-test.json']);
    }

    private function putFixture(array $records): void
    {
        $path = storage_path('app/mock/ranap-ketersediaan-test.json');
        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, json_encode($records));
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('app/mock/ranap-ketersediaan-test.json'));
        parent::tearDown();
    }

    public function test_halaman_kosong_jika_rs_bukan_yang_dikonfigurasi_ranap(): void
    {
        config(['services.ranap.rumah_sakit_id' => 999]);

        $this->putFixture([
            ['id' => 1, 'ruangKamar' => 1, 'tempatTidur' => 'BED 1', 'status' => 1, 'tanggal' => null, 'keterangan' => null, 'ruangan' => 'X', 'namaKamar' => 'KAMAR X', 'idKelas' => null],
        ]);

        Livewire::test(KetersediaanRawatInap::class)
            ->assertSee('Data ketersediaan belum tersedia');
    }

    public function test_halaman_tampilkan_ringkasan_dan_kamar_dari_fixture(): void
    {
        $this->putFixture([
            ['id' => 3, 'ruangKamar' => 3, 'tempatTidur' => 'BED STROKE 01', 'status' => 3, 'tanggal' => '2026-06-19T02:07:37.000Z', 'keterangan' => null, 'ruangan' => '101020106', 'namaKamar' => 'STROKE CENTER', 'idKelas' => 7],
            ['id' => 4, 'ruangKamar' => 3, 'tempatTidur' => 'BED STROKE 02', 'status' => 1, 'tanggal' => '2026-06-19T02:07:37.000Z', 'keterangan' => null, 'ruangan' => '101020106', 'namaKamar' => 'STROKE CENTER', 'idKelas' => 7],
        ]);

        Livewire::test(KetersediaanRawatInap::class)
            ->assertSee('STROKE CENTER')
            ->assertSee('BED STROKE 01')
            ->assertSee('BED STROKE 02')
            ->assertSee('Terisi')
            ->assertSee('Kosong');
    }

    public function test_kelas_resolve_dari_idKelas_tampil_di_halaman(): void
    {
        KelasRawatInap::create([
            'rumah_sakit_id' => $this->rs->id,
            'nama' => 'Stroke Center',
            'id_kelas_api' => 7,
        ]);

        $this->putFixture([
            ['id' => 3, 'ruangKamar' => 3, 'tempatTidur' => 'BED STROKE 01', 'status' => 3, 'tanggal' => null, 'keterangan' => null, 'ruangan' => '101020106', 'namaKamar' => 'STROKE CENTER', 'idKelas' => 7],
        ]);

        Livewire::test(KetersediaanRawatInap::class)
            ->assertSee('Stroke Center');
    }

    public function test_filter_nama_kamar_menyaring_hasil(): void
    {
        $this->putFixture([
            ['id' => 1, 'ruangKamar' => 1, 'tempatTidur' => 'BED A', 'status' => 1, 'tanggal' => null, 'keterangan' => null, 'ruangan' => 'A', 'namaKamar' => 'KAMAR A', 'idKelas' => null],
            ['id' => 2, 'ruangKamar' => 2, 'tempatTidur' => 'BED B', 'status' => 1, 'tanggal' => null, 'keterangan' => null, 'ruangan' => 'B', 'namaKamar' => 'KAMAR B', 'idKelas' => null],
        ]);

        // "KAMAR B" tetap dicek di dropdown filter (sengaja menampilkan semua pilihan),
        // jadi yang dipastikan tersaring adalah isi kartu kamarnya (nama bed), bukan teks
        // opsi dropdown.
        Livewire::test(KetersediaanRawatInap::class)
            ->set('namaKamarFilter', 'KAMAR A')
            ->assertSee('BED A')
            ->assertDontSee('BED B');
    }
}
