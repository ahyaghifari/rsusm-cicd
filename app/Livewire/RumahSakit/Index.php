<?php

namespace App\Livewire\RumahSakit;

use App\Models\LayananUnggulan;
use App\Models\LinkLayanan;
use App\Models\Partner;
use App\Models\RumahSakit;
use Livewire\Component;

class Index extends Component
{
    public RumahSakit $rs;

    public function mount(){
        $this->rs = current_rumahsakit();
    }

    public function render()
    {
        $layananUnggulan = LayananUnggulan::where('rumah_sakit_id', $this->rs->id)->get();

        $linkLayanan = LinkLayanan::where('rumah_sakit_id', $this->rs->id)
            ->where('aktif', true)
            ->get();

        $partnerAsuransi = Partner::where('rumah_sakit_id', $this->rs->id)
            ->where('kategori', 'ASURANSI')
            ->where('aktif', true)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        $partnerPerusahaan = Partner::where('rumah_sakit_id', $this->rs->id)
            ->where('kategori', 'PERUSAHAAN')
            ->where('aktif', true)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        return view('rumah_sakit.index', [
            'layanan_unggulan'   => $layananUnggulan,
            'link_layanan'       => $linkLayanan,
            'partner_asuransi'   => $partnerAsuransi,
            'partner_perusahaan' => $partnerPerusahaan,
        ]);
    }
}
