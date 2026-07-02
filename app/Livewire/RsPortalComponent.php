<?php

namespace App\Livewire;

use App\Models\RumahSakit;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Livewire\Attributes\Locked;
use Livewire\Component;

abstract class RsPortalComponent extends Component
{
    #[Locked]
    public ?RumahSakit $rs = null;

    // Diisi oleh mount() tiap halaman agar booted() bisa build full title dengan benar
    protected string  $seoTitle       = '';
    protected string  $seoDescription = '';
    protected ?string $seoImage       = null;

    public function boot(): void
    {
        if ($this->rs === null) {
            $this->rs = current_rumahsakit();
        }
    }

    /**
     * Panggil ini dari mount() di tiap halaman sebagai pengganti SEOMeta::setTitle().
     * Contoh: $this->seo('Rawat Jalan', 'Layanan poliklinik di ...', $imageUrl);
     */
    protected function seo(string $title, string $description = '', ?string $image = null): void
    {
        $this->seoTitle       = $title;
        $this->seoDescription = $description;
        $this->seoImage       = $image;

        $rsName    = $this->rs?->nama ?? '';
        $fullTitle = $title && $rsName ? "$title - $rsName" : ($title ?: $rsName);

        SEOMeta::setTitle($fullTitle);
        if ($description) SEOMeta::setDescription($description);
    }

    // Runs AFTER mount() — sync OG, canonical, & JSON-LD dari seoTitle/seoDescription
    public function booted(): void
    {
        if (! $this->rs) return;

        $rsName    = $this->rs->nama;
        $fullTitle = $this->seoTitle
            ? $this->seoTitle . ' - ' . $rsName
            : $rsName;
        $desc = $this->seoDescription;
        $url  = request()->url();

        // Canonical — cegah duplicate content multi-cabang / query params
        SEOMeta::setCanonical($url);

        OpenGraph::setTitle($fullTitle);
        OpenGraph::setUrl($url);
        OpenGraph::addProperty('site_name', $rsName);
        if ($desc) OpenGraph::setDescription($desc);

        // OG image: custom dari halaman → fallback logo RS
        $image = $this->seoImage
            ?? ($this->rs->logo ? asset('storage/' . $this->rs->logo) : null);
        if ($image) OpenGraph::addImage($image);

    }
}
