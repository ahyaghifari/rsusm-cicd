<?php

namespace Tests\Feature;

use App\Livewire\Pages\KetersediaanRawatInap;
use App\Models\KelasRawatInap;
use App\Models\Kontak;
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

    public function test_halaman_tampil_dari_fixture_walau_rs_belum_punya_ranap_kode_api(): void
    {
        // Multi-tenant: RS tanpa rumah_sakit.ranap_kode_api tetap fallback ke fixture
        // lokal (bukan halaman kosong) — lihat issues/link-layanan-static-dan-ranap-multi-tenant.md.
        $this->assertNull($this->rs->ranap_kode_api);

        $this->putFixture([
            ['id' => 1, 'ruangKamar' => 1, 'tempatTidur' => 'BED 1', 'status' => 1, 'tanggal' => null, 'keterangan' => null, 'ruangan' => 'X', 'namaKamar' => 'KAMAR X', 'idKelas' => null],
        ]);

        Livewire::test(KetersediaanRawatInap::class)
            ->assertSee('BED 1')
            ->assertDontSee('Data ketersediaan belum tersedia');
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

    public function test_filter_status_menyaring_hasil(): void
    {
        $this->putFixture([
            ['id' => 1, 'ruangKamar' => 1, 'tempatTidur' => 'BED KOSONG', 'status' => 1, 'tanggal' => null, 'keterangan' => null, 'ruangan' => 'A', 'namaKamar' => 'KAMAR A', 'idKelas' => null],
            ['id' => 2, 'ruangKamar' => 1, 'tempatTidur' => 'BED TERISI', 'status' => 3, 'tanggal' => null, 'keterangan' => null, 'ruangan' => 'A', 'namaKamar' => 'KAMAR A', 'idKelas' => null],
        ]);

        Livewire::test(KetersediaanRawatInap::class)
            ->set('statusFilter', 3)
            ->assertSee('BED TERISI')
            ->assertDontSee('BED KOSONG');
    }

    public function test_filter_status_kosongkan_kembali_tampilkan_semua(): void
    {
        $this->putFixture([
            ['id' => 1, 'ruangKamar' => 1, 'tempatTidur' => 'BED KOSONG', 'status' => 1, 'tanggal' => null, 'keterangan' => null, 'ruangan' => 'A', 'namaKamar' => 'KAMAR A', 'idKelas' => null],
            ['id' => 2, 'ruangKamar' => 1, 'tempatTidur' => 'BED TERISI', 'status' => 3, 'tanggal' => null, 'keterangan' => null, 'ruangan' => 'A', 'namaKamar' => 'KAMAR A', 'idKelas' => null],
        ]);

        Livewire::test(KetersediaanRawatInap::class)
            ->set('statusFilter', 3)
            ->set('statusFilter', null)
            ->assertSee('BED TERISI')
            ->assertSee('BED KOSONG');
    }

    public function test_disclaimer_konfirmasi_resepsionis_tampilkan_kontak_rawat_inap(): void
    {
        Kontak::create([
            'rumah_sakit_id' => $this->rs->id,
            'label' => 'Rawat Inap',
            'value' => '0511-1234567',
            'link' => 'tel:05111234567',
            'kategori' => 'RAWAT INAP',
            'aktif' => true,
        ]);

        $this->putFixture([
            ['id' => 1, 'ruangKamar' => 1, 'tempatTidur' => 'BED A', 'status' => 1, 'tanggal' => null, 'keterangan' => null, 'ruangan' => 'A', 'namaKamar' => 'KAMAR A', 'idKelas' => null],
        ]);

        Livewire::test(KetersediaanRawatInap::class)
            ->assertSee('Status kamar bisa berubah dalam hitungan menit.')
            ->assertSee('Rawat Inap: 0511-1234567');
    }

    public function test_disclaimer_tidak_tampilkan_kontak_kategori_lain(): void
    {
        Kontak::create([
            'rumah_sakit_id' => $this->rs->id,
            'label' => 'Sosial Media',
            'value' => '@rsutest',
            'link' => 'https://instagram.com/rsutest',
            'kategori' => 'SOSIAL MEDIA',
            'aktif' => true,
        ]);

        // PENDAFTARAN sengaja tidak ikut tampil di halaman ini lagi — sejak kategori RAWAT INAP
        // dedicated ditambahkan, halaman ini hanya pakai kontak RAWAT INAP, bukan reuse PENDAFTARAN.
        Kontak::create([
            'rumah_sakit_id' => $this->rs->id,
            'label' => 'Pendaftaran',
            'value' => '0511-9999999',
            'link' => 'tel:05119999999',
            'kategori' => 'PENDAFTARAN',
            'aktif' => true,
        ]);

        $this->putFixture([
            ['id' => 1, 'ruangKamar' => 1, 'tempatTidur' => 'BED A', 'status' => 1, 'tanggal' => null, 'keterangan' => null, 'ruangan' => 'A', 'namaKamar' => 'KAMAR A', 'idKelas' => null],
        ]);

        Livewire::test(KetersediaanRawatInap::class)
            ->assertDontSee('Sosial Media: @rsutest')
            ->assertDontSee('Pendaftaran: 0511-9999999');
    }

    public function test_render_ulang_tidak_error_walau_binding_currentrumahsakit_hilang(): void
    {
        // Mensimulasikan request AJAX wire:poll/Livewire update — RumahSakitMiddleware
        // tidak jalan di /livewire/update sehingga binding 'currentRumahSakit' di
        // container tidak otomatis tersedia lagi. boot() di komponen ini harus
        // re-bind manual dari $rumah_sakit_id yang sudah di-mount, bukan error
        // BindingResolutionException.
        $this->putFixture([
            ['id' => 1, 'ruangKamar' => 1, 'tempatTidur' => 'BED A', 'status' => 1, 'tanggal' => null, 'keterangan' => null, 'ruangan' => 'A', 'namaKamar' => 'KAMAR A', 'idKelas' => null],
        ]);

        $component = Livewire::test(KetersediaanRawatInap::class);

        app()->forgetInstance('currentRumahSakit');
        $this->assertFalse(app()->bound('currentRumahSakit'));

        // Trigger render ulang lewat AJAX (mirip efek wire:poll) tanpa binding ter-set.
        $component->set('kelasFilter', null)->assertSee('BED A');

        $this->assertTrue(app()->bound('currentRumahSakit'));
    }
}
