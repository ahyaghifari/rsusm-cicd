<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\Faq as FaqModel;

class Faq extends RsPortalComponent
{
    public function mount(): void
    {
        $this->seo('FAQ', 'Pertanyaan yang sering ditanyakan seputar layanan ' . $this->rs->nama . '.');
    }

    public function render()
    {
        $faqs = FaqModel::where('rumah_sakit_id', $this->rs->id)
            ->aktif()
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();

        return view('rumah_sakit.pages.faq', compact('faqs'));
    }
}
