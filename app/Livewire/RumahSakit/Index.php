<?php

namespace App\Livewire\RumahSakit;

use App\Models\Banner;
use App\Models\Dokter;
use App\Models\Faq;
use App\Models\LayananUnggulan;
use App\Models\LinkLayanan;
use App\Models\Partner;
use App\Models\Promo;
use App\Models\RumahSakit;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Index extends Component
{
    #[Locked]
    public RumahSakit $rs;

    public function mount(): void
    {
        $this->rs = current_rumahsakit();

        $desc = $this->rs->tentang_kami
            ? \Illuminate\Support\Str::limit(strip_tags($this->rs->tentang_kami), 155)
            : 'Portal layanan kesehatan ' . $this->rs->nama . '.';

        SEOMeta::setTitle($this->rs->nama);
        SEOMeta::setDescription($desc);

        OpenGraph::setTitle($this->rs->nama);
        OpenGraph::setDescription($desc);
        OpenGraph::setUrl(request()->url());
        OpenGraph::addProperty('site_name', $this->rs->nama);
        if ($this->rs->gambar) {
            OpenGraph::addImage(asset('storage/' . $this->rs->gambar));
        }
    }

    public function render()
    {
        $banner = Banner::where('rumah_sakit_id', $this->rs->id)
            ->where('aktif', true)
            ->orderBy('sort_order')
            ->get();

        $layananUnggulan = LayananUnggulan::where('rumah_sakit_id', $this->rs->id)->get();

        $dokterKami = Dokter::where('rumah_sakit_id', $this->rs->id)
            ->with('spesialis')
            ->where('aktif', true)
            ->inRandomOrder()
            ->limit(3)
            ->get();

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

        $promos = Promo::where('rumah_sakit_id', $this->rs->id)
            ->aktif()
            ->orderByDesc('popup')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $faqs = Faq::where('rumah_sakit_id', $this->rs->id)
            ->aktif()
            ->orderBy('sort_order')
            ->limit(5)
            ->get();

        return view('rumah_sakit.index', [
            'banner'             => $banner,
            'layanan_unggulan'   => $layananUnggulan,
            'dokter_kami'        => $dokterKami,
            'link_layanan'       => $linkLayanan,
            'partner_asuransi'   => $partnerAsuransi,
            'partner_perusahaan' => $partnerPerusahaan,
            'promos'             => $promos,
            'faqs'               => $faqs,
        ]);
    }
}
