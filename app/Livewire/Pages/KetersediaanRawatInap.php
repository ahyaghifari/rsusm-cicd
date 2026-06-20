<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\KelasRawatInap;
use App\Services\RanapApiClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class KetersediaanRawatInap extends RsPortalComponent
{
    public ?int $kelasFilter = null;

    public ?string $namaKamarFilter = null;

    public function mount(): void
    {
        $this->seo('Ketersediaan Rawat Inap', 'Cek ketersediaan kamar rawat inap secara real-time di ' . $this->rs->nama . '.');
    }

    public function render()
    {
        // Data ketersediaan tidak disimpan ke database — selalu diambil langsung dari
        // API/fixture Ranap setiap render (termasuk tiap wire:poll). Lihat
        // issues/ketersediaan-rawat-inap-plan.md.
        $ranapRumahSakitId = (int) config('services.ranap.rumah_sakit_id');

        if ($ranapRumahSakitId !== $this->rs->id) {
            return view('rumah_sakit.pages.ketersediaan-rawat-inap', [
                'kamarList'        => collect(),
                'ringkasan'        => $this->emptyRingkasan(),
                'kelasOptions'     => collect(),
                'namaKamarOptions' => collect(),
                'loadedAt'         => Carbon::now(),
            ]);
        }

        $kelasByApiId = KelasRawatInap::where('rumah_sakit_id', $this->rs->id)
            ->whereNotNull('id_kelas_api')
            ->get()
            ->keyBy('id_kelas_api');

        $semuaBed = collect(app(RanapApiClient::class)->fetch())
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
            });

        $namaKamarOptions = $semuaBed->pluck('nama_kamar')->unique()->sort()->values();

        $beds = $semuaBed
            ->when($this->kelasFilter, fn (Collection $c) => $c->where('kelas_id', $this->kelasFilter))
            ->when($this->namaKamarFilter, fn (Collection $c) => $c->where('nama_kamar', $this->namaKamarFilter))
            ->sortBy(['nama_kamar', 'tempat_tidur']);

        $ringkasan = $this->emptyRingkasan();
        foreach ($beds as $bed) {
            if (array_key_exists($bed['status'], $ringkasan)) {
                $ringkasan[$bed['status']]++;
            }
        }

        $kamarList = $beds->groupBy(fn ($bed) => $bed['ruang_kamar'] . '|' . $bed['nama_kamar']);

        return view('rumah_sakit.pages.ketersediaan-rawat-inap', [
            'kamarList'        => $kamarList,
            'ringkasan'        => $ringkasan,
            'kelasOptions'     => KelasRawatInap::where('rumah_sakit_id', $this->rs->id)->orderBy('nama')->get(),
            'namaKamarOptions' => $namaKamarOptions,
            'loadedAt'         => Carbon::now(),
        ]);
    }

    private function emptyRingkasan(): array
    {
        return [1 => 0, 2 => 0, 3 => 0, 6 => 0];
    }
}
