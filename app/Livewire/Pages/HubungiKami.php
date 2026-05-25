<?php

namespace App\Livewire\Pages;

use App\Models\Kontak;
use App\Models\RumahSakit;
use Livewire\Component;

class HubungiKami extends Component
{
    public RumahSakit $rs;

    public function mount(): void
    {
        $this->rs = current_rumahsakit();
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
