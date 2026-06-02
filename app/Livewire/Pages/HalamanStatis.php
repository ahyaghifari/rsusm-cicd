<?php

namespace App\Livewire\Pages;

use App\Livewire\RsPortalComponent;
use App\Models\Halaman;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;

class HalamanStatis extends RsPortalComponent
{
    #[Locked]
    public ?Halaman $halaman = null;

    public function mount(string $slug): void
    {
        $this->halaman = Halaman::where('rumah_sakit_id', $this->rs->id)
            ->where('slug', $slug)
            ->where('aktif', true)
            ->firstOrFail();

        $this->seo(
            $this->halaman->judul,
            Str::limit(strip_tags(preg_replace('/\s+/', ' ', $this->halaman->konten ?? '')), 155)
        );
    }

    public function render()
    {
        return view('rumah_sakit.pages.halaman-statis');
    }
}
