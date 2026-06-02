<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\JadwalPraktek;
use App\Models\PoliKlinik;
use Artesaos\SEOTools\Facades\OpenGraph;
use Livewire\Attributes\Locked;

class PoliKlinikDetail extends RsPortalComponent
{
    #[Locked]
    public PoliKlinik $poliklinik;

    public function mount(PoliKlinik $poliklinik): void
    {
        $poliklinik->load('unitLayanan');

        abort_unless(
            $poliklinik->unitLayanan->rumah_sakit_id === $this->rs->id,
            404
        );

        $this->poliklinik = $poliklinik;

        $this->seo(
            $poliklinik->nama,
            $poliklinik->deskripsi ?? ('Layanan ' . $poliklinik->nama . ' di ' . $this->rs->nama)
        );

        if ($poliklinik->gambar) {
            OpenGraph::addImage(asset('storage/' . $poliklinik->gambar));
        }
    }

    public function render()
    {
        // Gunakan CASE WHEN agar kompatibel MySQL dan SQLite
        $jadwalMingguan = JadwalPraktek::where('poliklinik_id', $this->poliklinik->id)
            ->orderByRaw("CASE hari
                WHEN 'SENIN'   THEN 1
                WHEN 'SELASA'  THEN 2
                WHEN 'RABU'    THEN 3
                WHEN 'KAMIS'   THEN 4
                WHEN 'JUMAT'   THEN 5
                WHEN 'SABTU'   THEN 6
                WHEN 'MINGGU'  THEN 7
                ELSE 8 END")
            ->orderBy('waktu_mulai')
            ->with('dokter')
            ->get()
            ->groupBy(fn ($j) => $j->hari->value);

        return view('rumah_sakit.pages.poliklinik-detail', [
            'jadwalMingguan' => $jadwalMingguan,
        ]);
    }
}
