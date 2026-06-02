<?php

namespace App\Livewire;

use App\Models\Dokter;
use App\Models\Faq;
use App\Models\Halaman;
use App\Models\PoliKlinik;
use App\Models\Promo;
use App\Models\RumahSakit;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class GlobalSearch extends Component
{
    // Sama persis dengan pola RsPortalComponent — Livewire hydrate dari snapshot
    // di AJAX request, current_rumahsakit() hanya dipanggil saat render pertama
    #[Locked]
    public ?RumahSakit $rs = null;

    public string $query  = '';
    public bool   $isOpen = false;

    public function boot(): void
    {
        if ($this->rs === null) {
            $this->rs = current_rumahsakit();
        }

        // Re-bind ke container agar rumahsakit_route() bekerja pada Livewire AJAX request
        // (RumahSakitMiddleware tidak jalan di /livewire/update)
        if ($this->rs) {
            app()->instance('currentRumahSakit', $this->rs);
        }
    }

    #[On('open-search')]
    public function open(): void
    {
        $this->isOpen = true;
        $this->query  = '';
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->query  = '';
    }

    public function render(): View
    {
        $results = [
            'dokter'     => collect(),
            'poliklinik' => collect(),
            'promo'      => collect(),
            'faq'        => collect(),
            'halaman'    => collect(),
        ];

        $q = trim($this->query);

        if (mb_strlen($q) >= 2) {
            $bq   = $this->toBooleanQuery($q);
            $rsId = $this->rs->id;

            // Dokter — FULLTEXT pada nama, ATAU cocok via spesialis
            $results['dokter'] = Dokter::where(function ($query) use ($bq) {
                    $query->whereFullText('nama', $bq, ['mode' => 'boolean'])
                          ->orWhereHas('spesialis', fn ($s) =>
                              $s->whereFullText('nama', $bq, ['mode' => 'boolean'])
                          );
                })
                ->where('rumah_sakit_id', $rsId)
                ->where('aktif', true)
                ->with('spesialis')
                ->limit(5)
                ->get(['id', 'nama', 'slug', 'foto', 'spesialis_id']);

            // Poliklinik
            $results['poliklinik'] = PoliKlinik::whereFullText(['nama', 'deskripsi'], $bq, ['mode' => 'boolean'])
                ->where('aktif', true)
                ->whereHas('unitLayanan', fn ($u) => $u->where('rumah_sakit_id', $rsId)->where('aktif', true))
                ->with('unitLayanan')
                ->limit(5)
                ->get(['id', 'nama', 'slug', 'gambar', 'unit_layanan_id']);

            // Promo
            $results['promo'] = Promo::whereFullText(['judul', 'deskripsi'], $bq, ['mode' => 'boolean'])
                ->where('rumah_sakit_id', $rsId)
                ->aktif()
                ->limit(5)
                ->get(['id', 'judul', 'slug', 'gambar']);

            // FAQ
            $results['faq'] = Faq::whereFullText(['judul', 'deskripsi'], $bq, ['mode' => 'boolean'])
                ->where('rumah_sakit_id', $rsId)
                ->aktif()
                ->limit(5)
                ->get(['id', 'judul']);

            // Halaman statis
            $results['halaman'] = Halaman::whereFullText('judul', $bq, ['mode' => 'boolean'])
                ->where('rumah_sakit_id', $rsId)
                ->where('aktif', true)
                ->limit(5)
                ->get(['id', 'judul', 'slug']);
        }

        $hasResults = collect($results)->some(fn ($r) => $r->isNotEmpty());

        return view('livewire.global-search', [
            'results'    => $results,
            'hasResults' => $hasResults,
            'searched'   => mb_strlen($q) >= 2,
            'rsSlug'     => $this->rs?->slug ?? '',
        ]);
    }

    // Ubah "poli jantung" → "poli* jantung*" untuk FULLTEXT boolean mode
    // Efeknya: partial match ("poli" cocok "poliklinik", "jant" cocok "jantung")
    private function toBooleanQuery(string $query): string
    {
        return collect(explode(' ', $query))
            ->map(fn ($w) => trim($w))
            ->filter(fn ($w) => mb_strlen($w) >= 2)
            ->map(fn ($w) => $w . '*')
            ->join(' ');
    }
}
