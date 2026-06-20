<?php

namespace App\Livewire\Dokter;

use App\Livewire\RsPortalComponent;
use App\Models\Dokter;
use App\Models\JadwalPraktek;
use App\Services\AntrianApiClient;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Livewire\Attributes\Locked;

class Show extends RsPortalComponent
{
    #[Locked]
    public Dokter $dokter;

    public function mount(Dokter $dokter): void
    {
        $rs = $this->rs;

        abort_unless($dokter->rumah_sakit_id === $rs->id, 404);

        $this->dokter = $dokter;

        $desc = $dokter->deskripsi
            ? \Illuminate\Support\Str::limit(strip_tags($dokter->deskripsi), 155)
            : 'Dokter ' . ($dokter->spesialis?->nama ?? '') . ' di ' . $rs->nama;

        $fullTitle = $dokter->nama . ' - ' . $rs->nama;

        SEOMeta::setTitle($fullTitle);
        SEOMeta::setDescription($desc);
        OpenGraph::setTitle($fullTitle);
        OpenGraph::setDescription($desc);
        OpenGraph::setUrl(request()->url());
        OpenGraph::addProperty('site_name', $rs->nama);
        if ($dokter->foto) {
            OpenGraph::addImage(asset('storage/' . $dokter->foto));
        }
    }

    public function render()
    {
        $rs = $this->rs;

        $hariOrder = ['SENIN' => 1, 'SELASA' => 2, 'RABU' => 3, 'KAMIS' => 4, 'JUMAT' => 5, 'SABTU' => 6, 'MINGGU' => 7];

        $jadwal = JadwalPraktek::where('dokter_id', $this->dokter->id)
            ->whereHas('poliklinik', fn ($q) => $q->where('rumah_sakit_id', $rs->id))
            ->with('poliklinik')
            ->get()
            ->sortBy(fn ($j) => $hariOrder[$j->hari->value] ?? 8);

        // Status antrian diambil live tiap render (tidak disimpan ke database) — sama
        // seperti pola RanapApiClient di KetersediaanRawatInap. Base URL API-nya per RS,
        // dari kolom rumah_sakit.link_antrian (sama kolom dengan kartu "Pantauan Antrian").
        $antrian = $this->dokter->nomor_poli_antrian
            ? app(AntrianApiClient::class)->fetch($rs->link_antrian, $this->dokter->nomor_poli_antrian)
            : null;

        return view('rumah_sakit.dokter.show', [
            'dokter'  => $this->dokter,
            'jadwal'  => $jadwal,
            'rs'      => $rs,
            'antrian' => $antrian,
        ]);
    }
}
