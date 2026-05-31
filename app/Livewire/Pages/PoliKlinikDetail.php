<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\JadwalLayanan;
use App\Models\PoliKlinik;
use Artesaos\SEOTools\Facades\OpenGraph;

class PoliKlinikDetail extends RsPortalComponent
{
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
        $hariOrder = ['SENIN' => 1, 'SELASA' => 2, 'RABU' => 3, 'KAMIS' => 4, 'JUMAT' => 5, 'SABTU' => 6, 'MINGGU' => 7];

        $jadwalMingguan = JadwalLayanan::where('poliklinik_id', $this->poliklinik->id)
            ->orderByRaw("FIELD(hari, 'SENIN','SELASA','RABU','KAMIS','JUMAT','SABTU','MINGGU')")
            ->orderBy('jam_mulai')
            ->get()
            ->groupBy(fn ($j) => $j->hari->value);

        return view('rumah_sakit.pages.poliklinik-detail', [
            'jadwalMingguan' => $jadwalMingguan,
        ]);
    }
}
