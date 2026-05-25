<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\RumahSakit;
use App\Models\Kontak;
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

        $rumahSakits = RumahSakit::select("id", "lokasi", "slug")->where('aktif', true)->get();

        view()->share('daftarRS', $rumahSakits);

        $hospital = RumahSakit::where('slug', $slug)->firstOrFail();

        // REGISTER KE CONTAINER
        app()->instance('currentRumahSakit', $hospital);

        // SHARE KE VIEW
        view()->share('currentRumahSakit', $hospital);

        // SHARE KONTAK
        $kontakRS = Kontak::where('rumah_sakit_id', $hospital->id)->where('aktif', true)->get();

        view()->share('kontakRumahSakit', $kontakRS);

        return $next($request);
    }
}
