<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\Partner;

class PartnerKami extends RsPortalComponent
{
    public string $search = '';

    public function mount(): void
    {
        $this->seo('Partner Kami', 'Mitra asuransi dan perusahaan rekanan ' . $this->rs->nama . '.');
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
