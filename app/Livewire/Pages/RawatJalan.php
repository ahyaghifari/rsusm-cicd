<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\PoliKlinik;

class RawatJalan extends RsPortalComponent
{
    public function mount(): void
    {
        $this->seo('Rawat Jalan', 'Layanan rawat jalan dan jadwal poliklinik di ' . $this->rs->nama . '.');
    }

    public function render()
    {
        $poliklinik = PoliKlinik::where('rumah_sakit_id', $this->rs->id)
            ->where('aktif', true)
            ->orderBy('nama')
            ->get();

        return view('rumah_sakit.pages.rawat-jalan', [
            'poliklinik' => $poliklinik,
            'rsSlug'     => $this->rs->slug,
        ]);
    }
}
