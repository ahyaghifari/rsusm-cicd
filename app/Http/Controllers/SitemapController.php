<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\Halaman;
use App\Models\PoliKlinik;
use App\Models\Promo;
use App\Models\RumahSakit;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    private const CACHE_TTL_HOURS = 6;

    /**
     * Sitemap index — daftar sitemap per cabang RS yang aktif.
     */
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.index', now()->addHours(self::CACHE_TTL_HOURS), function () {
            $branches = RumahSakit::where('aktif', true)->pluck('slug');

            return view('sitemap.index', ['branches' => $branches])->render();
        });

        return $this->xmlResponse($xml);
    }

    /**
     * Sitemap untuk satu cabang RS — berisi seluruh halaman publik miliknya.
     */
    public function show(string $rumahsakit): Response
    {
        $hospital = RumahSakit::where('slug', $rumahsakit)->where('aktif', true)->firstOrFail();

        $xml = Cache::remember("sitemap.{$hospital->slug}", now()->addHours(self::CACHE_TTL_HOURS), function () use ($hospital) {
            return view('sitemap.show', ['urls' => $this->urlsFor($hospital)])->render();
        });

        return $this->xmlResponse($xml);
    }

    private function urlsFor(RumahSakit $hospital): array
    {
        $rs = $hospital->slug;
        $urls = [];

        foreach ([
            'rumahsakit.home', 'rumahsakit.dokter_kami', 'rumahsakit.jadwal_praktek',
            'rumahsakit.rawat_jalan', 'rumahsakit.rawat_inap', 'rumahsakit.unggulan',
            'rumahsakit.fasilitas_pendukung', 'rumahsakit.penunjang_medis',
            'rumahsakit.partner_kami', 'rumahsakit.hubungi_kami', 'rumahsakit.promo',
            'rumahsakit.magazine', 'rumahsakit.faq',
        ] as $routeName) {
            $urls[] = ['loc' => route($routeName, ['rumahsakit' => $rs])];
        }

        $dokter = Dokter::where('rumah_sakit_id', $hospital->id)->where('aktif', true)
            ->get(['slug', 'updated_at'])
            ->map(fn ($d) => [
                'loc'     => route('rumahsakit.dokter_show', ['rumahsakit' => $rs, 'dokter' => $d->slug]),
                'lastmod' => $d->updated_at,
            ]);

        $poli = PoliKlinik::where('rumah_sakit_id', $hospital->id)->where('aktif', true)
            ->get(['slug', 'updated_at'])
            ->map(fn ($p) => [
                'loc'     => route('rumahsakit.rawat_jalan_show', ['rumahsakit' => $rs, 'poliklinik' => $p->slug]),
                'lastmod' => $p->updated_at,
            ]);

        $promo = Promo::where('rumah_sakit_id', $hospital->id)->aktif()
            ->get(['slug', 'updated_at'])
            ->map(fn ($p) => [
                'loc'     => route('rumahsakit.promo_detail', ['rumahsakit' => $rs, 'promo' => $p->slug]),
                'lastmod' => $p->updated_at,
            ]);

        $halaman = Halaman::where('rumah_sakit_id', $hospital->id)->where('aktif', true)
            ->get(['slug', 'updated_at'])
            ->map(fn ($h) => [
                'loc'     => route('rumahsakit.halaman_statis', ['rumahsakit' => $rs, 'slug' => $h->slug]),
                'lastmod' => $h->updated_at,
            ]);

        return array_merge($urls, $dokter->all(), $poli->all(), $promo->all(), $halaman->all());
    }

    private function xmlResponse(string $xml): Response
    {
        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
