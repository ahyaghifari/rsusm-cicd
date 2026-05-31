<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\LayananUnggulan as ModelsLayananUnggulan;

class LayananUnggulan extends RsPortalComponent
{
    public function mount(): void
    {
        $this->seo('Layanan Unggulan', 'Layanan unggulan terbaik di ' . $this->rs->nama . '.');
    }

    public function render()
    {
        $layanan = ModelsLayananUnggulan::where('rumah_sakit_id', $this->rs->id)->get();

        return view('rumah_sakit.pages.layanan-unggulan', ['data' => $layanan]);
    }
}
