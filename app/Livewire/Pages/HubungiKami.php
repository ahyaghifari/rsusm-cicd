<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\Kontak;

class HubungiKami extends RsPortalComponent
{
    public function mount(): void
    {
        $this->seo('Hubungi Kami', 'Informasi kontak, lokasi, dan jam operasional ' . $this->rs->nama . '.');
    }

    public function render()
    {
        $kontak = Kontak::where('rumah_sakit_id', $this->rs->id)
            ->where('aktif', true)
            ->orderBy('kategori')
            ->get();

        return view('rumah_sakit.pages.hubungi-kami', [
            'operasional'  => $kontak->where('kategori', 'OPERASIONAL')->values(),
            'sosial_media' => $kontak->where('kategori', 'SOSIAL MEDIA')->values(),
        ]);
    }
}
