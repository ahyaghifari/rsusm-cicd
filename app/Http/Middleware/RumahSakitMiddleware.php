<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\RumahSakit;
use App\Models\Halaman;
use App\Models\Kontak;
use App\Models\Promo;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RumahSakitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('rumahsakit');

        $rumahSakits = RumahSakit::select("id", "nama", "lokasi", "slug")->where('aktif', true)->get();

        view()->share('daftarRS', $rumahSakits);

        $hospital = RumahSakit::where('slug', $slug)->firstOrFail();

        // REGISTER KE CONTAINER
        app()->instance('currentRumahSakit', $hospital);

        // SHARE KE VIEW
        view()->share('currentRumahSakit', $hospital);

        // SHARE KONTAK
        $kontakRS = Kontak::where('rumah_sakit_id', $hospital->id)->where('aktif', true)->get();
        view()->share('kontakRumahSakit', $kontakRS);

        // SHARE PROMO POPUP
        $promoPopup = Promo::where('rumah_sakit_id', $hospital->id)
            ->aktif()
            ->popup()
            ->orderByDesc('created_at')
            ->get();
        view()->share('promo_popup', $promoPopup);

        // SHARE HALAMAN NAV (untuk dropdown Tentang Kami)
        $halamanNav = Halaman::where('rumah_sakit_id', $hospital->id)
            ->where('aktif', true)
            ->orderBy('judul')
            ->get(['id', 'slug', 'judul']);
        view()->share('halaman_nav', $halamanNav);

        return $next($request);
    }
}
