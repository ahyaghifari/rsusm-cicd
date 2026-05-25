<?php

namespace App\Livewire\Pages;

use App\Models\LayananUnggulan as ModelsLayananUnggulan;
use Livewire\Component;
use App\Models\RumahSakit;

class LayananUnggulan extends Component
{
    public RumahSakit $rs;

    public function mount(){
        $this->rs = current_rumahsakit();
    }

    public function render()
    {
        $layanan = ModelsLayananUnggulan::where('rumah_sakit_id', $this->rs->id)->get();
        return view('rumah_sakit.pages.layanan-unggulan', ['data' => $layanan]);
    }
}
