<?php

namespace App\Livewire\Pages;

use App\Models\Partner;
use App\Models\RumahSakit;
use Livewire\Component;

class PartnerKami extends Component
{
    public RumahSakit $rs;

    public string $search = '';

    public function mount(): void
    {
        $this->rs = current_rumahsakit();
    }

    public function render()
    {
        $partners = Partner::where('rumah_sakit_id', $this->rs->id)
            ->where('aktif', true)
            ->when($this->search, fn ($q) => $q->where('nama', 'like', "%{$this->search}%"))
            ->orderBy('nama')
            ->get();

        return view('rumah_sakit.pages.partner-kami', [
            'partner_asuransi'   => $partners->where('kategori', 'ASURANSI'),
            'partner_perusahaan' => $partners->where('kategori', 'PERUSAHAAN'),
        ]);
    }
}
