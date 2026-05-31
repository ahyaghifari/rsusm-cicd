<?php

namespace App\Livewire;

use App\Models\RumahSakit;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Livewire\Component;

abstract class RsPortalComponent extends Component
{
    public ?RumahSakit $rs = null;

    // Diisi oleh mount() tiap halaman agar booted() bisa build full title dengan benar
    protected string $seoTitle       = '';
    protected string $seoDescription = '';

    public function boot(): void
    {
        if ($this->rs === null) {
            $this->rs = current_rumahsakit();
        }
    }

    /**
     * Panggil ini dari mount() di tiap halaman sebagai pengganti SEOMeta::setTitle().
     * Contoh: $this->seo('Rawat Jalan', 'Layanan poliklinik di ...');
     */
    protected function seo(string $title, string $description = ''): void
    {
        $this->seoTitle       = $title;
        $this->seoDescription = $description;

        $rsName    = $this->rs?->nama ?? '';
        $fullTitle = $title && $rsName ? "$title - $rsName" : ($title ?: $rsName);

        SEOMeta::setTitle($fullTitle);
        if ($description) SEOMeta::setDescription($description);
    }

    // Runs AFTER mount() — sync OG & JSON-LD dari seoTitle/seoDescription yang sudah di-set
    public function booted(): void
    {
        if (! $this->rs) return;

        $rsName    = $this->rs->nama;
        $fullTitle = $this->seoTitle
            ? $this->seoTitle . ' - ' . $rsName
            : $rsName;
        $desc = $this->seoDescription;

        OpenGraph::setTitle($fullTitle);
        OpenGraph::setUrl(request()->url());
        OpenGraph::addProperty('site_name', $rsName);
        if ($desc) OpenGraph::setDescription($desc);

        JsonLd::setTitle($fullTitle);
        if ($desc) JsonLd::setDescription($desc);
        JsonLd::addValue('url', request()->url());
    }
}
