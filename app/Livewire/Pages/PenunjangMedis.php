<?php

namespace App\Livewire\Pages;

use App\Models\PenunjangMedis as ModelsPenunjangMedis;
use App\Models\RumahSakit;
use Livewire\Component;

class PenunjangMedis extends Component
{
    public RumahSakit $rs;

    public function mount(){
        $this->rs = current_rumahsakit();
    }

    public function render()
    {
        $penunjang_medis = ModelsPenunjangMedis::where('rumah_sakit_id', $this->rs->id)->get();
        return view('rumah_sakit.pages.penunjang-medis', ['data' => $penunjang_medis]);
    }
}
