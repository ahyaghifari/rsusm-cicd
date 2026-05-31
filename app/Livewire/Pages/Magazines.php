<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\Magazine;

class Magazines extends RsPortalComponent
{
    public function mount(): void
    {
        $this->seo('Syifa Magazine', 'Majalah dan publikasi digital dari ' . $this->rs->nama . '.');
    }

    public function render()
    {
        $magazines = Magazine::where('rumah_sakit_id', $this->rs->id)
            ->aktif()
            ->orderByDesc('published_at')
            ->get();

        return view('rumah_sakit.pages.magazines', [
            'magazines' => $magazines,
        ]);
    }
}
