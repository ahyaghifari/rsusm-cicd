<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promo;
use App\Models\RumahSakit;
use App\Models\Spesialis;
use Artesaos\SEOTools\Facades\SEOMeta;

class PortalController extends Controller
{
    public function index()
    {
        $rumahsakit = RumahSakit::where('aktif', true)->get();

        $promos = Promo::with('rumahSakit')
            ->whereHas('rumahSakit', fn($q) => $q->where('aktif', true))
            ->aktif()
            ->orderByDesc('popup')
            ->orderByDesc('created_at')
            ->get();

        SEOMeta::setTitle('RSU SYIFA MEDIKA - Pelayanan Professional & Terpercaya');
        SEOMeta::setDescription('RSU Syifa Medika hadir untuk masyarakat yang ingin mendapatkan pelayanan kesehatan yang berkualitas, RSU Syifa Medika merupakan pelayanan kesehatan yang jujur dalam pelayanan dan selalu memberikan kemudahan karena di dukung oleh staff medis yang profesional, bersertifikasi, Ahli dibidangnya serta di dukung oleh peralatan yang mutakhir dan terkini sesuai dengan moto kami yaitu Pelayanan yang profesional dan terpercaya.');

        return view('welcome', [
            'rumahsakit' => $rumahsakit,
            'promos'     => $promos,
        ]);
    }

    public function spesialis(Request $request)
    {
        $validated = $request->validate([
            'rs' => ['required', 'string', 'max:100', 'alpha_dash'],
        ]);

        $daftarSpesialis = Spesialis::whereHas('rumahsakit', function ($q) use ($validated) {
            $q->where('slug', $validated['rs'])->where('aktif', true);
        })->whereHas('dokter')->get(['id', 'nama', 'slug']);

        return response()->json($daftarSpesialis);
    }
}
