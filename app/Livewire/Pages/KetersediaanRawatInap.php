<?php

namespace App\Livewire\Pages;

use App\Models\KelasRawatInap;
use App\Models\Kontak;
use App\Models\RumahSakit;
use App\Services\RanapApiClient;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Component;

class KetersediaanRawatInap extends Component
{
    #[Locked]
    public int $rumah_sakit_id;

    public ?int $kelasFilter = null;

    public ?string $namaKamarFilter = null;

    public ?int $statusFilter = null;

    public string $groupBy = 'kamar';

    public function boot(): void
    {
        // Re-bind currentRumahSakit ke container agar tersedia bagi komponen lain
        // (mis. GlobalSearch) yang berjalan bersamaan dalam satu Livewire AJAX request.
        // RumahSakitMiddleware tidak jalan di /livewire/update, jadi kita bind manual.
        // Ini juga yang dibutuhkan agar wire:poll tidak error BindingResolutionException
        // setelah request awal, karena poll selalu lewat /livewire/update.
        if (! empty($this->rumah_sakit_id) && ! app()->bound('currentRumahSakit')) {
            $rs = RumahSakit::find($this->rumah_sakit_id);
            if ($rs) {
                app()->instance('currentRumahSakit', $rs);
            }
        }
    }

    public function mount(): void
    {
        $rs = current_rumahsakit();
        $this->rumah_sakit_id = $rs->id;

        $fullTitle = 'Ketersediaan Rawat Inap - ' . $rs->nama;
        $desc      = 'Cek ketersediaan kamar rawat inap secara real-time di ' . $rs->nama . '.';
        SEOMeta::setTitle($fullTitle);
        SEOMeta::setDescription($desc);
        OpenGraph::setTitle($fullTitle);
        OpenGraph::setDescription($desc);
        OpenGraph::setUrl(request()->url());
        OpenGraph::addProperty('site_name', $rs->nama);
    }

    public function render()
    {
        // Data ketersediaan tidak disimpan ke database — selalu diambil langsung dari
        // API/fixture Ranap setiap render (termasuk tiap wire:poll). Lihat
        // issues/link-layanan-static-dan-ranap-multi-tenant.md.
        $rs = RumahSakit::find($this->rumah_sakit_id);

        $kelasByApiId = KelasRawatInap::where('rumah_sakit_id', $this->rumah_sakit_id)
            ->whereNotNull('id_kelas_api')
            ->get()
            ->keyBy('id_kelas_api');

        $semuaBed = collect(app(RanapApiClient::class)->fetch($rs?->ranap_kode_api))
            ->map(function (array $r) use ($kelasByApiId) {
                $kelas = $kelasByApiId->get($r['idKelas'] ?? null);

                return [
                    'ruang_kamar'   => $r['ruangKamar'],
                    'tempat_tidur'  => $r['tempatTidur'],
                    'status'        => $r['status'],
                    'tanggal'       => $r['tanggal'] ?? null,
                    'keterangan'    => $r['keterangan'] ?? null,
                    'nama_kamar'    => $r['namaKamar'],
                    'kelas_id'      => $kelas?->id,
                    'kelas_nama'    => $kelas?->nama,
                ];
            })
            ->reject(fn (array $r) => $r['status'] === 0);

        $namaKamarOptions = $semuaBed->pluck('nama_kamar')->unique()->sort()->values();

        $sortKeys = $this->groupBy === 'kelas'
            ? ['kelas_nama', 'nama_kamar', 'tempat_tidur']
            : ['nama_kamar', 'tempat_tidur'];

        $beds = $semuaBed
            ->when($this->kelasFilter, fn (Collection $c) => $c->where('kelas_id', $this->kelasFilter))
            ->when($this->namaKamarFilter, fn (Collection $c) => $c->where('nama_kamar', $this->namaKamarFilter))
            ->when($this->statusFilter, fn (Collection $c) => $c->where('status', $this->statusFilter))
            ->sortBy($sortKeys);

        $ringkasan = $this->emptyRingkasan();
        foreach ($beds as $bed) {
            if (array_key_exists($bed['status'], $ringkasan)) {
                $ringkasan[$bed['status']]++;
            }
        }

        $kamarList = $this->groupBy === 'kelas'
            ? $beds->groupBy(fn ($bed) => $bed['kelas_id'] ?? 'tanpa-kelas')
            : $beds->groupBy(fn ($bed) => $bed['ruang_kamar'] . '|' . $bed['nama_kamar']);

        return view('rumah_sakit.pages.ketersediaan-rawat-inap', [
            'kamarList'        => $kamarList,
            'ringkasan'        => $ringkasan,
            'kelasOptions'     => KelasRawatInap::where('rumah_sakit_id', $this->rumah_sakit_id)->orderBy('nama')->get(),
            'namaKamarOptions' => $namaKamarOptions,
            'loadedAt'         => Carbon::now(),
            'totalBed'         => $semuaBed->count(),
            'jumlahHasil'      => $beds->count(),
            // Diquery eksplisit (bukan andalkan view()->share() dari RumahSakitMiddleware)
            // karena middleware itu tidak jalan lagi di /livewire/update setelah wire:poll.
            // Kategori RAWAT INAP dedicated (bukan reuse PENDAFTARAN) supaya admin bisa isi
            // nomor/ekstensi khusus rawat inap kalau beda dari pendaftaran umum.
            'kontakRawatInap' => Kontak::where('rumah_sakit_id', $this->rumah_sakit_id)
                ->where('kategori', 'RAWAT INAP')
                ->where('aktif', true)
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    private function emptyRingkasan(): array
    {
        return [1 => 0, 2 => 0, 3 => 0, 6 => 0];
    }
}
