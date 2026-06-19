<?php

namespace App\Livewire\Pages;

use App\Enums\StatusKetersediaanKamar;
use App\Livewire\RsPortalComponent;
use App\Models\KelasRawatInap;
use App\Models\RawatInapKetersediaan;

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
        $query = RawatInapKetersediaan::where('rumah_sakit_id', $this->rs->id)
            ->with('kelasRawatInap');

        if ($this->kelasFilter) {
            $query->where('kelas_rawat_inap_id', $this->kelasFilter);
        }

        if ($this->namaKamarFilter) {
            $query->where('nama_kamar', $this->namaKamarFilter);
        }

        $beds = $query->orderBy('nama_kamar')->orderBy('tempat_tidur')->get();

        $ringkasan = [
            StatusKetersediaanKamar::KOSONG->value    => 0,
            StatusKetersediaanKamar::RESERVASI->value => 0,
            StatusKetersediaanKamar::TERISI->value    => 0,
            StatusKetersediaanKamar::PERBAIKAN->value => 0,
        ];

        foreach ($beds as $bed) {
            if (array_key_exists($bed->status, $ringkasan)) {
                $ringkasan[$bed->status]++;
            }
        }

        $kamarList = $beds->groupBy(fn ($bed) => $bed->ruang_kamar . '|' . $bed->nama_kamar);

        return view('rumah_sakit.pages.ketersediaan-rawat-inap', [
            'kamarList'       => $kamarList,
            'ringkasan'       => $ringkasan,
            'kelasOptions'    => KelasRawatInap::where('rumah_sakit_id', $this->rs->id)->orderBy('nama')->get(),
            'namaKamarOptions' => RawatInapKetersediaan::where('rumah_sakit_id', $this->rs->id)
                ->distinct()
                ->orderBy('nama_kamar')
                ->pluck('nama_kamar'),
            'lastSyncedAt'    => $beds->max('synced_at'),
        ]);
    }
}
